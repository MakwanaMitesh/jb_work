<?php

namespace Tests\Feature;

use App\Models\CustomerConstitution;
use App\Models\DocumentType;
use App\Models\Lead;
use App\Models\LeadDocument;
use App\Models\LoanProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeadDocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $otherUser;
    protected LoanProduct $product;
    protected CustomerConstitution $constitution;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::factory()->create(['status' => 'active']);
        $this->admin->assignRole('Admin');

        $this->otherUser = User::factory()->create(['status' => 'active']);

        $this->product = LoanProduct::create([
            'name' => 'Test Business Loan',
            'code' => 'TBL-' . uniqid(),
            'status' => 'active',
        ]);
        $this->constitution = CustomerConstitution::create([
            'name' => 'Proprietorship',
            'code' => 'PROP-' . uniqid(),
            'status' => 'active',
        ]);

        \DB::table('loan_product_constitutions')->insert([
            'loan_product_id' => $this->product->id,
            'constitution_id' => $this->constitution->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function validLeadPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'John Document Test',
            'mobile_number' => '+919876543210',
            'loan_product_id' => $this->product->id,
            'constitution_id' => $this->constitution->id,
            'email' => 'johndoc@example.com',
            'status' => 'new',
        ], $overrides);
    }

    public function test_single_document_upload(): void
    {
        Storage::fake('public');

        $panType = DocumentType::where('code', 'pan_card')->firstOrFail();
        $file = UploadedFile::fake()->create('pan_card.pdf', 200, 'application/pdf');

        $payload = $this->validLeadPayload([
            'documents' => [
                'pan_card' => [
                    'file' => $file,
                ],
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leads.store'), $payload);

        $response->assertRedirect(route('admin.leads.index'));

        $lead = Lead::where('email', 'johndoc@example.com')->firstOrFail();
        $this->assertDatabaseHas('lead_documents', [
            'lead_id' => $lead->id,
            'document_type_id' => $panType->id,
            'original_name' => 'pan_card.pdf',
        ]);

        $docRecord = $lead->leadDocuments->first();
        Storage::disk('public')->assertExists($docRecord->file_path);
    }

    public function test_multiple_document_upload(): void
    {
        Storage::fake('public');

        $bankStatementType = DocumentType::where('code', 'bank_statement')->firstOrFail();
        $file1 = UploadedFile::fake()->create('stmt_jan.pdf', 300, 'application/pdf');
        $file2 = UploadedFile::fake()->create('stmt_feb.pdf', 300, 'application/pdf');

        $payload = $this->validLeadPayload([
            'documents' => [
                'bank_statement' => [
                    'files' => [$file1, $file2],
                ],
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leads.store'), $payload);

        $response->assertRedirect(route('admin.leads.index'));

        $lead = Lead::where('email', 'johndoc@example.com')->firstOrFail();
        $this->assertCount(2, $lead->leadDocuments);
        $this->assertDatabaseHas('lead_documents', [
            'lead_id' => $lead->id,
            'document_type_id' => $bankStatementType->id,
            'original_name' => 'stmt_jan.pdf',
        ]);
        $this->assertDatabaseHas('lead_documents', [
            'lead_id' => $lead->id,
            'document_type_id' => $bankStatementType->id,
            'original_name' => 'stmt_feb.pdf',
        ]);
    }

    public function test_front_and_back_document_upload(): void
    {
        Storage::fake('public');

        $aadharType = DocumentType::where('code', 'aadhar_card')->firstOrFail();
        $front = UploadedFile::fake()->create('aadhar_front.jpg', 250, 'image/jpeg');
        $back = UploadedFile::fake()->create('aadhar_back.jpg', 250, 'image/jpeg');

        $payload = $this->validLeadPayload([
            'documents' => [
                'aadhar_card' => [
                    'front' => $front,
                    'back' => $back,
                ],
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leads.store'), $payload);

        $response->assertRedirect(route('admin.leads.index'));

        $lead = Lead::where('email', 'johndoc@example.com')->firstOrFail();
        $this->assertDatabaseHas('lead_documents', [
            'lead_id' => $lead->id,
            'document_type_id' => $aadharType->id,
            'side' => 'front',
            'original_name' => 'aadhar_front.jpg',
        ]);
        $this->assertDatabaseHas('lead_documents', [
            'lead_id' => $lead->id,
            'document_type_id' => $aadharType->id,
            'side' => 'back',
            'original_name' => 'aadhar_back.jpg',
        ]);
    }

    public function test_required_document_validation(): void
    {
        // Set PAN Card as required
        $panType = DocumentType::where('code', 'pan_card')->firstOrFail();
        $panType->update(['is_required' => true]);

        $payload = $this->validLeadPayload([
            'documents' => [],
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leads.store'), $payload);

        $response->assertSessionHasErrors('documents.pan_card');
    }

    public function test_invalid_file_type_validation(): void
    {
        Storage::fake('public');

        // Photo type allows jpg, jpeg, png
        $photoType = DocumentType::where('code', 'photo')->firstOrFail();
        $exeFile = UploadedFile::fake()->create('malicious.exe', 100, 'application/x-msdownload');

        $payload = $this->validLeadPayload([
            'documents' => [
                'photo' => [
                    'file' => $exeFile,
                ],
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leads.store'), $payload);

        $response->assertSessionHasErrors('documents.photo');
    }

    public function test_file_size_validation(): void
    {
        Storage::fake('public');

        $photoType = DocumentType::where('code', 'photo')->firstOrFail();
        $photoType->update(['max_file_size_kb' => 500]); // 500 KB limit

        // Create 1 MB file (exceeds 500 KB limit)
        $largeFile = UploadedFile::fake()->create('large_photo.jpg', 1200, 'image/jpeg');

        $payload = $this->validLeadPayload([
            'documents' => [
                'photo' => [
                    'file' => $largeFile,
                ],
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leads.store'), $payload);

        $response->assertSessionHasErrors('documents.photo');
    }

    public function test_document_access_authorization(): void
    {
        Storage::fake('public');

        $lead1 = Lead::create($this->validLeadPayload(['email' => 'lead1@example.com']));
        $lead2 = Lead::create($this->validLeadPayload(['email' => 'lead2@example.com']));

        $panType = DocumentType::where('code', 'pan_card')->firstOrFail();

        $doc1 = LeadDocument::create([
            'lead_id' => $lead1->id,
            'document_type_id' => $panType->id,
            'file_path' => "leads/{$lead1->id}/documents/pan.pdf",
            'original_name' => 'pan.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
        ]);

        Storage::disk('public')->put($doc1->file_path, 'dummy content');

        // Authorized admin can download lead1's document
        $response = $this->actingAs($this->admin)
            ->get(route('admin.leads.documents.download', [$lead1, $doc1]));
        $response->assertStatus(200);

        // Accessing lead1's document via lead2's route URL must return 403 Forbidden
        $unauthorizedResponse = $this->actingAs($this->admin)
            ->get(route('admin.leads.documents.download', [$lead2, $doc1]));
        $unauthorizedResponse->assertStatus(403);
    }

    public function test_document_deletion(): void
    {
        Storage::fake('public');

        $lead = Lead::create($this->validLeadPayload(['email' => 'leaddelete@example.com']));
        $panType = DocumentType::where('code', 'pan_card')->firstOrFail();

        $path = "leads/{$lead->id}/documents/test_delete.pdf";
        Storage::disk('public')->put($path, 'dummy file');

        $doc = LeadDocument::create([
            'lead_id' => $lead->id,
            'document_type_id' => $panType->id,
            'file_path' => $path,
            'original_name' => 'test_delete.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.leads.documents.destroy', [$lead, $doc]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('lead_documents', ['id' => $doc->id]);
        Storage::disk('public')->assertMissing($path);
    }
}
