<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\City;
use App\Models\CustomerConstitution;
use App\Models\DocumentType;
use App\Models\Lead;
use App\Models\LoanProduct;
use App\Models\LoanProductConstitution;
use App\Models\User;
use App\Services\ApplicationFormGenerator;
use App\Services\CmaGenerator;
use App\Services\DprGenerator;
use App\Services\OrGenerator;
use App\Services\SbiStockStatementGenerator;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class LeadReportsDownloadTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private LoanProduct $product;
    private CustomerConstitution $constitution;
    private City $city;
    private Bank $bank;
    private Lead $lead;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@gmail.com')->first();
        if (!$this->admin) {
            $this->admin = User::factory()->create();
            $this->admin->assignRole('Admin');
        }

        $this->product = LoanProduct::firstOrCreate(
            ['code' => 'BL'],
            ['name' => 'Business Loan', 'status' => 'active']
        );

        $this->constitution = CustomerConstitution::firstOrCreate(
            ['code' => 'PROP'],
            ['name' => 'Proprietorship', 'status' => 'active']
        );

        LoanProductConstitution::firstOrCreate([
            'loan_product_id' => $this->product->id,
            'constitution_id' => $this->constitution->id,
        ], ['status' => 'active']);

        $this->city = City::firstOrCreate(['name' => 'Surat'], ['status' => 'active']);
        $this->bank = Bank::firstOrCreate(['name' => 'State Bank of India'], ['status' => 'active']);

        // Create lead with complete details
        $this->lead = Lead::create([
            'name' => 'Anilbhai Shantilal Shah',
            'business_name' => 'Shah Advanced Automations',
            'email' => 'anil.shah@example.com',
            'mobile_number' => '+919988776655',
            'address' => 'Plot 88, Surat Textile Park, Sachin GIDC, Surat',
            'business_address' => 'Plot 88, Sachin GIDC, Surat - 394230',
            'pan_card' => 'XYZPA9876Q',
            'aadhar_card' => '987654321098',
            'udyam_registration' => 'UDYAM-GJ-02-0099887',
            'education' => 'Master of Technology',
            'date_of_birth' => '1982-11-25',
            'gender' => 'Male',
            'mother_name' => 'Champaben Shah',
            'father_full_name' => 'Shantilal Shah',
            'relationship' => 'Son',
            'city_id' => $this->city->id,
            'bank_id' => $this->bank->id,
            'loan_product_id' => $this->product->id,
            'constitution_id' => $this->constitution->id,
            'status' => 'in_progress',
            'required_loan_amount' => '8500000',
            'cc_amount' => '5000000',
            'term_loan_amount' => '3500000',
            'bank_loan_details' => [
                'applicant_name' => 'Anilbhai Shantilal Shah - Prop. Of Shah Advanced Automations',
                'office_address' => 'Plot 88, Sachin GIDC, Surat',
                'business_years' => '15 Years',
                'inspection_remarks' => 'Plant verified in Sachin GIDC.',
            ],
            'bank_details' => [
                [
                    'bank_name' => 'State Bank of India',
                    'account_number' => '898900112233',
                    'account_type' => 'Current',
                    'ifsc_code' => 'SBIN0001234',
                ],
            ],
        ]);
    }

    private function getXlsxAllText(string $path): string
    {
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path));
        $allText = '';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_ends_with($name, '.xml')) {
                $allText .= ' ' . strip_tags($zip->getFromName($name));
            }
        }
        $zip->close();
        return $allText;
    }

    public function test_application_form_generation_and_download_contains_lead_data(): void
    {
        // 1. Generate Application Form
        $genResponse = $this->actingAs($this->admin)->post(route('admin.leads.application-form.generate', $this->lead));
        $genResponse->assertRedirect();

        // 2. Download Application Form
        $downloadResponse = $this->actingAs($this->admin)->get(route('admin.leads.application-form.download', $this->lead));
        $downloadResponse->assertStatus(200);

        // Verify content inside file
        $path = Storage::disk(ApplicationFormGenerator::DISK)->path(ApplicationFormGenerator::relativePath($this->lead));
        $this->assertFileExists($path);

        $allText = $this->getXlsxAllText($path);

        $this->assertStringContainsString('Anilbhai Shantilal Shah', $allText);
        $this->assertStringContainsString('Shah Advanced Automations', $allText);
        $this->assertStringContainsString('XYZPA9876Q', $allText);
    }

    public function test_cma_report_generation_and_download_contains_lead_data(): void
    {
        $genResponse = $this->actingAs($this->admin)->post(route('admin.leads.cma.generate', $this->lead));
        $genResponse->assertRedirect();

        $downloadResponse = $this->actingAs($this->admin)->get(route('admin.leads.cma.download', $this->lead));
        $downloadResponse->assertStatus(200);

        $path = storage_path('app/public/cma/' . $this->lead->id . '/CMA.xlsx');
        $this->assertFileExists($path);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path));
        $sharedStrings = $zip->getFromName('xl/sharedStrings.xml');
        $zip->close();

        $this->assertStringContainsString('Anilbhai Shantilal Shah', $sharedStrings);
        $this->assertStringContainsString('Shah Advanced Automations', $sharedStrings);
        $this->assertStringContainsString('XYZPA9876Q', $sharedStrings);
    }

    public function test_sbi_stock_statement_generation_and_download_contains_lead_data(): void
    {
        $genResponse = $this->actingAs($this->admin)->post(route('admin.leads.sbi-stock-statement.generate', $this->lead));
        $genResponse->assertRedirect();

        $downloadResponse = $this->actingAs($this->admin)->get(route('admin.leads.sbi-stock-statement.download', $this->lead));
        $downloadResponse->assertStatus(200);

        $path = storage_path('app/public/sbi-stock-statement/' . $this->lead->id . '/SBI_Stock_Statement.xlsx');
        $this->assertFileExists($path);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path));
        $sharedStrings = $zip->getFromName('xl/sharedStrings.xml');
        $zip->close();

        $this->assertStringContainsString('Shah Advanced Automations', $sharedStrings);
    }

    public function test_dpr_document_generation_and_download_contains_lead_data(): void
    {
        $genResponse = $this->actingAs($this->admin)->post(route('admin.leads.dpr.generate', $this->lead));
        $genResponse->assertRedirect();

        $downloadResponse = $this->actingAs($this->admin)->get(route('admin.leads.dpr.download', $this->lead));
        $downloadResponse->assertStatus(200);

        $path = storage_path('app/public/dpr/' . $this->lead->id . '/DPR.docx');
        $this->assertFileExists($path);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path));
        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();

        $this->assertStringContainsString('Anilbhai Shantilal Shah', $documentXml);
        $this->assertStringContainsString('Shah Advanced Automations', $documentXml);
        $this->assertStringContainsString('XYZPA9876Q', $documentXml);
    }

    public function test_opinion_report_generation_and_download_contains_lead_data(): void
    {
        $genResponse = $this->actingAs($this->admin)->post(route('admin.leads.or.generate', $this->lead));
        $genResponse->assertRedirect();

        $downloadResponse = $this->actingAs($this->admin)->get(route('admin.leads.or.download', $this->lead));
        $downloadResponse->assertStatus(200);

        $path = storage_path('app/public/or/' . $this->lead->id . '/OR.docx');
        $this->assertFileExists($path);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path));
        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();

        $this->assertStringContainsString('Anilbhai Shantilal Shah', $documentXml);
        $this->assertStringContainsString('Shah Advanced Automations', $documentXml);
        $this->assertStringContainsString('Shantilal Shah', $documentXml);
    }

    public function test_inspection_sheet_pdf_generation_and_download_contains_lead_data(): void
    {
        $genResponse = $this->actingAs($this->admin)->post(route('admin.leads.inspection-sheet.generate', $this->lead));
        $genResponse->assertRedirect();

        $downloadResponse = $this->actingAs($this->admin)->get(route('admin.leads.inspection-sheet.pdf', $this->lead));
        $downloadResponse->assertStatus(200);

        $path = storage_path('app/public/inspection_sheets/' . $this->lead->id . '/inspection_sheet_lead_' . $this->lead->id . '.pdf');
        $this->assertFileExists($path);
        $this->assertGreaterThan(1000, filesize($path));
    }

    public function test_download_all_documents_zip(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.leads.download-all', $this->lead));
        $response->assertStatus(200);
    }

    public function test_download_all_uploaded_documents_zip(): void
    {
        $panType = DocumentType::where('code', 'pan_card')->first();
        $file = UploadedFile::fake()->create('pan.pdf', 100, 'application/pdf');
        $this->lead->leadDocuments()->create([
            'document_type_id' => $panType->id,
            'file_path' => $file->store("leads/{$this->lead->id}/documents", 'public'),
            'original_name' => 'pan.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100000,
            'uploaded_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.leads.uploaded-documents.download-all', $this->lead));
        $response->assertStatus(200);
    }
}
