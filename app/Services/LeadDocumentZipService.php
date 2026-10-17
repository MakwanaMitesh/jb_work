<?php

namespace App\Services;

use App\Models\Lead;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class LeadDocumentZipService
{
    public function __construct(
        protected CmaGenerator $cmaGenerator,
        protected DprGenerator $dprGenerator,
        protected OrGenerator $orGenerator,
        protected SbiStockStatementGenerator $sbiStockStatementGenerator
    ) {}

    /**
     * Create a single ZIP file containing all applicable generated documents
     * and available uploaded files for the given lead.
     *
     * Returns the absolute path to the temporary ZIP file.
     * Caller is responsible for deleting the temp file after download.
     */
    public function createZip(Lead $lead): string
    {
        $lead->loadMissing(['bank', 'loanProduct', 'city', 'constitution', 'leadDocuments.documentType']);

        $tempFile = tempnam(sys_get_temp_dir(), 'lead_zip_');
        $zip = new ZipArchive();

        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Failed to create temporary ZIP archive.');
        }

        $user = auth()->user();
        $fileCount = 0;

        // 1. CMA Workbook
        if (! $user || $user->can('cma.download')) {
            try {
                $cmaRelativePath = 'cma/' . $lead->id . '/CMA.xlsx';
                $fullPath = $this->getStoragePath($cmaRelativePath);
                if (! is_file($fullPath) && (! $user || $user->can('cma.generate'))) {
                    $cmaRelativePath = $this->cmaGenerator->generate($lead);
                    $fullPath = $this->getStoragePath($cmaRelativePath);
                }
                if (is_file($fullPath)) {
                    $zip->addFile($fullPath, 'CMA_' . $lead->id . '.xlsx');
                    $fileCount++;
                }
            } catch (\Throwable $e) {
                Log::warning("Could not include CMA for lead {$lead->id} in ZIP: " . $e->getMessage());
            }
        }

        // 2. DPR Document
        if (! $user || $user->can('dpr.download')) {
            try {
                $dprRelativePath = 'dpr/' . $lead->id . '/DPR.docx';
                $fullPath = $this->getStoragePath($dprRelativePath);
                if (! is_file($fullPath) && (! $user || $user->can('dpr.generate'))) {
                    $dprRelativePath = $this->dprGenerator->generate($lead);
                    $fullPath = $this->getStoragePath($dprRelativePath);
                }
                if (is_file($fullPath)) {
                    $zip->addFile($fullPath, 'DPR_' . $lead->id . '.docx');
                    $fileCount++;
                }
            } catch (\Throwable $e) {
                Log::warning("Could not include DPR for lead {$lead->id} in ZIP: " . $e->getMessage());
            }
        }

        // 3. OR (Opinion Report / Affidavit)
        if (! $user || $user->can('or.download')) {
            try {
                $orRelativePath = 'or/' . $lead->id . '/OR.docx';
                $fullPath = $this->getStoragePath($orRelativePath);
                if (! is_file($fullPath) && (! $user || $user->can('or.generate'))) {
                    $orRelativePath = $this->orGenerator->generate($lead);
                    $fullPath = $this->getStoragePath($orRelativePath);
                }
                if (is_file($fullPath)) {
                    $zip->addFile($fullPath, 'OR_' . $lead->id . '.docx');
                    $fileCount++;
                }
            } catch (\Throwable $e) {
                Log::warning("Could not include OR for lead {$lead->id} in ZIP: " . $e->getMessage());
            }
        }

        // 4. SBI Stock Statement
        if (! $user || $user->can('sbi_stock_statement.download')) {
            try {
                $stockRelativePath = 'sbi-stock-statement/' . $lead->id . '/SBI_Stock_Statement.xlsx';
                $fullPath = $this->getStoragePath($stockRelativePath);
                if (! is_file($fullPath)) {
                    $altRelative = 'sbi_stock_statement/' . $lead->id . '/SBI_Stock_statement.xlsx';
                    if (is_file($this->getStoragePath($altRelative))) {
                        $stockRelativePath = $altRelative;
                        $fullPath = $this->getStoragePath($altRelative);
                    } elseif (! $user || $user->can('sbi_stock_statement.generate')) {
                        $stockRelativePath = $this->sbiStockStatementGenerator->generate($lead);
                        $fullPath = $this->getStoragePath($stockRelativePath);
                    }
                }
                if (is_file($fullPath)) {
                    $zip->addFile($fullPath, 'SBI_Stock_Statement_' . $lead->id . '.xlsx');
                    $fileCount++;
                }
            } catch (\Throwable $e) {
                Log::warning("Could not include SBI Stock Statement for lead {$lead->id} in ZIP: " . $e->getMessage());
            }
        }

        // 5. Pre-Sanction Inspection Sheet (PDF)
        // MUST ONLY be generated/included when BOTH conditions are true:
        // Bank = SBI (State Bank of India) AND Loan Type = Business Loan
        if ($lead->isInspectionSheetApplicable() && (! $user || $user->can('inspection_sheet.download'))) {
            try {
                $inspectionRelative = 'inspection_sheets/' . $lead->id . '/inspection_sheet_lead_' . $lead->id . '.pdf';
                $fullPath = $this->getStoragePath($inspectionRelative);
                if (is_file($fullPath)) {
                    $zip->addFile($fullPath, 'Inspection_Sheet_lead_' . $lead->id . '.pdf');
                    $fileCount++;
                } elseif (! $user || $user->can('inspection_sheet.generate')) {
                    $bld = $lead->bank_loan_details ?? [];
                    $pdf = Pdf::loadView('admin.leads.pdf.inspection_sheet', compact('lead', 'bld'));
                    $pdf->setPaper('a4', 'portrait');
                    $pdfContent = $pdf->output();

                    $zip->addFromString('Inspection_Sheet_lead_' . $lead->id . '.pdf', $pdfContent);
                    $fileCount++;
                }
            } catch (\Throwable $e) {
                Log::warning("Could not include Inspection Sheet for lead {$lead->id} in ZIP: " . $e->getMessage());
            }
        }

        // 6. Uploaded Lead Documents (if user has documents.download)
        if ((! $user || $user->can('documents.download')) && $lead->leadDocuments && $lead->leadDocuments->isNotEmpty()) {
            $usedNames = [];
            foreach ($lead->leadDocuments as $doc) {
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    $fullPath = Storage::disk('public')->path($doc->file_path);
                    if (is_file($fullPath)) {
                        $docTypeName = $doc->documentType?->name ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $doc->documentType->name) : 'Document';
                        $baseName = $doc->original_name ?: basename($doc->file_path);
                        $entryName = 'Uploaded_Documents/' . $docTypeName . '_' . $baseName;

                        if (isset($usedNames[$entryName])) {
                            $usedNames[$entryName]++;
                            $ext = pathinfo($baseName, PATHINFO_EXTENSION);
                            $filenameWithoutExt = pathinfo($baseName, PATHINFO_FILENAME);
                            $entryName = 'Uploaded_Documents/' . $docTypeName . '_' . $filenameWithoutExt . '_' . $usedNames[$entryName] . ($ext ? '.' . $ext : '');
                        } else {
                            $usedNames[$entryName] = 1;
                        }

                        $zip->addFile($fullPath, $entryName);
                        $fileCount++;
                    }
                }
            }
        }

        // 7. Uploaded ITR files (if any present in itr_details)
        if (is_array($lead->itr_details)) {
            foreach ($lead->itr_details as $index => $itr) {
                $ay = !empty($itr['assessment_year']) ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $itr['assessment_year']) : ('AY_' . ($index + 1));
                $fileKeys = [
                    'audit_report' => 'Audit_Report',
                    'itr_file' => 'ITR_Ack',
                    'computation' => 'Computation',
                    'itr_form' => 'ITR_Form',
                ];

                foreach ($fileKeys as $field => $label) {
                    if (!empty($itr[$field]) && Storage::disk('public')->exists($itr[$field])) {
                        $fullPath = Storage::disk('public')->path($itr[$field]);
                        if (is_file($fullPath)) {
                            $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                            $entryName = 'ITR_Documents/' . $ay . '_' . $label . ($ext ? '.' . $ext : '');
                            $zip->addFile($fullPath, $entryName);
                            $fileCount++;
                        }
                    }
                }
            }
        }

        $zip->close();

        if ($fileCount === 0) {
            @unlink($tempFile);
            throw new RuntimeException('No documents available to package into ZIP.');
        }

        return $tempFile;
    }

    /**
     * Create a single ZIP file containing ONLY files uploaded to the given lead.
     * Excludes any generated/template documents.
     *
     * Returns the absolute path to the temporary ZIP file.
     * Caller is responsible for deleting the temp file after download.
     */
    public function createUploadedDocumentsZip(Lead $lead): string
    {
        $lead->loadMissing(['leadDocuments.documentType']);

        $tempFile = tempnam(sys_get_temp_dir(), 'lead_uploads_zip_');
        $zip = new ZipArchive();

        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Failed to create temporary ZIP archive.');
        }

        $fileCount = 0;
        $usedFilenames = [];

        // 1. Lead uploaded documents from lead_documents table
        if ($lead->leadDocuments && $lead->leadDocuments->isNotEmpty()) {
            foreach ($lead->leadDocuments as $doc) {
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    $fullPath = $this->getStoragePath($doc->file_path);
                    if (is_file($fullPath)) {
                        $baseName = $doc->original_name ?: basename($doc->file_path);
                        $baseName = basename($baseName);
                        if (empty($baseName)) {
                            $baseName = 'document_' . $doc->id;
                        }

                        $lower = strtolower($baseName);
                        if (isset($usedFilenames[$lower])) {
                            $usedFilenames[$lower]++;
                            $count = $usedFilenames[$lower];
                            $ext = pathinfo($baseName, PATHINFO_EXTENSION);
                            $filenameWithoutExt = pathinfo($baseName, PATHINFO_FILENAME);
                            $entryName = $filenameWithoutExt . " ({$count})" . ($ext ? '.' . $ext : '');
                        } else {
                            $usedFilenames[$lower] = 1;
                            $entryName = $baseName;
                        }

                        $zip->addFile($fullPath, $entryName);
                        $fileCount++;
                    }
                }
            }
        }

        // 2. Uploaded ITR files (if any attached in itr_details)
        if (is_array($lead->itr_details)) {
            foreach ($lead->itr_details as $index => $itr) {
                $ay = !empty($itr['assessment_year']) ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $itr['assessment_year']) : ('AY_' . ($index + 1));
                $fileKeys = [
                    'audit_report' => 'Audit_Report',
                    'itr_file' => 'ITR_Ack',
                    'computation' => 'Computation',
                    'itr_form' => 'ITR_Form',
                ];

                foreach ($fileKeys as $field => $label) {
                    if (!empty($itr[$field]) && Storage::disk('public')->exists($itr[$field])) {
                        $fullPath = $this->getStoragePath($itr[$field]);
                        if (is_file($fullPath)) {
                            $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                            $baseName = $ay . '_' . $label . ($ext ? '.' . $ext : '');
                            $lower = strtolower($baseName);
                            if (isset($usedFilenames[$lower])) {
                                $usedFilenames[$lower]++;
                                $count = $usedFilenames[$lower];
                                $filenameWithoutExt = pathinfo($baseName, PATHINFO_FILENAME);
                                $entryName = $filenameWithoutExt . " ({$count})" . ($ext ? '.' . $ext : '');
                            } else {
                                $usedFilenames[$lower] = 1;
                                $entryName = $baseName;
                            }
                            $zip->addFile($fullPath, $entryName);
                            $fileCount++;
                        }
                    }
                }
            }
        }

        $zip->close();

        if ($fileCount === 0) {
            @unlink($tempFile);
            throw new RuntimeException('No documents available for download.');
        }

        return $tempFile;
    }

    /**
     * Resolve the absolute path to a generated or storage file.
     */
    protected function getStoragePath(string $relativePath): string
    {
        $directPath = storage_path('app/public/' . $relativePath);
        if (is_file($directPath)) {
            return $directPath;
        }

        return Storage::disk('public')->path($relativePath);
    }
}
