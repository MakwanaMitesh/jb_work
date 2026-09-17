<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::factory()->create(['status' => 'active']);
        $this->admin->assignRole('Admin');
    }

    public function test_admin_can_view_document_types_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.document-types.index'));
        $response->assertStatus(200);
        $response->assertSee('Aadhaar Card');
        $response->assertSee('PAN Card');
    }

    public function test_admin_can_create_document_type(): void
    {
        $payload = [
            'name' => 'Passport',
            'code' => 'passport_doc',
            'description' => 'Upload Passport copy',
            'is_required' => 1,
            'has_front_back' => 1,
            'allow_multiple' => 0,
            'allowed_file_types' => ['pdf', 'jpg', 'png'],
            'max_file_size_kb' => 10240,
            'sort_order' => 20,
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.document-types.store'), $payload);

        $response->assertRedirect(route('admin.document-types.index'));
        $this->assertDatabaseHas('document_types', [
            'code' => 'passport_doc',
            'name' => 'Passport',
            'is_required' => true,
            'has_front_back' => true,
        ]);
    }

    public function test_admin_can_update_document_type(): void
    {
        $type = DocumentType::where('code', 'pan_card')->firstOrFail();

        $payload = [
            'name' => 'PAN Card Updated',
            'code' => 'pan_card',
            'description' => 'Updated description',
            'is_required' => 1,
            'has_front_back' => 0,
            'allow_multiple' => 0,
            'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
            'max_file_size_kb' => 5120,
            'sort_order' => 2,
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.document-types.update', $type), $payload);

        $response->assertRedirect(route('admin.document-types.index'));
        $type->refresh();
        $this->assertSame('PAN Card Updated', $type->name);
        $this->assertTrue($type->is_required);
        $this->assertSame(5120, $type->max_file_size_kb);
    }

    public function test_admin_can_toggle_document_type_status(): void
    {
        $type = DocumentType::where('code', 'pan_card')->firstOrFail();

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.document-types.toggle-status', $type));

        $response->assertRedirect();
        $type->refresh();
        $this->assertSame('inactive', $type->status);
    }
}
