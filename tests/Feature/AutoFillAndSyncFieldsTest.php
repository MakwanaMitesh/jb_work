<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\City;
use App\Models\CustomerConstitution;
use App\Models\Lead;
use App\Models\LoanProduct;
use App\Models\LoanProductConstitution;
use App\Models\User;
use App\Support\ApplicationFormData;
use App\Support\CmaProfileData;
use App\Support\DprData;
use App\Support\OpinionReportData;
use App\Support\StockStatementData;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoFillAndSyncFieldsTest extends TestCase
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
            ['code' => 'TEST_PROD'],
            ['name' => 'Business Loan Test', 'status' => 'active']
        );

        $this->constitution = CustomerConstitution::firstOrCreate(
            ['code' => 'TEST_CONST'],
            ['name' => 'Proprietorship', 'status' => 'active']
        );

        LoanProductConstitution::firstOrCreate([
            'loan_product_id' => $this->product->id,
            'constitution_id' => $this->constitution->id,
        ], ['status' => 'active']);

        $this->city = City::firstOrCreate(['name' => 'Ahmedabad'], ['status' => 'active']);
        $this->bank = Bank::firstOrCreate(['name' => 'State Bank of India'], ['status' => 'active']);
    }

    /**
     * Test that Customer Name automatically populates across all subsequent tabs
     * (Application Form, Stock Statement, Opinion Report, CMA, DPR, and Pre Sanction).
     */
    public function test_customer_name_auto_fills_across_all_subsequent_tabs(): void
    {
        $customerName = 'Rajeshbhai Kantilal Patel';

        $lead = Lead::create([
            'name' => $customerName,
            'mobile_number' => '+919876543210',
            'loan_product_id' => $this->product->id,
            'constitution_id' => $this->constitution->id,
            'status' => 'new',
        ]);

        // 1. Application Form (Applicant Name and Enterprise Name fallback)
        $afData = ApplicationFormData::for($lead);
        $this->assertSame($customerName, $afData['applicant_name'], 'Application Form applicant_name did not auto-fill customer name');
        $this->assertSame($customerName, $afData['enterprise_name'], 'Application Form enterprise_name fallback did not auto-fill customer name');

        // 2. Stock Statement (Company Name fallback)
        $ssData = StockStatementData::for($lead);
        $this->assertSame($customerName, $ssData['company_name'], 'Stock Statement company_name fallback did not auto-fill customer name');

        // 3. Opinion Report (Deponent Name and Borrower Name fallback)
        $oprData = OpinionReportData::for($lead);
        $this->assertSame($customerName, $oprData['deponent_name'], 'Opinion Report deponent_name did not auto-fill customer name');
        $this->assertSame($customerName, $oprData['borrower_name'], 'Opinion Report borrower_name fallback did not auto-fill customer name');

        // 4. CMA Profile (Proprietor Name and Firm Name fallback)
        $cmaData = CmaProfileData::for($lead);
        $this->assertSame($customerName, $cmaData['proprietor_name'], 'CMA proprietor_name did not auto-fill customer name');
        $this->assertSame($customerName, $cmaData['firm_name'], 'CMA firm_name fallback did not auto-fill customer name');

        // 5. DPR (Applicant Name)
        $dprData = DprData::for($lead);
        $this->assertSame($customerName, $dprData['applicant_name'], 'DPR applicant_name did not auto-fill customer name');
    }

    /**
     * Test that when both Customer Name and Business/Firm Name are provided,
     * the enterprise/company/firm fields use Business Name, while person fields use Customer Name.
     */
    public function test_business_name_and_customer_name_separate_appropriately(): void
    {
        $customerName = 'Rajeshbhai Patel';
        $businessName = 'Patel Precision Components';

        $lead = Lead::create([
            'name' => $customerName,
            'business_name' => $businessName,
            'mobile_number' => '+919876543210',
            'loan_product_id' => $this->product->id,
            'constitution_id' => $this->constitution->id,
            'status' => 'new',
        ]);

        // Application Form
        $afData = ApplicationFormData::for($lead);
        $this->assertSame($customerName, $afData['applicant_name']);
        $this->assertSame($businessName, $afData['enterprise_name']);

        // Stock Statement
        $ssData = StockStatementData::for($lead);
        $this->assertSame($businessName, $ssData['company_name']);

        // Opinion Report
        $oprData = OpinionReportData::for($lead);
        $this->assertSame($customerName, $oprData['deponent_name']);
        $this->assertSame($businessName, $oprData['borrower_name']);

        // CMA Profile
        $cmaData = CmaProfileData::for($lead);
        $this->assertSame($customerName, $cmaData['proprietor_name']);
        $this->assertSame($businessName, $cmaData['firm_name']);

        // DPR
        $dprData = DprData::for($lead);
        $this->assertSame($customerName, $dprData['applicant_name']);
        $this->assertSame($businessName, $dprData['firm_name']);
    }

    /**
     * Test all repeating fields auto-fill across all tabs seamlessly:
     * - Mobile, Email, Date of Birth, Gender, PAN, Aadhaar, Udyam,
     * - Address, Education, Father Name, Mother Name, Relationship,
     * - Business Activity, Experience, Manpower, Location, Amounts, Loans.
     */
    public function test_all_repeated_fields_auto_fill_across_all_tabs(): void
    {
        $lead = Lead::create([
            'name' => 'Vikram Sharma',
            'business_name' => 'Sharma Electricals',
            'mobile_number' => '+919876543210',
            'email' => 'vikram@example.com',
            'date_of_birth' => '1988-04-12',
            'gender' => 'Male',
            'address' => '101 Shivalik Heights, Satellite, Ahmedabad',
            'business_address' => 'Shop 5, Galaxy Complex, CG Road, Ahmedabad - 380009',
            'pan_card' => 'ABCDE1234F',
            'aadhar_card' => '123456789012',
            'udyam_registration' => 'UDYAM-GJ-01-0012345',
            'education' => 'Bachelor of Engineering',
            'mother_name' => 'Sunita Sharma',
            'father_full_name' => 'Ramesh Sharma',
            'relationship' => 'Son',
            'place_of_birth' => 'Ahmedabad',
            'occupation' => 'Manufacturing',
            'business_activity' => 'CNC Machining',
            'business_experience' => '10 Years',
            'no_of_manpower' => '15 Employees',
            'business_location' => 'Ahmedabad',
            'connectivity' => 'Prime location on main highway',
            'gst_number' => '24ABCDE1234F1Z5',
            'fssai_license' => '10019021000999',
            'land_and_factory_building' => 'Owned Industrial Shed',
            'cc_amount' => '2500000',
            'term_loan_amount' => '1500000',
            'bank_details' => [
                [
                    'bank_name' => 'State Bank of India',
                    'account_type' => 'Current',
                    'account_number' => '30123456789',
                    'ifsc_code' => 'SBIN0001234',
                ],
            ],
            'current_loans' => [
                [
                    'bank_name' => 'HDFC Bank',
                    'loan_type' => 'Business Loan',
                    'loan_amount' => '500000',
                    'emi' => '15000',
                    'outstanding_amount' => '200000',
                ],
            ],
            'loan_product_id' => $this->product->id,
            'constitution_id' => $this->constitution->id,
            'status' => 'new',
        ]);

        // 1. Application Form Verification
        $af = ApplicationFormData::for($lead);
        $this->assertSame('Vikram Sharma', $af['applicant_name']);
        $this->assertSame('Sharma Electricals', $af['enterprise_name']);
        $this->assertSame('+919876543210', $af['mobile_number']);
        $this->assertSame('+919876543210', $af['applicant_mobile']);
        $this->assertSame('91', $af['mobile_country_code']);
        $this->assertSame('vikram@example.com', $af['email']);
        $this->assertSame('1988-04-12', $af['date_of_birth']);
        $this->assertSame('M', $af['sex']); // Transformed Male -> M
        $this->assertSame('ABCDE1234F', $af['pan_din_number']);
        $this->assertSame('ABCDE1234F', $af['id_proof_number']);
        $this->assertSame('123456789012', $af['address_proof_number']);
        $this->assertSame('Yes', $af['is_registered']); // Udyam is present
        $this->assertSame('Bachelor of Engineering', $af['qualification']);
        $this->assertSame('10 Years', $af['experience_years']);
        $this->assertSame('101 Shivalik Heights, Satellite, Ahmedabad', $af['residential_address']);
        $this->assertSame('Shop 5, Galaxy Complex, CG Road, Ahmedabad - 380009', $af['business_address']);
        $this->assertSame('380009', $af['pincode']);
        $this->assertSame('CNC Machining', $af['activity_existing']);
        $this->assertSame('2500000', $af['proposed_facilities']['cash_credit']['amount']);
        $this->assertSame('1500000', $af['proposed_facilities']['term_loan']['amount']);

        // 2. Stock Statement Verification
        $ss = StockStatementData::for($lead);
        $this->assertSame('Sharma Electricals', $ss['company_name']);
        $this->assertSame('2500000', $ss['cc_limit']);
        $this->assertSame('Ahmedabad', $ss['place']);

        // 3. Opinion Report Verification
        $opr = OpinionReportData::for($lead);
        $this->assertSame('Vikram Sharma', $opr['deponent_name']);
        $this->assertSame('Sharma Electricals', $opr['borrower_name']);
        $this->assertSame('Ramesh Sharma', $opr['father_name']);
        $this->assertSame('Son', $opr['relationship']);
        $this->assertSame('1988-04-12', $opr['date_of_birth']);
        $this->assertSame('Ahmedabad', $opr['place_of_birth']);
        $this->assertSame('Manufacturing', $opr['occupation']);
        $this->assertSame('101 Shivalik Heights, Satellite, Ahmedabad', $opr['residential_address']);
        $this->assertSame('Shop 5, Galaxy Complex, CG Road, Ahmedabad - 380009', $opr['office_address']);

        // 4. CMA Profile Verification
        $cma = CmaProfileData::for($lead);
        $this->assertSame('Vikram Sharma', $cma['proprietor_name']);
        $this->assertSame('Sharma Electricals', $cma['firm_name']);
        $this->assertSame('+919876543210', $cma['mobile']);
        $this->assertSame('vikram@example.com', $cma['email']);
        $this->assertSame('ABCDE1234F', $cma['pan']);
        $this->assertSame('UDYAM-GJ-01-0012345', $cma['udyam_no']);
        $this->assertSame('CNC Machining', $cma['activity']);
        $this->assertSame('10 Years', $cma['business_experience']);
        $this->assertSame('15 Employees', $cma['manpower']);
        $this->assertSame('Prime location on main highway', $cma['connectivity']);
        $this->assertSame('Ahmedabad', $cma['area_of_operation']);
        $this->assertSame('Shop 5, Galaxy Complex, CG Road, Ahmedabad - 380009', $cma['address']);

        // 5. DPR Verification
        $dpr = DprData::for($lead);
        $this->assertSame('Vikram Sharma', $dpr['applicant_name']);
        $this->assertSame('Sharma Electricals', $dpr['firm_name']);
        $this->assertSame('+919876543210', $dpr['mobile']);
        $this->assertSame('vikram@example.com', $dpr['email']);
        $this->assertSame('1988-04-12', $dpr['date_of_birth']);
        $this->assertSame('Male', $dpr['gender']);
        $this->assertSame('ABCDE1234F', $dpr['pan']);
        $this->assertSame('123456789012', $dpr['aadhaar']);
        $this->assertSame('UDYAM-GJ-01-0012345', $dpr['udyam']);
        $this->assertSame('24ABCDE1234F1Z5', $dpr['gst']);
        $this->assertSame('10019021000999', $dpr['fssai']);
        $this->assertSame('Bachelor of Engineering', $dpr['education']);
        $this->assertSame('Sunita Sharma', $dpr['mother_name']);
        $this->assertSame('10 Years', $dpr['experience']);
        $this->assertSame('CNC Machining', $dpr['activity']);
        $this->assertSame('15 Employees', $dpr['manpower']);
        $this->assertSame('Prime location on main highway', $dpr['connectivity']);
        $this->assertSame('Owned Industrial Shed', $dpr['land_and_factory']);
        $this->assertSame('Shop 5, Galaxy Complex, CG Road, Ahmedabad - 380009', $dpr['business_address']);
        $this->assertSame('State Bank of India', $dpr['present_bank']);
        $this->assertSame('Current', $dpr['account_type']);
        $this->assertSame('30123456789', $dpr['account_number']);
        $this->assertSame('SBIN0001234', $dpr['ifsc']);
        $this->assertSame('1500000', $dpr['term_required']);
        $this->assertSame('2500000', $dpr['cc_required']);
        $this->assertSame('1500000', $dpr['fin_term_loan']);
        $this->assertSame('2500000', $dpr['fin_cash_credit']);
        $this->assertSame('2500000', $dpr['wc_amount']);

        // Check DPR loan row auto-fill
        $this->assertNotEmpty($dpr['current_loans']);
        $this->assertSame('HDFC Bank', $dpr['current_loans'][0]['bank_name']);
        $this->assertSame('Business Loan', $dpr['current_loans'][0]['loan_type']);
        $this->assertSame('500000', $dpr['current_loans'][0]['loan_amount']);
    }

    /**
     * Test form HTML outputs config and script blocks that drive browser live sync.
     */
    public function test_form_html_contains_live_sync_scripts_for_all_linked_fields(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.leads.create'));
        $content = $response->getContent();

        // 1. Pre Sanction sync function in _form.blade.php
        $this->assertStringContainsString('function syncBankLoanLive()', $content);
        $this->assertStringContainsString('function bankLoanDerived()', $content);

        // 2. Application Form live linking script
        $this->assertStringContainsString('af_applicant_name', $content);
        $this->assertStringContainsString('"path":"applicant_name"', $content);
        $this->assertStringContainsString('"path":"enterprise_name"', $content);

        // 3. Stock Statement live linking script
        $this->assertStringContainsString('"path":"company_name"', $content);

        // 4. Opinion Report live linking script
        $this->assertStringContainsString('"path":"deponent_name"', $content);
        $this->assertStringContainsString('"path":"borrower_name"', $content);

        // 5. CMA Profile live linking script
        $this->assertStringContainsString('"path":"proprietor_name"', $content);
        $this->assertStringContainsString('"path":"firm_name"', $content);

        // 6. DPR live linking script
        $this->assertStringContainsString('syncLoans', $content);
        $this->assertStringContainsString('"path":"applicant_name"', $content);
        $this->assertStringContainsString('"path":"firm_name"', $content);
    }
}
