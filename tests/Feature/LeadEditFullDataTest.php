<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\City;
use App\Models\CustomerConstitution;
use App\Models\DocumentType;
use App\Models\Lead;
use App\Models\LeadDocument;
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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeadEditFullDataTest extends TestCase
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
            ['code' => 'EDIT_TEST_PROD'],
            ['name' => 'Home Loan Edit Test', 'status' => 'active']
        );

        $this->constitution = CustomerConstitution::firstOrCreate(
            ['code' => 'EDIT_TEST_CONST'],
            ['name' => 'Proprietorship', 'status' => 'active']
        );

        LoanProductConstitution::firstOrCreate([
            'loan_product_id' => $this->product->id,
            'constitution_id' => $this->constitution->id,
        ], ['status' => 'active']);

        $this->city = City::firstOrCreate(['name' => 'Ahmedabad'], ['status' => 'active']);
        $this->bank = Bank::firstOrCreate(['name' => 'State Bank of India'], ['status' => 'active']);
    }

    public function test_lead_can_be_edited_updated_and_documents_managed(): void
    {
        Storage::fake('public');

        // 1. Create initial lead
        $panDocFile = UploadedFile::fake()->create('pan_old.pdf', 150, 'application/pdf');
        $stmt1 = UploadedFile::fake()->create('stmt_1.pdf', 200, 'application/pdf');

        $lead = Lead::create([
            'name' => 'Original Customer',
            'business_name' => 'Original Business',
            'email' => 'original@example.com',
            'mobile_number' => '+919876543210',
            'loan_product_id' => $this->product->id,
            'constitution_id' => $this->constitution->id,
            'city_id' => $this->city->id,
            'bank_id' => $this->bank->id,
            'status' => 'new',
            'required_loan_amount' => '1000000',
        ]);

        $panType = DocumentType::where('code', 'pan_card')->first();
        $bankType = DocumentType::where('code', 'bank_statement')->first();

        $lead->leadDocuments()->create([
            'document_type_id' => $panType->id,
            'file_path' => $panDocFile->store("leads/{$lead->id}/documents", 'public'),
            'original_name' => 'pan_old.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 150000,
            'uploaded_by' => $this->admin->id,
        ]);

        $lead->leadDocuments()->create([
            'document_type_id' => $bankType->id,
            'file_path' => $stmt1->store("leads/{$lead->id}/documents", 'public'),
            'original_name' => 'stmt_1.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 200000,
            'uploaded_by' => $this->admin->id,
        ]);

        // 2. Test Edit view loads cleanly
        $editResponse = $this->actingAs($this->admin)->get(route('admin.leads.edit', $lead));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Original Customer');
        $editResponse->assertSee('pan_old.pdf');

        // 3. Test Updating lead data and documents
        $newPanFile = UploadedFile::fake()->create('pan_updated.pdf', 180, 'application/pdf');
        $newStmt2 = UploadedFile::fake()->create('stmt_2.pdf', 250, 'application/pdf');

        $updatePayload = [
            'name' => 'Updated Customer Name',
            'business_name' => 'Updated Business Name',
            'email' => 'updated@example.com',
            'mobile_number' => '+919876543299',
            'loan_product_id' => $this->product->id,
            'constitution_id' => $this->constitution->id,
            'city_id' => $this->city->id,
            'bank_id' => $this->bank->id,
            'status' => 'contacted',
            'required_loan_amount' => '2500000',
            'cc_amount' => '1500000',
            'term_loan_amount' => '1000000',
            'documents' => [
                'pan_card' => [
                    'file' => $newPanFile,
                ],
                'bank_statement' => [
                    'files' => [$newStmt2],
                ],
            ],
        ];

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.leads.update', $lead), $updatePayload);
        $updateResponse->assertRedirect(route('admin.leads.index'));

        // 4. Assert updated lead values
        $freshLead = $lead->fresh();
        $this->assertSame('Updated Customer Name', $freshLead->name);
        $this->assertSame('Updated Business Name', $freshLead->business_name);
        $this->assertSame('updated@example.com', $freshLead->email);
        $this->assertSame('+919876543299', $freshLead->mobile_number);
        $this->assertSame('contacted', $freshLead->status);
        $this->assertSame('2500000', $freshLead->required_loan_amount);

        // 5. Assert document replacement: pan_old replaced by pan_updated
        $currentPan = $freshLead->leadDocuments()->where('document_type_id', $panType->id)->first();
        $this->assertSame('pan_updated.pdf', $currentPan->original_name);
        Storage::disk('public')->assertExists($currentPan->file_path);

        // 6. Assert multi-document addition: bank statement count is 2 (stmt_1 + stmt_2)
        $bankDocs = $freshLead->leadDocuments()->where('document_type_id', $bankType->id)->get();
        $this->assertCount(2, $bankDocs);

        // 7. Test Document Deletion
        $docToDelete = $bankDocs->first();
        $deleteDocResponse = $this->actingAs($this->admin)->delete(route('admin.leads.documents.destroy', [$freshLead, $docToDelete]));
        $deleteDocResponse->assertRedirect();

        $this->assertDatabaseMissing('lead_documents', ['id' => $docToDelete->id]);
        Storage::disk('public')->assertMissing($docToDelete->file_path);
        $this->assertSame(1, $freshLead->fresh()->leadDocuments()->where('document_type_id', $bankType->id)->count());

        // 8. Test Show page after updates
        $showResponse = $this->actingAs($this->admin)->get(route('admin.leads.show', $freshLead));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Updated Customer Name');
        $showResponse->assertSee('pan_updated.pdf');
    }

    public function test_edit_updates_every_single_field_across_all_modules(): void
    {
        Storage::fake('public');

        $lead = Lead::create([
            'name' => 'Initial Name',
            'mobile_number' => '+919876543210',
            'loan_product_id' => $this->product->id,
            'constitution_id' => $this->constitution->id,
            'status' => 'new',
        ]);

        $city2 = City::firstOrCreate(['name' => 'Surat'], ['status' => 'active']);
        $bank2 = Bank::firstOrCreate(['name' => 'Bank of Baroda'], ['status' => 'active']);

        $updateAll = [
            // Core Details Sheet
            'name' => 'Anilbhai Shantilal Shah',
            'date_of_birth' => '1982-11-25',
            'place_of_birth' => 'Surat',
            'occupation' => 'Textile Engineering',
            'gender' => 'Male',
            'mobile_number' => '+919988776655',
            'alternate_mobile_number' => '+919988776644',
            'email' => 'anil.shah@example.com',
            'address' => 'Plot 88, Surat Textile Park, Sachin GIDC, Surat - 394230',
            'aadhar_card' => '987654321098',
            'pan_card' => 'XYZPA9876Q',
            'udyam_registration' => 'UDYAM-GJ-02-0099887',
            'fssai_license' => '10020030040050',
            'education' => 'Master of Technology',
            'mother_name' => 'Champaben Shah',
            'father_full_name' => 'Shantilal Shah',
            'relationship' => 'Son',

            // ITR
            'itr_id' => 'XYZPA9876Q',
            'itr_password' => 'NewItrPass@2027',
            'itr_audited' => 'Yes',
            'itr_ay_2026_27' => 1,
            'itr_ay_2025_26' => 1,
            'itr_ay_2024_25' => 1,

            // Relations & Meta
            'bank_id' => $bank2->id,
            'loan_product_id' => $this->product->id,
            'constitution_id' => $this->constitution->id,
            'source' => 'CA Reference',
            'city_id' => $city2->id,
            'status' => 'in_progress',
            'notes' => 'Expansion unit in Sachin GIDC.',

            // Pre Sanction
            'bank_loan_details' => [
                'applicant_name' => 'Anilbhai Shantilal Shah - Prop. Of Shah Automations',
                'office_address' => 'Plot 88, Sachin GIDC, Surat',
                'business_years' => '15 Years',
                'type_of_organization' => ['Proprietorship'],
                'nature_of_business' => ['Manufacturing'],
                'office_ownership' => ['Owned'],
                'visit_office_date' => '2026-11-21',
                'office_tvr_result' => 'Positive',
                'inspection_remarks' => 'Plant verified.',
                'branch_name' => 'Sachin Branch',
            ],

            // Bank details
            'bank_details' => [
                [
                    'bank_name' => 'Bank of Baroda',
                    'account_number' => '898900112233',
                    'account_type' => 'Current',
                    'ifsc_code' => 'BARB0SACHIN',
                ],
            ],

            // Business details
            'business_name' => 'Shah Advanced Automations',
            'constitution_of_business' => 'Proprietorship',
            'introduction' => 'Precision automation systems.',
            'business_address' => 'Plot 88, Sachin GIDC, Surat',
            'gst_applicable' => 'Yes',
            'gst_number' => '24XYZPA9876Q1Z9',
            'gst_id' => 'shahautomation',
            'gst_password' => 'NewGstPass@2027',
            'firm_name' => 'Shah Advanced Automations',
            'business_activity' => 'CNC Machining',
            'business_experience' => '15 Years',
            'no_of_manpower' => '35 Technicians',
            'business_location' => 'Sachin GIDC',
            'area_of_premises' => '10000 Sq Ft',
            'land_and_factory_building' => 'Heavy Industrial Shed',
            'connectivity' => 'Direct NH access',

            // Loan details
            'required_loan_amount' => '8500000',
            'cc_amount' => '5000000',
            'cc_details' => 'Working capital.',
            'term_loan_amount' => '3500000',
            'term_loan_machinery_details' => '6-axis robotic welding.',
            'current_loans' => [
                [
                    'bank_name' => 'Union Bank of India',
                    'loan_type' => 'Equipment Loan',
                    'loan_amount' => '2500000',
                    'emi' => '55000',
                    'outstanding_amount' => '650000',
                ],
            ],

            // Application Form
            'af' => [
                'enterprise_name' => 'Shah Advanced Automations',
                'applicant_name' => 'Anilbhai Shantilal Shah',
                'branch_name' => 'Sachin Branch',
                'category' => 'Tarun',
                'constitution' => 'Proprietary',
                'business_address' => 'Plot 88, Sachin GIDC, Surat',
                'state' => 'Gujarat',
                'pincode' => '394230',
                'business_premises' => 'Owned',
                'mobile_number' => '9988776655',
                'email' => 'anil.shah@example.com',
                'activity_existing' => 'Automation',
                'activity_proposed' => 'Expansion',
                'date_of_birth' => '1982-11-25',
                'sex' => 'M',
                'residential_address' => 'Plot 88, Surat Textile Park',
                'applicant_mobile' => '9988776655',
                'qualification' => 'Master of Technology',
                'experience_years' => '15',
                'pan_din_number' => 'XYZPA9876Q',
                'customer_id' => 'BOB-CUST-8899',
            ],

            // Stock Statement
            'ss' => [
                'statement_date' => '2026-11-21',
                'company_name' => 'Shah Advanced Automations',
                'cc_limit' => '5000000',
                'place' => 'Surat',
                'summary_amount' => '6800000',
                'debtor_name' => 'Reliance Hazira',
                'debtor_amount' => '1850000',
                'sundry_creditors_year_end' => '1200000',
                'margin_stock' => '25',
                'margin_book_debts' => '40',
            ],

            // Opinion Report
            'opr' => [
                'deponent_name' => 'Anilbhai Shantilal Shah',
                'father_name' => 'Shantilal Shah',
                'date_of_birth' => '1982-11-25',
                'place_of_birth' => 'Surat',
                'occupation' => 'Industrialist',
                'residential_address' => 'Plot 88, Surat Textile Park',
                'office_address' => 'Plot 88, Sachin GIDC, Surat',
                'branch' => 'Sachin Branch',
                'borrower_name' => 'Shah Advanced Automations',
                'income_per_month' => '450000',
                'outside_india' => 'No',
            ],

            // CMA Profile
            'cma' => [
                'activity' => 'Robotics',
                'firm_name' => 'Shah Advanced Automations',
                'proprietor_name' => 'Anilbhai Shantilal Shah',
                'pan' => 'XYZPA9876Q',
                'udyam_no' => 'UDYAM-GJ-02-0099887',
                'mobile' => '9988776655',
                'email' => 'anil.shah@example.com',
                'address' => 'Plot 88, Sachin GIDC, Surat',
                'business_experience' => '15 Years',
                'area_of_operation' => 'Export',
                'manpower' => '35',
                'space' => '10000 sq ft',
                'connectivity' => 'Highway',
                'location' => 'Sachin GIDC',
                'electricity_water' => '125 HP',
            ],

            // DPR
            'dpr' => [
                'firm_name' => 'Shah Advanced Automations',
                'constitution' => 'Proprietorship',
                'applicant_name' => 'Anilbhai Shantilal Shah',
                'business_address' => 'Plot 88, Sachin GIDC, Surat',
                'pan' => 'XYZPA9876Q',
                'aadhaar' => '987654321098',
                'gender' => 'Male',
                'mobile' => '9988776655',
                'email' => 'anil.shah@example.com',
                'education' => 'Master of Technology',
                'mother_name' => 'Champaben Shah',
                'date_of_birth' => '1982-11-25',
                'experience' => '15 Years',
                'activity' => 'Precision Automation',
                'cost_machinery' => '4500000',
                'cost_working_capital' => '6500000',
                'fin_promoter_capital' => '2500000',
                'fin_term_loan' => '3500000',
                'fin_cash_credit' => '5000000',
            ],
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.leads.update', $lead), $updateAll);
        $response->assertRedirect(route('admin.leads.index'));

        $fresh = $lead->fresh();

        // Assert direct fields
        $this->assertSame('Anilbhai Shantilal Shah', $fresh->name);
        $this->assertSame('Shah Advanced Automations', $fresh->business_name);
        $this->assertSame('+919988776655', $fresh->mobile_number);
        $this->assertSame('anil.shah@example.com', $fresh->email);
        $this->assertSame('XYZPA9876Q', $fresh->pan_card);
        $this->assertSame('987654321098', $fresh->aadhar_card);
        $this->assertSame('UDYAM-GJ-02-0099887', $fresh->udyam_registration);
        $this->assertSame('10020030040050', $fresh->fssai_license);
        $this->assertSame('Master of Technology', $fresh->education);
        $this->assertSame('Champaben Shah', $fresh->mother_name);
        $this->assertSame('Shantilal Shah', $fresh->father_full_name);
        $this->assertSame('Son', $fresh->relationship);
        $this->assertSame('Surat', $fresh->place_of_birth);
        $this->assertSame('Textile Engineering', $fresh->occupation);
        $this->assertSame('in_progress', $fresh->status);
        $this->assertSame('8500000', $fresh->required_loan_amount);
        $this->assertSame('5000000', $fresh->cc_amount);
        $this->assertSame('3500000', $fresh->term_loan_amount);
        $this->assertSame('Bank of Baroda', $fresh->bank_details[0]['bank_name']);
        $this->assertSame('Union Bank of India', $fresh->current_loans[0]['bank_name']);

        // Assert sub-modules
        $this->assertSame('Shah Advanced Automations', \App\Models\ApplicationForm::where('lead_id', $fresh->id)->value('enterprise_name'));
        $this->assertSame('Shah Advanced Automations', \App\Models\StockStatement::where('lead_id', $fresh->id)->value('company_name'));
        $this->assertSame('Anilbhai Shantilal Shah', \App\Models\OpinionReport::where('lead_id', $fresh->id)->value('deponent_name'));
        $this->assertSame('Shah Advanced Automations', \App\Models\CmaProfile::where('lead_id', $fresh->id)->value('firm_name'));
        $this->assertSame('4500000', (string)\App\Models\DprReport::where('lead_id', $fresh->id)->value('cost_machinery'));
    }
}
