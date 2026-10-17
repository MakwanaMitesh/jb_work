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
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GranularDocumentPermissionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employee;
    private Bank $sbiBank;
    private LoanProduct $businessLoan;
    private City $city;
    private Agent $agent;
    private CustomerConstitution $constitution;
    private Lead $lead;

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

        $this->employee = User::factory()->create([
            'email' => 'employee@example.com',
            'status' => 'active',
        ]);
        $this->employee->assignRole('Employee');

        $this->city = City::create(['name' => 'Ahmedabad', 'status' => 'active']);
        $this->agent = Agent::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
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

        $this->businessLoan = LoanProduct::firstOrCreate(
            ['code' => 'BL'],
            ['name' => 'Business Loan', 'status' => 'active']
        );

        $this->lead = Lead::create([
            'name' => 'Alpha Industrial Supplies',
            'business_name' => 'Alpha Industrial Supplies',
            'firm_name' => 'Alpha Industrial Supplies',
            'mobile_number' => '+919876543210',
            'email' => 'alpha@example.com',
            'bank_id' => $this->sbiBank->id,
            'loan_product_id' => $this->businessLoan->id,
            'city_id' => $this->city->id,
            'constitution_id' => $this->constitution->id,
            'agent_id' => $this->agent->id,
            'status' => 'under_process',
            'required_loan_amount' => 5000000,
            'bank_loan_details' => [
                'applicant_coapplicant_guarantor_name' => 'Alpha Industries',
                'visit_office_date' => '2026-03-20',
                'office_organization_address' => 'GIDC Vatva, Ahmedabad',
                'office_tvr_result' => 'Positive',
            ],
        ]);
    }

    protected function tearDown(): void
    {
        if (isset($this->lead)) {
            \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory('cma/' . $this->lead->id);
            \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory('dpr/' . $this->lead->id);
            \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory('or/' . $this->lead->id);
            \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory('sbi_stock_statement/' . $this->lead->id);
            \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory('inspection_sheets/' . $this->lead->id);
            \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory('leads/' . $this->lead->id);
        }

        parent::tearDown();
    }

    public function test_permissions_are_seeded_with_correct_modules(): void
    {
        $this->assertDatabaseHas('permissions', ['name' => 'cma.generate', 'module' => 'cma']);
        $this->assertDatabaseHas('permissions', ['name' => 'cma.download', 'module' => 'cma']);
        $this->assertDatabaseHas('permissions', ['name' => 'dpr.generate', 'module' => 'dpr']);
        $this->assertDatabaseHas('permissions', ['name' => 'dpr.download', 'module' => 'dpr']);
        $this->assertDatabaseHas('permissions', ['name' => 'or.generate', 'module' => 'or']);
        $this->assertDatabaseHas('permissions', ['name' => 'or.download', 'module' => 'or']);
        $this->assertDatabaseHas('permissions', ['name' => 'sbi_stock_statement.generate', 'module' => 'sbi_stock_statement']);
        $this->assertDatabaseHas('permissions', ['name' => 'sbi_stock_statement.download', 'module' => 'sbi_stock_statement']);
        $this->assertDatabaseHas('permissions', ['name' => 'inspection_sheet.generate', 'module' => 'inspection_sheet']);
        $this->assertDatabaseHas('permissions', ['name' => 'inspection_sheet.download', 'module' => 'inspection_sheet']);
        $this->assertDatabaseHas('permissions', ['name' => 'documents.upload', 'module' => 'documents']);
        $this->assertDatabaseHas('permissions', ['name' => 'documents.download', 'module' => 'documents']);
        $this->assertDatabaseHas('permissions', ['name' => 'documents.download_all', 'module' => 'documents']);
        $this->assertDatabaseHas('permissions', ['name' => 'documents.delete', 'module' => 'documents']);
    }

    public function test_cma_generate_permission_allows_generation_and_shows_button_while_download_is_blocked(): void
    {
        $this->employee->givePermissionTo(['leads.view', 'cma.generate']);

        $response = $this->actingAs($this->employee)->get(route('admin.leads.show', $this->lead));
        $response->assertOk();
        $response->assertSee('Generate CMA');
        $response->assertDontSee('Download CMA');

        // Generation succeeds
        $genResponse = $this->actingAs($this->employee)->post(route('admin.leads.cma.generate', $this->lead));
        $genResponse->assertRedirect();
        $this->assertTrue(Storage::disk('public')->exists('cma/' . $this->lead->id . '/CMA.xlsx'));

        // Direct download is blocked with 403
        $this->actingAs($this->employee)
            ->get(route('admin.leads.cma.download', $this->lead))
            ->assertForbidden();
    }

    public function test_cma_download_permission_allows_downloading_existing_file_while_generation_is_blocked(): void
    {
        // Pre-create CMA file
        Storage::disk('public')->put('cma/' . $this->lead->id . '/CMA.xlsx', 'dummy-cma-content');

        $this->employee->givePermissionTo(['leads.view', 'cma.download']);

        $response = $this->actingAs($this->employee)->get(route('admin.leads.show', $this->lead));
        $response->assertOk();
        $response->assertSee('Download CMA');
        $response->assertDontSee('Generate CMA');

        // Download succeeds for existing file
        $downResponse = $this->actingAs($this->employee)->get(route('admin.leads.cma.download', $this->lead));
        $downResponse->assertOk();

        // Generation route is blocked with 403
        $this->actingAs($this->employee)
            ->post(route('admin.leads.cma.generate', $this->lead))
            ->assertForbidden();
    }

    public function test_cma_download_without_generate_permission_fails_if_file_does_not_exist(): void
    {
        $this->employee->givePermissionTo(['leads.view', 'cma.download']);

        // No file exists, so attempting to download returns 403 because user cannot generate
        $this->actingAs($this->employee)
            ->get(route('admin.leads.cma.download', $this->lead))
            ->assertForbidden();
    }

    public function test_dpr_generate_and_download_permissions_work_independently(): void
    {
        // 1. Only dpr.generate
        $this->employee->givePermissionTo(['leads.view', 'dpr.generate']);

        $response = $this->actingAs($this->employee)->get(route('admin.leads.show', $this->lead));
        $response->assertSee('Generate DPR');
        $response->assertDontSee('Download DPR');

        $this->actingAs($this->employee)
            ->post(route('admin.leads.dpr.generate', $this->lead))
            ->assertRedirect();
        $this->assertTrue(Storage::disk('public')->exists('dpr/' . $this->lead->id . '/DPR.docx'));

        $this->actingAs($this->employee)
            ->get(route('admin.leads.dpr.download', $this->lead))
            ->assertForbidden();

        // 2. Switch to only dpr.download
        $this->employee->revokePermissionTo('dpr.generate');
        $this->employee->givePermissionTo('dpr.download');

        $response2 = $this->actingAs($this->employee)->get(route('admin.leads.show', $this->lead));
        $response2->assertSee('Download DPR');
        $response2->assertDontSee('Generate DPR');

        $this->actingAs($this->employee)
            ->get(route('admin.leads.dpr.download', $this->lead))
            ->assertOk();

        $this->actingAs($this->employee)
            ->post(route('admin.leads.dpr.generate', $this->lead))
            ->assertForbidden();
    }

    public function test_or_generate_and_download_permissions_work_independently(): void
    {
        // 1. Only or.generate
        $this->employee->givePermissionTo(['leads.view', 'or.generate']);

        $response = $this->actingAs($this->employee)->get(route('admin.leads.show', $this->lead));
        $response->assertSee('Generate OR');
        $response->assertDontSee('Download OR');

        $this->actingAs($this->employee)
            ->post(route('admin.leads.or.generate', $this->lead))
            ->assertRedirect();
        $this->assertTrue(Storage::disk('public')->exists('or/' . $this->lead->id . '/OR.docx'));

        $this->actingAs($this->employee)
            ->get(route('admin.leads.or.download', $this->lead))
            ->assertForbidden();

        // 2. Switch to only or.download
        $this->employee->revokePermissionTo('or.generate');
        $this->employee->givePermissionTo('or.download');

        $response2 = $this->actingAs($this->employee)->get(route('admin.leads.show', $this->lead));
        $response2->assertSee('Download OR');
        $response2->assertDontSee('Generate OR');

        $this->actingAs($this->employee)
            ->get(route('admin.leads.or.download', $this->lead))
            ->assertOk();

        $this->actingAs($this->employee)
            ->post(route('admin.leads.or.generate', $this->lead))
            ->assertForbidden();
    }

    public function test_sbi_stock_statement_generate_and_download_permissions_work_independently(): void
    {
        // 1. Only sbi_stock_statement.generate
        $this->employee->givePermissionTo(['leads.view', 'sbi_stock_statement.generate']);

        $response = $this->actingAs($this->employee)->get(route('admin.leads.show', $this->lead));
        $response->assertSee('Generate SBI Stock Statement');
        $response->assertDontSee('Download SBI Stock Statement');

        $this->actingAs($this->employee)
            ->post(route('admin.leads.sbi-stock-statement.generate', $this->lead))
            ->assertRedirect();
        $this->assertTrue(
            Storage::disk('public')->exists('sbi-stock-statement/' . $this->lead->id . '/SBI_Stock_Statement.xlsx')
            || Storage::disk('public')->exists('sbi_stock_statement/' . $this->lead->id . '/SBI_Stock_statement.xlsx')
        );

        $this->actingAs($this->employee)
            ->get(route('admin.leads.sbi-stock-statement.download', $this->lead))
            ->assertForbidden();

        // 2. Switch to only sbi_stock_statement.download
        $this->employee->revokePermissionTo('sbi_stock_statement.generate');
        $this->employee->givePermissionTo('sbi_stock_statement.download');

        $response2 = $this->actingAs($this->employee)->get(route('admin.leads.show', $this->lead));
        $response2->assertSee('Download SBI Stock Statement');
        $response2->assertDontSee('Generate SBI Stock Statement');

        $this->actingAs($this->employee)
            ->get(route('admin.leads.sbi-stock-statement.download', $this->lead))
            ->assertOk();

        $this->actingAs($this->employee)
            ->post(route('admin.leads.sbi-stock-statement.generate', $this->lead))
            ->assertForbidden();
    }

    public function test_inspection_sheet_generate_and_download_permissions_work_independently(): void
    {
        // 1. Only inspection_sheet.generate
        $this->employee->givePermissionTo(['leads.view', 'inspection_sheet.generate']);

        $response = $this->actingAs($this->employee)->get(route('admin.leads.show', $this->lead));
        $response->assertSee('Generate Inspection Sheet');
        $response->assertDontSee('Download Inspection Sheet (PDF)');

        $this->actingAs($this->employee)
            ->post(route('admin.leads.inspection-sheet.generate', $this->lead))
            ->assertRedirect();
        $this->assertTrue(Storage::disk('public')->exists('inspection_sheets/' . $this->lead->id . '/inspection_sheet_lead_' . $this->lead->id . '.pdf'));

        $this->actingAs($this->employee)
            ->get(route('admin.leads.inspection-sheet.pdf', $this->lead))
            ->assertForbidden();

        // 2. Switch to only inspection_sheet.download
        $this->employee->revokePermissionTo('inspection_sheet.generate');
        $this->employee->givePermissionTo('inspection_sheet.download');

        $response2 = $this->actingAs($this->employee)->get(route('admin.leads.show', $this->lead));
        $response2->assertSee('Download Inspection Sheet (PDF)');
        $response2->assertDontSee('Generate Inspection Sheet');

        $this->actingAs($this->employee)
            ->get(route('admin.leads.inspection-sheet.pdf', $this->lead))
            ->assertOk();

        $this->actingAs($this->employee)
            ->post(route('admin.leads.inspection-sheet.generate', $this->lead))
            ->assertForbidden();
    }

    public function test_documents_download_all_permission_controls_uploaded_docs_zip_only(): void
    {
        $docType = DocumentType::create([
            'name' => 'PAN Card',
            'code' => 'pan_card',
            'allowed_file_types' => ['pdf'],
            'status' => 'active',
        ]);

        $filePath = 'leads/' . $this->lead->id . '/documents/pan.pdf';
        Storage::disk('public')->put($filePath, 'pan-file-content');
        LeadDocument::create([
            'lead_id' => $this->lead->id,
            'document_type_id' => $docType->id,
            'file_path' => $filePath,
            'original_name' => 'PAN.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'uploaded_by' => $this->admin->id,
        ]);

        // User has only documents.download_all and leads.view
        $this->employee->givePermissionTo(['leads.view', 'documents.download_all']);

        $response = $this->actingAs($this->employee)->get(route('admin.leads.show', $this->lead));
        $response->assertSee('Download All Uploaded Documents');
        // Generated document buttons should NOT be visible
        $response->assertDontSee('Download CMA');
        $response->assertDontSee('Generate CMA');

        // Can download uploaded documents ZIP
        $zipResponse = $this->actingAs($this->employee)->get(route('admin.leads.uploaded-documents.download-all', $this->lead));
        $zipResponse->assertOk();

        // Cannot download generated CMA
        $this->actingAs($this->employee)
            ->get(route('admin.leads.cma.download', $this->lead))
            ->assertForbidden();

        // Without documents.download_all, uploaded docs zip download is blocked
        $this->employee->revokePermissionTo('documents.download_all');
        $this->actingAs($this->employee)
            ->get(route('admin.leads.uploaded-documents.download-all', $this->lead))
            ->assertForbidden();
    }

    public function test_uploaded_document_crud_permissions_are_strictly_enforced(): void
    {
        $docType = DocumentType::create([
            'name' => 'Aadhaar Card',
            'code' => 'aadhaar_card',
            'allowed_file_types' => ['pdf', 'jpg'],
            'status' => 'active',
        ]);

        $filePath = 'leads/' . $this->lead->id . '/documents/aadhaar.pdf';
        Storage::disk('public')->put($filePath, 'aadhaar-file-content');
        $leadDoc = LeadDocument::create([
            'lead_id' => $this->lead->id,
            'document_type_id' => $docType->id,
            'file_path' => $filePath,
            'original_name' => 'Aadhaar.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'uploaded_by' => $this->admin->id,
        ]);

        // 1. Without documents.download: direct download returns 403
        $this->employee->givePermissionTo(['leads.view']);
        $this->actingAs($this->employee)
            ->get(route('admin.leads.documents.download', [$this->lead, $leadDoc]))
            ->assertForbidden();

        // With documents.download: download works
        $this->employee->givePermissionTo('documents.download');
        $this->actingAs($this->employee)
            ->get(route('admin.leads.documents.download', [$this->lead, $leadDoc]))
            ->assertOk();

        // 2. Without documents.delete: delete returns 403
        $this->actingAs($this->employee)
            ->delete(route('admin.leads.documents.destroy', [$this->lead, $leadDoc]))
            ->assertForbidden();

        // With documents.delete: delete works
        $this->employee->givePermissionTo('documents.delete');
        $this->actingAs($this->employee)
            ->delete(route('admin.leads.documents.destroy', [$this->lead, $leadDoc]))
            ->assertRedirect();
        $this->assertDatabaseMissing('lead_documents', ['id' => $leadDoc->id]);
    }

    public function test_super_admin_has_unrestricted_access_to_all_actions(): void
    {
        // Admin can generate and download everything without individual permission assignments
        $response = $this->actingAs($this->admin)->get(route('admin.leads.show', $this->lead));
        $response->assertOk();
        $response->assertSee('Generate CMA');
        $response->assertSee('Download CMA');
        $response->assertSee('Generate DPR');
        $response->assertSee('Download DPR');
        $response->assertSee('Generate OR');
        $response->assertSee('Download OR');
        $response->assertSee('Generate SBI Stock Statement');
        $response->assertSee('Download SBI Stock Statement');
        $response->assertSee('Generate Inspection Sheet');
        $response->assertSee('Download Inspection Sheet (PDF)');

        // Admin generate actions work
        $this->actingAs($this->admin)->post(route('admin.leads.cma.generate', $this->lead))->assertRedirect();
        $this->actingAs($this->admin)->post(route('admin.leads.dpr.generate', $this->lead))->assertRedirect();
        $this->actingAs($this->admin)->post(route('admin.leads.or.generate', $this->lead))->assertRedirect();
        $this->actingAs($this->admin)->post(route('admin.leads.sbi-stock-statement.generate', $this->lead))->assertRedirect();
        $this->actingAs($this->admin)->post(route('admin.leads.inspection-sheet.generate', $this->lead))->assertRedirect();

        // Admin download actions work
        $this->actingAs($this->admin)->get(route('admin.leads.cma.download', $this->lead))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.leads.dpr.download', $this->lead))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.leads.or.download', $this->lead))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.leads.sbi-stock-statement.download', $this->lead))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.leads.inspection-sheet.pdf', $this->lead))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.leads.download-all', $this->lead))->assertOk();
    }
}
