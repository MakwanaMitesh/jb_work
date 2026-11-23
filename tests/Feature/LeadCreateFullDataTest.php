<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Bank;
use App\Models\City;
use App\Models\CustomerConstitution;
use App\Models\DocumentType;
use App\Models\Lead;
use App\Models\LoanProduct;
use App\Models\LoanProductConstitution;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeadCreateFullDataTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private LoanProduct $product;
    private CustomerConstitution $constitution;
    private City $city;
    private Bank $bank;

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
            ['code' => 'TEST_HL'],
            ['name' => 'Home Loan Test', 'status' => 'active']
        );

        $this->constitution = CustomerConstitution::firstOrCreate(
            ['code' => 'TEST_IND'],
            ['name' => 'Individual Test', 'status' => 'active']
        );

        LoanProductConstitution::firstOrCreate([
            'loan_product_id' => $this->product->id,
            'constitution_id' => $this->constitution->id,
        ], ['status' => 'active']);

        $this->city = City::firstOrCreate(['name' => 'Ahmedabad Test'], ['status' => 'active']);
        $this->bank = Bank::firstOrCreate(['name' => 'SBI Test'], ['status' => 'active']);
    }

    public function test_create_lead_form_renders_cleanly(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.leads.create'));
        $response->assertStatus(200);
        $response->assertSee('Create Lead');
        $response->assertSee('Details Sheet');
        $response->assertSee('Documents');
        $response->assertSee('Pre Sanction');
    }

    public function test_create_lead_with_full_data_and_dummy_documents(): void
    {
        Storage::fake('public');

        $pdfAadhaarFront = UploadedFile::fake()->create('aadhaar_front.pdf', 200, 'application/pdf');
        $pdfAadhaarBack = UploadedFile::fake()->create('aadhaar_back.pdf', 200, 'application/pdf');
        $pdfPan = UploadedFile::fake()->create('pan_card.pdf', 150, 'application/pdf');
        $pngPhoto = UploadedFile::fake()->image('photo.png', 300, 300);
        $pdfBank1 = UploadedFile::fake()->create('statement_jan.pdf', 500, 'application/pdf');
        $pdfBank2 = UploadedFile::fake()->create('statement_feb.pdf', 500, 'application/pdf');
        $itrAudit = UploadedFile::fake()->create('audit_report.pdf', 400, 'application/pdf');
        $itrDoc = UploadedFile::fake()->create('itr_file.pdf', 300, 'application/pdf');

        $payload = [
            // 1. KYC Details Sheet
            'name' => 'Vikram Sharma',
            'date_of_birth' => '1988-04-12',
            'gender' => 'Male',
            'place_of_birth' => 'Ahmedabad',
            'occupation' => 'Business Owner',
            'mobile_number' => '+919876543210',
            'alternate_mobile_number' => '+919876543211',
            'email' => 'vikram.sharma.test@example.com',
            'address' => 'Shop 12, Galaxy Commercial Hub, CG Road, Ahmedabad',
            'aadhar_card' => '123456789012',
            'pan_card' => 'ABCDE1234F',
            'udyam_registration' => 'UDYAM-GJ-01-0099887',
            'fssai_license' => '10019021000999',
            'education' => 'Graduate',
            'mother_name' => 'Sunita Sharma',
            'father_full_name' => 'Ramesh Sharma',
            'relationship' => 'Son',

            // ITR
            'itr_id' => 'ABCDE1234F',
            'itr_password' => 'ItrSecret123',
            'itr_audited' => 'Yes',
            'itr_ay_2026_27' => 1,
            'itr_ay_2025_26' => 1,
            'itr_ay_2024_25' => 1,
            'itr_details' => [
                [
                    'assessment_year' => '2025-26',
                    'itr_audited' => 'Yes',
                    'audit_report' => $itrAudit,
                    'itr_file' => $itrDoc,
                ],
            ],

            // Selects
            'bank_id' => $this->bank->id,
            'loan_product_id' => $this->product->id,
            'constitution_id' => $this->constitution->id,
            'source' => 'Direct Walk-in',
            'city_id' => $this->city->id,
            'status' => 'new',
            'notes' => 'Testing complete lead creation workflow.',

            // 2. Pre Sanction
            'bank_loan_details' => [
                'applicant_name' => 'Vikram Sharma',
                'office_address' => 'Shop 12, Galaxy Hub, CG Road',
                'business_years' => '8 Years',
                'type_of_organization' => ['Proprietorship'],
                'nature_of_business' => ['Trading'],
                'office_ownership' => ['Rented'],
                'visit_office_date' => '2026-11-20',
                'office_tvr_result' => 'Positive',
                'inspection_remarks' => 'Office inspected and verified.',
            ],

            // 3. Bank Details
            'bank_details' => [
                [
                    'bank_name' => 'State Bank of India',
                    'account_number' => '20011223344',
                    'account_type' => 'Current',
                    'ifsc_code' => 'SBIN0001234',
                ],
            ],

            // 4. Business Details
            'business_name' => 'Sharma Trading Co.',
            'constitution_of_business' => 'Proprietorship',
            'introduction' => 'Wholesale distribution of electrical items.',
            'business_address' => 'Shop 12, Galaxy Hub, CG Road',
            'gst_applicable' => 'Yes',
            'gst_number' => '24ABCDE1234F1Z5',
            'gst_id' => 'sharmatrading',
            'gst_password' => 'GstSecret123',
            'firm_name' => 'Sharma Trading Co.',
            'business_activity' => 'Wholesale Trading',
            'business_experience' => '8 Years',
            'no_of_manpower' => '5 Employees',
            'business_location' => 'Ahmedabad',
            'area_of_premises' => '1200 Sq Ft',
            'land_and_factory_building' => 'Commercial Shop',
            'connectivity' => 'Prime location on main road',

            // 5. Loan Details
            'required_loan_amount' => '2500000',
            'cc_amount' => '1500000',
            'cc_details' => 'Stock in trade working capital',
            'term_loan_amount' => '1000000',
            'term_loan_machinery_details' => 'Shop interior and billing infrastructure',
            'current_loans' => [
                [
                    'bank_name' => 'HDFC Bank',
                    'loan_type' => 'Business Loan',
                    'loan_amount' => '500000',
                    'disburse_date' => '2024-01-15',
                    'emi' => '18500',
                    'outstanding_amount' => '220000',
                    'tenure' => '3 Years',
                ],
            ],

            // 6. Application Form (`af`)
            'af' => [
                'enterprise_name' => 'Sharma Trading Co.',
                'applicant_name' => 'Vikram Sharma',
                'branch_name' => 'CG Road Branch',
                'category' => 'Tarun',
                'constitution' => 'Proprietary',
                'business_address' => 'Shop 12, Galaxy Hub, CG Road',
                'state' => 'Gujarat',
                'pincode' => '380009',
                'business_premises' => 'Rented',
                'mobile_number' => '9876543210',
                'email' => 'vikram.sharma.test@example.com',
                'activity_existing' => 'Trading',
                'activity_proposed' => 'Expansion',
                'date_of_birth' => '1988-04-12',
                'sex' => 'M',
                'residential_address' => 'A-101, Shanti Tower, Satellite, Ahmedabad',
                'applicant_mobile' => '9876543210',
                'qualification' => 'Graduate',
                'experience_years' => '8',
                'pan_din_number' => 'ABCDE1234F',
            ],

            // 7. Stock Statement (`ss`)
            'ss' => [
                'statement_date' => '2026-11-20',
                'company_name' => 'Sharma Trading Co.',
                'cc_limit' => '1500000',
                'place' => 'Ahmedabad',
                'summary_amount' => '2200000',
                'debtor_name' => 'Retail Client A',
                'debtor_amount' => '450000',
                'sundry_creditors_year_end' => '300000',
                'margin_stock' => '25',
                'margin_book_debts' => '40',
            ],

            // 8. Opinion Report (`opr`)
            'opr' => [
                'deponent_name' => 'Vikram Sharma',
                'father_name' => 'Ramesh Sharma',
                'date_of_birth' => '1988-04-12',
                'place_of_birth' => 'Ahmedabad',
                'occupation' => 'Business',
                'residential_address' => 'A-101, Shanti Tower, Satellite, Ahmedabad',
                'office_address' => 'Shop 12, Galaxy Hub, CG Road',
                'branch' => 'CG Road Branch',
                'borrower_name' => 'Sharma Trading Co.',
                'income_per_month' => '180000',
                'outside_india' => 'No',
            ],

            // 9. CMA Profile (`cma`)
            'cma' => [
                'activity' => 'Trading',
                'firm_name' => 'Sharma Trading Co.',
                'proprietor_name' => 'Vikram Sharma',
                'pan' => 'ABCDE1234F',
                'udyam_no' => 'UDYAM-GJ-01-0099887',
                'mobile' => '9876543210',
                'email' => 'vikram.sharma.test@example.com',
                'address' => 'Shop 12, Galaxy Hub, CG Road',
                'business_experience' => '8 Years',
                'area_of_operation' => 'Ahmedabad and Gandhinagar',
                'manpower' => '5',
                'space' => '1200 sq ft',
                'connectivity' => 'Good',
                'location' => 'Commercial Complex',
                'electricity_water' => 'Available',
            ],

            // 10. DPR (`dpr`)
            'dpr' => [
                'firm_name' => 'Sharma Trading Co.',
                'constitution' => 'Proprietorship',
                'applicant_name' => 'Vikram Sharma',
                'business_address' => 'Shop 12, Galaxy Hub, CG Road',
                'pan' => 'ABCDE1234F',
                'aadhaar' => '123456789012',
                'gender' => 'Male',
                'mobile' => '9876543210',
                'email' => 'vikram.sharma.test@example.com',
                'education' => 'Graduate',
                'experience' => '8 Years',
                'activity' => 'Electrical Wholesale Trading',
                'cost_machinery' => '500000',
                'cost_working_capital' => '2000000',
                'fin_promoter_capital' => '500000',
                'fin_term_loan' => '500000',
                'fin_cash_credit' => '1500000',
            ],

            // 11. Documents
            'documents' => [
                'aadhar_card' => [
                    'front' => $pdfAadhaarFront,
                    'back' => $pdfAadhaarBack,
                ],
                'pan_card' => [
                    'file' => $pdfPan,
                ],
                'photo' => [
                    'file' => $pngPhoto,
                ],
                'bank_statement' => [
                    'files' => [$pdfBank1, $pdfBank2],
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.leads.store'), $payload);

        $response->assertRedirect(route('admin.leads.index'));
        $response->assertSessionHas('success');

        $lead = Lead::where('email', 'vikram.sharma.test@example.com')->first();
        $this->assertNotNull($lead, 'Lead was not saved to database');
        $this->assertSame('Vikram Sharma', $lead->name);
        $this->assertSame('details_entry', $lead->current_stage);

        // Assert documents stored
        $this->assertCount(6, $lead->leadDocuments);
        foreach ($lead->leadDocuments as $doc) {
            Storage::disk('public')->assertExists($doc->file_path);
        }

        // Assert sub-modules stored
        $this->assertNotNull(\App\Models\ApplicationForm::where('lead_id', $lead->id)->first());
        $this->assertNotNull(\App\Models\StockStatement::where('lead_id', $lead->id)->first());
        $this->assertNotNull(\App\Models\OpinionReport::where('lead_id', $lead->id)->first());
        $this->assertNotNull(\App\Models\CmaProfile::where('lead_id', $lead->id)->first());
        $this->assertNotNull(\App\Models\DprReport::where('lead_id', $lead->id)->first());

        // Test Show Page
        $showResponse = $this->actingAs($this->admin)->get(route('admin.leads.show', $lead));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Vikram Sharma');
        $showResponse->assertSee('aadhaar_front.pdf');

        // Test Edit Page
        $editResponse = $this->actingAs($this->admin)->get(route('admin.leads.edit', $lead));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Vikram Sharma');

        // Test Document Download
        $firstDoc = $lead->leadDocuments->first();
        $downloadResponse = $this->actingAs($this->admin)->get(route('admin.leads.documents.download', [$lead, $firstDoc]));
        $downloadResponse->assertStatus(200);

        // Test Download All Documents
        $downloadAllResponse = $this->actingAs($this->admin)->get(route('admin.leads.download-all', $lead));
        $downloadAllResponse->assertStatus(200);
    }
}
