<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Bank;
use App\Models\City;
use App\Models\CustomerConstitution;
use App\Models\DocumentType;
use App\Models\Lead;
use App\Models\LeadDocument;
use App\Models\LoanProduct;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class LeadDocumentZipAndInspectionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Bank $sbiBank;
    private Bank $hdfcBank;
    private LoanProduct $businessLoan;
    private LoanProduct $homeLoan;
    private City $city;
    private Agent $agent;
    private CustomerConstitution $constitution;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'status' => 'active',
        ]);
        $this->admin->assignRole('Admin');

        $this->city = City::create(['name' => 'Ahmedabad', 'status' => 'active']);
        $this->agent = Agent::create([
            'first_name' => 'Test',
            'last_name' => 'Agent',
            'email' => 'agent@example.com',
            'mobile_number' => '+919876543210',
            'city_id' => $this->city->id,
            'status' => 'active',
        ]);

        $this->constitution = CustomerConstitution::firstOrCreate(
            ['code' => 'PROPRIETORSHIP'],
            ['name' => 'Proprietorship', 'status' => 'active']
        );

        $this->sbiBank = Bank::firstOrCreate(
            ['name' => 'State Bank of India'],
            ['status' => 'active']
        );

        $this->hdfcBank = Bank::firstOrCreate(
            ['name' => 'HDFC Bank'],
            ['status' => 'active']
        );

        $this->businessLoan = LoanProduct::firstOrCreate(
            ['code' => 'BL'],
            ['name' => 'Business Loan', 'status' => 'active']
        );

        $this->homeLoan = LoanProduct::firstOrCreate(
            ['code' => 'HL'],
            ['name' => 'Home Loan', 'status' => 'active']
        );
    }

    private function createLead(Bank $bank, LoanProduct $product, array $overrides = []): Lead
    {
        return Lead::create(array_merge([
            'name' => 'Test Applicant',
            'email' => 'applicant@example.com',
            'mobile_number' => '+919825123456',
            'city_id' => $this->city->id,
            'agent_id' => $this->agent->id,
            'bank_id' => $bank->id,
            'loan_product_id' => $product->id,
            'constitution_id' => $this->constitution->id,
            'firm_name' => 'Test Enterprise',
            'business_name' => 'Test Enterprise',
            'business_activity' => 'Manufacturing',
            'business_address' => 'Plot 10, Industrial Estate, Ahmedabad',
            'term_loan_amount' => '3000000',
            'cc_amount' => '1000000',
            'required_loan_amount' => '4000000',
            'bank_loan_details' => [
                'applicant_coapplicant_guarantor_name' => 'Test Applicant',
                'visit_office_date' => '2026-09-28',
                'office_organization_address' => 'Plot 10, Industrial Estate, Ahmedabad',
                'type_of_organization' => ['Proprietorship'],
                'nature_of_business' => ['Manufacturing'],
                'office_ownership' => ['Owned'],
                'workplace_landmark' => 'Near Highway',
                'years_in_business' => '5 Years',
                'designation_applicant_guarantor' => 'Proprietor',
                'whom_met_details' => 'Test Applicant',
                'office_tvr_result' => 'Positive',
            ],
            'status' => 'new',
        ], $overrides));
    }

    /**
     * Case 1: SBI + Business Loan
     * -> Inspection Sheet visible in UI + generated successfully + included in ZIP.
     */
    public function test_case_1_sbi_plus_business_loan_inspection_sheet_applicable_and_in_zip(): void
    {
        $lead = $this->createLead($this->sbiBank, $this->businessLoan);

        $this->assertTrue($lead->isInspectionSheetApplicable());

        // 1. Check Inspection Sheet generation endpoint succeeds
        $pdfResponse = $this->actingAs($this->admin)
            ->get(route('admin.leads.inspection-sheet.pdf', $lead));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');

        // 2. Check Inspection Sheet link is visible in index view
        $indexResponse = $this->actingAs($this->admin)
            ->get(route('admin.leads.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee(route('admin.leads.inspection-sheet.pdf', $lead));
        $indexResponse->assertSee(route('admin.leads.download-all', $lead));

        // 3. Check Inspection Sheet link is visible in show view
        $showResponse = $this->actingAs($this->admin)
            ->get(route('admin.leads.show', $lead));
        $showResponse->assertStatus(200);
        $showResponse->assertSee(route('admin.leads.inspection-sheet.pdf', $lead));
        $showResponse->assertSee(route('admin.leads.download-all', $lead));

        // 4. Check Download All ZIP contains inspection sheet and other docs
        $zipResponse = $this->actingAs($this->admin)
            ->get(route('admin.leads.download-all', $lead));
        $zipResponse->assertStatus(200);
        $zipResponse->assertHeader('content-type', 'application/zip');

        // Verify ZIP contents
        $zipContent = $zipResponse->streamedContent();
        $tempZip = tempnam(sys_get_temp_dir(), 'test_zip_');
        file_put_contents($tempZip, $zipContent);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($tempZip) === true);
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entries[] = $zip->getNameIndex($i);
        }
        $zip->close();
        @unlink($tempZip);

        $this->assertContains('Inspection_Sheet_lead_' . $lead->id . '.pdf', $entries);
        $this->assertContains('CMA_' . $lead->id . '.xlsx', $entries);
        $this->assertContains('DPR_' . $lead->id . '.docx', $entries);
        $this->assertContains('OR_' . $lead->id . '.docx', $entries);
        $this->assertContains('SBI_Stock_Statement_' . $lead->id . '.xlsx', $entries);
    }

    /**
     * Case 2: SBI + non-Business Loan
     * -> Inspection Sheet hidden in UI + not generated (404) + not in ZIP.
     */
    public function test_case_2_sbi_plus_non_business_loan_inspection_sheet_not_applicable(): void
    {
        $lead = $this->createLead($this->sbiBank, $this->homeLoan);

        $this->assertFalse($lead->isInspectionSheetApplicable());

        // 1. Direct PDF endpoint should return 404
        $pdfResponse = $this->actingAs($this->admin)
            ->get(route('admin.leads.inspection-sheet.pdf', $lead));
        $pdfResponse->assertStatus(404);

        // 2. Action menu / Index view must NOT contain inspection sheet link for this lead
        $indexResponse = $this->actingAs($this->admin)
            ->get(route('admin.leads.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee(route('admin.leads.inspection-sheet.pdf', $lead));

        // 3. Download All ZIP should still work and NOT contain inspection sheet
        $zipResponse = $this->actingAs($this->admin)
            ->get(route('admin.leads.download-all', $lead));
        $zipResponse->assertStatus(200);

        $zipContent = $zipResponse->streamedContent();
        $tempZip = tempnam(sys_get_temp_dir(), 'test_zip_');
        file_put_contents($tempZip, $zipContent);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($tempZip) === true);
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entries[] = $zip->getNameIndex($i);
        }
        $zip->close();
        @unlink($tempZip);

        $this->assertNotContains('Inspection_Sheet_lead_' . $lead->id . '.pdf', $entries);
        $this->assertContains('CMA_' . $lead->id . '.xlsx', $entries);
        $this->assertContains('DPR_' . $lead->id . '.docx', $entries);
    }

    /**
     * Case 3: Non-SBI + Business Loan
     * -> Inspection Sheet hidden in UI + not generated (404) + not in ZIP.
     */
    public function test_case_3_non_sbi_plus_business_loan_inspection_sheet_not_applicable(): void
    {
        $lead = $this->createLead($this->hdfcBank, $this->businessLoan);

        $this->assertFalse($lead->isInspectionSheetApplicable());

        // 1. Direct PDF endpoint should return 404
        $pdfResponse = $this->actingAs($this->admin)
            ->get(route('admin.leads.inspection-sheet.pdf', $lead));
        $pdfResponse->assertStatus(404);

        // 2. Action menu / Index view must NOT contain inspection sheet link for this lead
        $indexResponse = $this->actingAs($this->admin)
            ->get(route('admin.leads.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee(route('admin.leads.inspection-sheet.pdf', $lead));

        // 3. Download All ZIP should NOT contain inspection sheet
        $zipResponse = $this->actingAs($this->admin)
            ->get(route('admin.leads.download-all', $lead));
        $zipResponse->assertStatus(200);

        $zipContent = $zipResponse->streamedContent();
        $tempZip = tempnam(sys_get_temp_dir(), 'test_zip_');
        file_put_contents($tempZip, $zipContent);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($tempZip) === true);
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entries[] = $zip->getNameIndex($i);
        }
        $zip->close();
        @unlink($tempZip);

        $this->assertNotContains('Inspection_Sheet_lead_' . $lead->id . '.pdf', $entries);
    }

    /**
     * Case 4: Non-SBI + non-Business Loan
     * -> Inspection Sheet hidden in UI + not generated (404) + not in ZIP.
     */
    public function test_case_4_non_sbi_plus_non_business_loan_inspection_sheet_not_applicable(): void
    {
        $lead = $this->createLead($this->hdfcBank, $this->homeLoan);

        $this->assertFalse($lead->isInspectionSheetApplicable());

        // 1. Direct PDF endpoint should return 404
        $pdfResponse = $this->actingAs($this->admin)
            ->get(route('admin.leads.inspection-sheet.pdf', $lead));
        $pdfResponse->assertStatus(404);

        // 2. Action menu / Index view must NOT contain inspection sheet link for this lead
        $indexResponse = $this->actingAs($this->admin)
            ->get(route('admin.leads.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee(route('admin.leads.inspection-sheet.pdf', $lead));

        // 3. Download All ZIP should NOT contain inspection sheet
        $zipResponse = $this->actingAs($this->admin)
            ->get(route('admin.leads.download-all', $lead));
        $zipResponse->assertStatus(200);

        $zipContent = $zipResponse->streamedContent();
        $tempZip = tempnam(sys_get_temp_dir(), 'test_zip_');
        file_put_contents($tempZip, $zipContent);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($tempZip) === true);
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entries[] = $zip->getNameIndex($i);
        }
        $zip->close();
        @unlink($tempZip);

        $this->assertNotContains('Inspection_Sheet_lead_' . $lead->id . '.pdf', $entries);
    }

    /**
     * Case 5: Download All includes uploaded lead documents and ITR attachments.
     */
    public function test_case_5_download_all_includes_uploaded_documents_and_attachments(): void
    {
        $lead = $this->createLead($this->sbiBank, $this->businessLoan);

        $docType = DocumentType::create([
            'name' => 'PAN Card',
            'code' => 'PAN_CARD',
            'status' => 'active',
        ]);

        $storedPath = 'lead-documents/' . $lead->id . '/test_pan.pdf';
        Storage::disk('public')->put($storedPath, 'Sample PDF content for testing');

        LeadDocument::create([
            'lead_id' => $lead->id,
            'document_type_id' => $docType->id,
            'file_path' => $storedPath,
            'original_name' => 'my_pan_card.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'uploaded_by' => $this->admin->id,
        ]);

        $zipResponse = $this->actingAs($this->admin)
            ->get(route('admin.leads.download-all', $lead));
        $zipResponse->assertStatus(200);

        $zipContent = $zipResponse->streamedContent();
        $tempZip = tempnam(sys_get_temp_dir(), 'test_zip_');
        file_put_contents($tempZip, $zipContent);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($tempZip) === true);
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entries[] = $zip->getNameIndex($i);
        }
        $zip->close();
        @unlink($tempZip);

        $this->assertContains('Uploaded_Documents/PAN_Card_my_pan_card.pdf', $entries);
        $this->assertContains('CMA_' . $lead->id . '.xlsx', $entries);
        $this->assertContains('DPR_' . $lead->id . '.docx', $entries);
        $this->assertContains('OR_' . $lead->id . '.docx', $entries);
        $this->assertContains('SBI_Stock_Statement_' . $lead->id . '.xlsx', $entries);
        $this->assertContains('Inspection_Sheet_lead_' . $lead->id . '.pdf', $entries);
    }

    /**
     * Test Download All Uploaded Documents includes JPG, PDF, XLSX, Front & Back documents.
     */
    public function test_download_all_uploaded_documents_contains_all_file_types_and_sides(): void
    {
        $lead = $this->createLead($this->sbiBank, $this->businessLoan, [
            'name' => 'ABC Enterprise',
            'business_name' => 'ABC Enterprise',
        ]);

        $docTypeAadhaar = DocumentType::create([
            'name' => 'Aadhaar Card',
            'code' => 'AADHAAR_CARD',
            'status' => 'active',
        ]);

        $docTypeGst = DocumentType::create([
            'name' => 'GST Certificate',
            'code' => 'GST_CERTIFICATE',
            'status' => 'active',
        ]);

        $docTypeQuotation = DocumentType::create([
            'name' => 'Quotation',
            'code' => 'QUOTATION',
            'status' => 'active',
        ]);

        // 1. Aadhaar Front (JPG)
        $path1 = 'lead-documents/' . $lead->id . '/aadhaar_f.jpg';
        Storage::disk('public')->put($path1, 'JPG image binary data');
        LeadDocument::create([
            'lead_id' => $lead->id,
            'document_type_id' => $docTypeAadhaar->id,
            'side' => 'front',
            'file_path' => $path1,
            'original_name' => 'Aadhaar_Front.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 2048,
            'uploaded_by' => $this->admin->id,
        ]);

        // 2. Aadhaar Back (JPEG)
        $path2 = 'lead-documents/' . $lead->id . '/aadhaar_b.jpeg';
        Storage::disk('public')->put($path2, 'JPEG image binary data');
        LeadDocument::create([
            'lead_id' => $lead->id,
            'document_type_id' => $docTypeAadhaar->id,
            'side' => 'back',
            'file_path' => $path2,
            'original_name' => 'Aadhaar_Back.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 2048,
            'uploaded_by' => $this->admin->id,
        ]);

        // 3. GST Certificate (PDF)
        $path3 = 'lead-documents/' . $lead->id . '/gst.pdf';
        Storage::disk('public')->put($path3, 'PDF certificate binary data');
        LeadDocument::create([
            'lead_id' => $lead->id,
            'document_type_id' => $docTypeGst->id,
            'file_path' => $path3,
            'original_name' => 'GST_Certificate.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 4096,
            'uploaded_by' => $this->admin->id,
        ]);

        // 4. Quotation (XLSX)
        $path4 = 'lead-documents/' . $lead->id . '/quotation.xlsx';
        Storage::disk('public')->put($path4, 'Excel quotation binary data');
        LeadDocument::create([
            'lead_id' => $lead->id,
            'document_type_id' => $docTypeQuotation->id,
            'file_path' => $path4,
            'original_name' => 'Quotation.xlsx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'file_size' => 8192,
            'uploaded_by' => $this->admin->id,
        ]);

        // Check show page has the button
        $showResponse = $this->actingAs($this->admin)
            ->get(route('admin.leads.show', $lead));
        $showResponse->assertStatus(200);
        $showResponse->assertSee(route('admin.leads.uploaded-documents.download-all', $lead));

        // Request ZIP download
        $response = $this->actingAs($this->admin)
            ->get(route('admin.leads.uploaded-documents.download-all', $lead));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/zip');
        $this->assertStringContainsString('ABC_Enterprise_Uploaded_Documents.zip', $response->headers->get('content-disposition'));

        $zipContent = $response->streamedContent();
        $tempZip = tempnam(sys_get_temp_dir(), 'test_uploads_zip_');
        file_put_contents($tempZip, $zipContent);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($tempZip) === true);
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entries[] = $zip->getNameIndex($i);
        }
        $zip->close();
        @unlink($tempZip);

        $this->assertContains('Aadhaar_Front.jpg', $entries);
        $this->assertContains('Aadhaar_Back.jpg', $entries);
        $this->assertContains('GST_Certificate.pdf', $entries);
        $this->assertContains('Quotation.xlsx', $entries);

        // Generated docs must NOT be in the uploaded documents ZIP
        $this->assertNotContains('CMA_' . $lead->id . '.xlsx', $entries);
        $this->assertNotContains('DPR_' . $lead->id . '.docx', $entries);
        $this->assertNotContains('OR_' . $lead->id . '.docx', $entries);
        $this->assertNotContains('SBI_Stock_Statement_' . $lead->id . '.xlsx', $entries);
        $this->assertNotContains('Inspection_Sheet_lead_' . $lead->id . '.pdf', $entries);
    }

    /**
     * Test duplicate filenames are handled with unique entries in the ZIP.
     */
    public function test_uploaded_documents_zip_handles_duplicate_filenames(): void
    {
        $lead = $this->createLead($this->sbiBank, $this->businessLoan);

        $docType = DocumentType::create([
            'name' => 'Bank Statement',
            'code' => 'BANK_STATEMENT',
            'status' => 'active',
        ]);

        $path1 = 'lead-documents/' . $lead->id . '/stmt_1.pdf';
        $path2 = 'lead-documents/' . $lead->id . '/stmt_2.pdf';
        Storage::disk('public')->put($path1, 'Statement 1');
        Storage::disk('public')->put($path2, 'Statement 2');

        LeadDocument::create([
            'lead_id' => $lead->id,
            'document_type_id' => $docType->id,
            'file_path' => $path1,
            'original_name' => 'Bank_Statement.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'uploaded_by' => $this->admin->id,
        ]);

        LeadDocument::create([
            'lead_id' => $lead->id,
            'document_type_id' => $docType->id,
            'file_path' => $path2,
            'original_name' => 'Bank_Statement.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'uploaded_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.leads.uploaded-documents.download-all', $lead));

        $response->assertStatus(200);

        $zipContent = $response->streamedContent();
        $tempZip = tempnam(sys_get_temp_dir(), 'test_uploads_dup_zip_');
        file_put_contents($tempZip, $zipContent);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($tempZip) === true);
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entries[] = $zip->getNameIndex($i);
        }
        $zip->close();
        @unlink($tempZip);

        $this->assertCount(2, $entries);
        $this->assertContains('Bank_Statement.pdf', $entries);
        $this->assertContains('Bank_Statement (2).pdf', $entries);
    }

    /**
     * Test Lead with no uploaded documents shows proper empty-state message and redirects.
     */
    public function test_lead_with_no_uploaded_documents_shows_empty_state_message(): void
    {
        $lead = $this->createLead($this->sbiBank, $this->businessLoan);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.leads.uploaded-documents.download-all', $lead));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'No documents available for download.');
    }

    /**
     * Test isolation: Only files belonging to the requested Lead are included.
     */
    public function test_uploaded_documents_zip_strictly_isolated_to_selected_lead(): void
    {
        $leadA = $this->createLead($this->sbiBank, $this->businessLoan, ['name' => 'Lead Alpha']);
        $leadB = $this->createLead($this->sbiBank, $this->businessLoan, ['name' => 'Lead Beta']);

        $docType = DocumentType::create([
            'name' => 'ID Proof',
            'code' => 'ID_PROOF',
            'status' => 'active',
        ]);

        $pathA = 'lead-documents/' . $leadA->id . '/alpha.pdf';
        $pathB = 'lead-documents/' . $leadB->id . '/beta.pdf';
        Storage::disk('public')->put($pathA, 'Alpha Doc');
        Storage::disk('public')->put($pathB, 'Beta Doc');

        LeadDocument::create([
            'lead_id' => $leadA->id,
            'document_type_id' => $docType->id,
            'file_path' => $pathA,
            'original_name' => 'Alpha_Passport.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'uploaded_by' => $this->admin->id,
        ]);

        LeadDocument::create([
            'lead_id' => $leadB->id,
            'document_type_id' => $docType->id,
            'file_path' => $pathB,
            'original_name' => 'Beta_Passport.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'uploaded_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.leads.uploaded-documents.download-all', $leadA));

        $response->assertStatus(200);

        $zipContent = $response->streamedContent();
        $tempZip = tempnam(sys_get_temp_dir(), 'test_isolation_zip_');
        file_put_contents($tempZip, $zipContent);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($tempZip) === true);
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entries[] = $zip->getNameIndex($i);
        }
        $zip->close();
        @unlink($tempZip);

        $this->assertContains('Alpha_Passport.pdf', $entries);
        $this->assertNotContains('Beta_Passport.pdf', $entries);
    }
}
