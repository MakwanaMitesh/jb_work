<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['status' => 'active']);
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);
        $this->admin->assignRole($role);
    }

    public function test_admin_can_create_bank(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.banks.store'), [
            'name' => 'Kotak Mahindra Bank New',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $response->assertRedirect(route('admin.banks.index'));
        $this->assertDatabaseHas('banks', [
            'name' => 'Kotak Mahindra Bank New',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_update_bank(): void
    {
        $bank = Bank::create([
            'name' => 'Test Bank',
            'status' => 'active',
            'sort_order' => 5,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.banks.update', $bank), [
            'name' => 'Test Bank Updated',
            'status' => 'inactive',
            'sort_order' => 10,
        ]);

        $response->assertRedirect(route('admin.banks.index'));
        $this->assertDatabaseHas('banks', [
            'id' => $bank->id,
            'name' => 'Test Bank Updated',
            'status' => 'inactive',
        ]);
    }

    public function test_admin_can_toggle_bank_status(): void
    {
        $bank = Bank::create([
            'name' => 'Status Test Bank',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.banks.toggle-status', $bank));

        $response->assertRedirect();
        $this->assertDatabaseHas('banks', [
            'id' => $bank->id,
            'status' => 'inactive',
        ]);
    }
}
