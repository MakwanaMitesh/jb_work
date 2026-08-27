<?php

namespace Tests\Feature;

use App\Models\LoanProduct;
use App\Models\CustomerConstitution;
use App\Models\LoanProductConstitution;
use App\Models\Lead;
use App\Models\User;
use App\Models\City;
use App\Models\Agent;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductConstitutionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $unauthorizedUser;
    private City $city;
    private Agent $agent;
    private LoanProduct $hlProduct;
    private LoanProduct $blProduct;
    private CustomerConstitution $individualConst;
    private CustomerConstitution $proprietorConst;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');

        $this->unauthorizedUser = User::factory()->create();

        $this->city = City::create(['name' => 'Austin', 'status' => 'active']);
        $this->agent = Agent::create([
            'first_name' => 'Bob',
            'last_name' => 'Agent',
            'email' => 'bob.agent@example.com',
            'mobile_number' => '+919876543210',
            'city_id' => $this->city->id,
            'status' => 'active',
        ]);

        // Create initial products
        $this->hlProduct = LoanProduct::create([
            'name' => 'Home Loan',
            'code' => 'HL',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $this->blProduct = LoanProduct::create([
            'name' => 'Business Loan',
            'code' => 'BL',
            'status' => 'active',
            'sort_order' => 2,
        ]);

        // Create initial constitutions
        $this->individualConst = CustomerConstitution::create([
            'name' => 'Individual',
            'code' => 'INDIVIDUAL',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $this->proprietorConst = CustomerConstitution::create([
            'name' => 'Proprietorship',
            'code' => 'PROPRIETORSHIP',
            'status' => 'active',
            'sort_order' => 2,
        ]);

        // Enable combination: HL + Individual
        LoanProductConstitution::create([
            'loan_product_id' => $this->hlProduct->id,
            'constitution_id' => $this->individualConst->id,
            'status' => 'active',
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'John Client',
            'email' => 'john.client@example.com',
            'mobile_number' => '+919876543210',
            'agent_id' => $this->agent->id,
            'city_id' => $this->city->id,
            'source' => 'Web',
            'status' => 'new',
            'loan_product_id' => $this->hlProduct->id,
            'constitution_id' => $this->individualConst->id,
        ], $overrides);
    }

    public function test_admin_can_crud_loan_products(): void
    {
        // 1. List
        $response = $this->actingAs($this->admin)->get(route('admin.loan-products.index'));
        $response->assertStatus(200);

        // 2. Create
        $response = $this->actingAs($this->admin)->post(route('admin.loan-products.store'), [
            'name' => 'Project Loan',
            'code' => 'PROJECT',
            'description' => 'Large scale project financing',
            'status' => 'active',
            'sort_order' => 3,
        ]);
        $response->assertRedirect(route('admin.loan-products.index'));
        $this->assertDatabaseHas('loan_products', ['code' => 'PROJECT']);

        // 3. Edit / Update
        $product = LoanProduct::where('code', 'PROJECT')->first();
        $response = $this->actingAs($this->admin)->put(route('admin.loan-products.update', $product), [
            'name' => 'Project Loan Updated',
            'code' => 'PROJECT',
            'status' => 'active',
            'sort_order' => 5,
        ]);
        $response->assertRedirect(route('admin.loan-products.index'));
        $this->assertSame('Project Loan Updated', $product->fresh()->name);

        // 4. Toggle Status (Deactivate)
        $response = $this->actingAs($this->admin)->patch(route('admin.loan-products.toggle-status', $product));
        $this->assertSame('inactive', $product->fresh()->status);
    }

    public function test_admin_can_crud_constitutions(): void
    {
        // 1. List
        $response = $this->actingAs($this->admin)->get(route('admin.constitutions.index'));
        $response->assertStatus(200);

        // 2. Create
        $response = $this->actingAs($this->admin)->post(route('admin.constitutions.store'), [
            'name' => 'Partnership',
            'code' => 'PARTNERSHIP',
            'description' => 'Partnership firms',
            'status' => 'active',
            'sort_order' => 3,
        ]);
        $response->assertRedirect(route('admin.constitutions.index'));
        $this->assertDatabaseHas('customer_constitutions', ['code' => 'PARTNERSHIP']);

        // 3. Toggle Status
        $const = CustomerConstitution::where('code', 'PARTNERSHIP')->first();
        $response = $this->actingAs($this->admin)->patch(route('admin.constitutions.toggle-status', $const));
        $this->assertSame('inactive', $const->fresh()->status);
    }

    public function test_admin_can_configure_product_constitutions_combination(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.loan-products.config.edit', $this->hlProduct));
        $response->assertStatus(200);

        // Map HL with Proprietorship as well
        $response = $this->actingAs($this->admin)->post(route('admin.loan-products.config.update', $this->hlProduct), [
            'constitutions' => [$this->individualConst->id, $this->proprietorConst->id]
        ]);
        $response->assertRedirect(route('admin.loan-products.show', $this->hlProduct));

        $this->assertDatabaseHas('loan_product_constitutions', [
            'loan_product_id' => $this->hlProduct->id,
            'constitution_id' => $this->proprietorConst->id,
            'status' => 'active'
        ]);
    }

    public function test_lead_valid_and_invalid_combinations(): void
    {
        // 1. Valid: HL + Individual (Mapped)
        $response = $this->actingAs($this->admin)
            ->post(route('admin.leads.store'), $this->validPayload());
        $response->assertRedirect(route('admin.leads.index'));

        // 2. Invalid: HL + Proprietorship (Not configured/mapped yet)
        $response = $this->actingAs($this->admin)
            ->post(route('admin.leads.store'), $this->validPayload([
                'constitution_id' => $this->proprietorConst->id
            ]));
        $response->assertSessionHasErrors(['loan_product_id']);

        // 3. Inactive Product cannot be selected
        $inactiveProduct = LoanProduct::create([
            'name' => 'Inactive Loan',
            'code' => 'INACTIVE_L',
            'status' => 'inactive',
        ]);
        LoanProductConstitution::create([
            'loan_product_id' => $inactiveProduct->id,
            'constitution_id' => $this->individualConst->id,
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leads.store'), $this->validPayload([
                'loan_product_id' => $inactiveProduct->id
            ]));
        $response->assertSessionHasErrors(['loan_product_id']);
    }

    public function test_unauthorized_user_is_gated(): void
    {
        $response = $this->actingAs($this->unauthorizedUser)->get(route('admin.loan-products.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->unauthorizedUser)->get(route('admin.constitutions.index'));
        $response->assertStatus(403);
    }
}
