<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');

        $this->city = \App\Models\City::create(['name' => 'Mumbai', 'status' => 'active']);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Jane',
            'middle_name' => '',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'password' => 'correct-horse-battery',
            'mobile_number' => '9876543210',
            'city_id' => $this->city->id,
            'address' => '123 Street',
            'joining_date' => '2026-01-01',
            'role' => 'Employee',
            'status' => 'active',
            'insurance_policy_number' => 'POL-99999',
            'insurance_start_date' => '2026-01-01',
            'insurance_end_date' => '2027-01-01',
            'qualification' => 'B.Tech',
            'alternate_mobile_number' => '9876543211',
            'aadhaar_card_number' => '123456789012',
            'pan_card_number' => 'ABCDE1234F',
            'bank_account_number' => '999912345678',
            'bank_ifsc_code' => 'HDFC0000123',
            'bank_name' => 'HDFC',
            'bank_account_holder_name' => 'Jane Doe',
        ], $overrides);
    }

    public function test_admin_can_create_an_employee_which_also_creates_a_login_account(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.employees.store'), $this->validPayload());

        $response->assertRedirect(route('admin.employees.index'));

        $employee = User::where('email', 'jane.doe@example.com')->first();
        $this->assertNotNull($employee);
        $this->assertSame('Jane Doe', $employee->name);
        $this->assertTrue($employee->hasRole('Employee'));
        $this->assertTrue($employee->isActive());

        // Assert additional fields are stored correctly
        $this->assertSame('POL-99999', $employee->insurance_policy_number);
        $this->assertSame('B.Tech', $employee->qualification);
        $this->assertSame('9876543211', $employee->alternate_mobile_number);
        $this->assertSame('123456789012', $employee->aadhaar_card_number);
        $this->assertSame('ABCDE1234F', $employee->pan_card_number);
        $this->assertSame('999912345678', $employee->bank_account_number);
        $this->assertSame('HDFC0000123', $employee->bank_ifsc_code);
        $this->assertSame('HDFC', $employee->bank_name);
        $this->assertSame('Jane Doe', $employee->bank_account_holder_name);
    }

    public function test_the_admin_set_password_is_hashed_never_stored_in_the_clear(): void
    {
        $this->actingAs($this->admin)->post(route('admin.employees.store'), $this->validPayload());

        $employee = User::where('email', 'jane.doe@example.com')->first();

        // The stored value is a bcrypt hash of what the admin typed, never the plaintext itself.
        $this->assertStringStartsWith('$2y$', $employee->password);
        $this->assertNotSame('correct-horse-battery', $employee->password);
    }

    public function test_the_employee_can_log_in_with_the_password_the_admin_set(): void
    {
        $this->actingAs($this->admin)->post(route('admin.employees.store'), $this->validPayload());

        $this->post(route('logout'));

        $this->post('/login', ['email' => 'jane.doe@example.com', 'password' => 'correct-horse-battery']);

        $this->assertAuthenticated();
    }

    public function test_password_is_required_to_create_an_employee(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.employees.store'), $this->validPayload(['password' => '']));

        $response->assertSessionHasErrors('password');
        $this->assertNull(User::where('email', 'jane.doe@example.com')->first());
    }

    public function test_admin_can_change_an_employees_password_on_edit(): void
    {
        $employee = User::factory()->create(['email' => 'change-me@example.com']);
        $employee->assignRole('Employee');
        $originalHash = $employee->password;

        $this->actingAs($this->admin)->put(route('admin.employees.update', $employee), $this->validPayload([
            'email' => 'change-me@example.com', 'password' => 'brand-new-password',
        ]))->assertRedirect();

        $employee->refresh();
        $this->assertNotSame($originalHash, $employee->password);

        $this->post(route('logout'));
        $this->post('/login', ['email' => 'change-me@example.com', 'password' => 'brand-new-password']);
        $this->assertAuthenticated();
    }

    public function test_leaving_password_blank_on_edit_keeps_the_existing_one_unchanged(): void
    {
        $employee = User::factory()->create(['email' => 'keep-me@example.com']);
        $employee->assignRole('Employee');
        $originalHash = $employee->password;

        $this->actingAs($this->admin)->put(route('admin.employees.update', $employee), $this->validPayload([
            'email' => 'keep-me@example.com', 'password' => '',
        ]))->assertRedirect();

        $this->assertSame($originalHash, $employee->fresh()->password);
    }

    public function test_the_create_and_edit_pages_render_a_password_field_with_a_show_hide_toggle(): void
    {
        $create = $this->actingAs($this->admin)->get(route('admin.employees.create'))->assertOk()->getContent();
        $this->assertStringContainsString('name="password"', $create);
        $this->assertStringContainsString('jb-password-toggle', $create);
        $this->assertStringContainsString('type="password"', $create);

        $employee = User::factory()->create();
        $employee->assignRole('Employee');
        $edit = $this->actingAs($this->admin)->get(route('admin.employees.edit', $employee))->assertOk()->getContent();
        $this->assertStringContainsString('name="password"', $edit);
        $this->assertStringContainsString('jb-password-toggle', $edit);
        // Never pre-filled with (or able to display) the existing hash.
        $this->assertStringNotContainsString($employee->password, $edit);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'jane.doe@example.com']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.employees.store'), $this->validPayload());

        $response->assertSessionHasErrors('email');
    }

    public function test_required_fields_are_validated(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.employees.store'), []);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'password', 'role', 'status']);
    }

    /**
     * The Create Employee page must actually submit every field StoreEmployeeRequest requires.
     * "status" previously had no input at all on the page — every real submission failed
     * validation (silently, since no error was shown next to a field that didn't exist), while
     * tests kept passing because validPayload() above sent it directly, bypassing the page itself.
     */
    public function test_the_create_page_has_an_input_for_every_required_field(): void
    {
        $html = $this->actingAs($this->admin)->get(route('admin.employees.create'))->assertOk()->getContent();

        foreach (['first_name', 'last_name', 'email', 'password', 'mobile_number', 'role', 'status'] as $field) {
            $this->assertStringContainsString('name="' . $field . '"', $html, "Create Employee page has no input named \"{$field}\"");
        }
    }

    public function test_user_without_create_permission_cannot_create_employee(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $response = $this->actingAs($employee)
            ->post(route('admin.employees.store'), $this->validPayload());

        $response->assertForbidden();
        $this->assertNull(User::where('email', 'jane.doe@example.com')->first());
    }

    public function test_guest_is_redirected_from_employee_routes(): void
    {
        $this->get(route('admin.employees.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_update_an_employee(): void
    {
        Notification::fake();

        $employee = User::factory()->create(['first_name' => 'Old', 'last_name' => 'Name']);
        $employee->assignRole('Employee');

        $response = $this->actingAs($this->admin)->put(route('admin.employees.update', $employee), $this->validPayload([
            'email' => $employee->email,
            'first_name' => 'New',
        ]));

        $response->assertRedirect(route('admin.employees.index'));
        $this->assertSame('New', $employee->fresh()->first_name);
        $this->assertSame('New Doe', $employee->fresh()->name);
        $this->assertSame('POL-99999', $employee->fresh()->insurance_policy_number);
        $this->assertSame('B.Tech', $employee->fresh()->qualification);
        $this->assertSame('9876543211', $employee->fresh()->alternate_mobile_number);
        $this->assertSame('123456789012', $employee->fresh()->aadhaar_card_number);
        $this->assertSame('ABCDE1234F', $employee->fresh()->pan_card_number);
        $this->assertSame('999912345678', $employee->fresh()->bank_account_number);
        $this->assertSame('HDFC0000123', $employee->fresh()->bank_ifsc_code);
        $this->assertSame('HDFC', $employee->fresh()->bank_name);
        $this->assertSame('Jane Doe', $employee->fresh()->bank_account_holder_name);
    }

    public function test_user_without_assign_role_permission_cannot_change_an_employees_role(): void
    {
        // Grant a limited user only employees.edit, not employees.assign_role.
        $limited = User::factory()->create();
        $limited->assignRole('Employee');
        $limited->givePermissionTo('employees.edit');

        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $this->actingAs($limited)->put(route('admin.employees.update', $employee), $this->validPayload([
            'email' => $employee->email,
            'role' => 'Admin', // attempted privilege escalation
        ]));

        $this->assertTrue($employee->fresh()->hasRole('Employee'));
        $this->assertFalse($employee->fresh()->hasRole('Admin'));
    }

    public function test_admin_with_assign_role_permission_can_change_an_employees_role(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $this->actingAs($this->admin)->put(route('admin.employees.update', $employee), $this->validPayload([
            'email' => $employee->email,
            'role' => 'Admin',
        ]));

        $this->assertTrue($employee->fresh()->hasRole('Admin'));
        $this->assertFalse($employee->fresh()->hasRole('Employee'));
    }

    public function test_admin_can_deactivate_and_reactivate_an_employee(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $this->actingAs($this->admin)->patch(route('admin.employees.toggle-status', $employee))
            ->assertRedirect();
        $this->assertFalse($employee->fresh()->isActive());

        $this->actingAs($this->admin)->patch(route('admin.employees.toggle-status', $employee))
            ->assertRedirect();
        $this->assertTrue($employee->fresh()->isActive());
    }

    public function test_admin_cannot_deactivate_their_own_account(): void
    {
        $this->actingAs($this->admin)->patch(route('admin.employees.toggle-status', $this->admin))
            ->assertRedirect();

        $this->assertTrue($this->admin->fresh()->isActive());
    }

    public function test_admin_can_delete_an_employee_and_it_is_soft_deleted(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $this->actingAs($this->admin)->delete(route('admin.employees.destroy', $employee))
            ->assertRedirect(route('admin.employees.index'));

        $this->assertSoftDeleted($employee);
        $this->assertFalse(User::whereKey($employee->id)->exists());
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $this->actingAs($this->admin)->delete(route('admin.employees.destroy', $this->admin));

        $this->assertNotSoftDeleted($this->admin);
    }

    public function test_last_remaining_admin_cannot_be_deleted(): void
    {
        // $this->admin is the only Admin. A different actor (non-Admin, but
        // granted employees.delete) attempts to delete them — must be blocked.
        $actor = User::factory()->create();
        $actor->assignRole('Employee');
        $actor->givePermissionTo('employees.delete');

        $this->actingAs($actor)->delete(route('admin.employees.destroy', $this->admin));

        $this->assertNotSoftDeleted($this->admin);
    }

    public function test_a_non_last_admin_can_be_deleted(): void
    {
        $secondAdmin = User::factory()->create();
        $secondAdmin->assignRole('Admin');

        // Two admins exist now — deleting one should succeed.
        $this->actingAs($this->admin)->delete(route('admin.employees.destroy', $secondAdmin))
            ->assertRedirect(route('admin.employees.index'));

        $this->assertSoftDeleted($secondAdmin);
    }

    /**
     * Employee creation itself no longer sends a reset-link email (the admin sets the password
     * directly) — but the general Forgot Password flow still works for an employee's account,
     * same as for any other user, independent of how that account was created.
     */
    public function test_employee_can_still_use_forgot_password_independent_of_creation(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)->post(route('admin.employees.store'), $this->validPayload());
        $employee = User::where('email', 'jane.doe@example.com')->first();

        // Log out the acting Admin — the reset-password endpoint is a
        // guest-only route and would otherwise redirect an authenticated
        // session away instead of processing the reset.
        $this->post(route('logout'));

        $this->post(route('password.email'), ['email' => $employee->email]);

        Notification::assertSentTo($employee, ResetPassword::class, function ($notification) use ($employee) {
            $token = $notification->token;

            $response = $this->post(route('password.store'), [
                'token' => $token,
                'email' => $employee->email,
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ]);

            $response->assertRedirect(route('login'));

            return true;
        });

        $this->post('/login', [
            'email' => 'jane.doe@example.com',
            'password' => 'new-secure-password',
        ]);

        $this->assertAuthenticated();
    }

    public function test_search_filters_employees_by_name_or_email(): void
    {
        User::factory()->create(['name' => 'Findable Person', 'email' => 'findable@example.com'])->assignRole('Employee');
        User::factory()->create(['name' => 'Other Person', 'email' => 'other@example.com'])->assignRole('Employee');

        $response = $this->actingAs($this->admin)->get(route('admin.employees.index', ['search' => 'Findable']));

        $response->assertOk();
        $response->assertSee('Findable Person');
        $response->assertDontSee('Other Person');
    }

    public function test_filter_by_status(): void
    {
        $active = User::factory()->create(['name' => 'Active Person', 'status' => 'active']);
        $active->assignRole('Employee');
        $inactive = User::factory()->create(['name' => 'Inactive Person', 'status' => 'inactive']);
        $inactive->assignRole('Employee');

        $response = $this->actingAs($this->admin)->get(route('admin.employees.index', ['status' => 'inactive']));

        $response->assertSee('Inactive Person');
        $response->assertDontSee('Active Person');
    }

    public function test_filter_by_role(): void
    {
        $adminUser = User::factory()->create(['name' => 'Another Admin']);
        $adminUser->assignRole('Admin');
        $employeeUser = User::factory()->create(['name' => 'Plain Employee']);
        $employeeUser->assignRole('Employee');

        $response = $this->actingAs($this->admin)->get(route('admin.employees.index', ['role' => 'Employee']));

        $response->assertSee('Plain Employee');
        $response->assertDontSee('Another Admin');
    }

    public function test_filter_by_city(): void
    {
        $cityMumbai = $this->city;
        $cityDelhi = \App\Models\City::create(['name' => 'Delhi', 'status' => 'active']);

        $mumbai = User::factory()->create(['name' => 'Mumbai Person', 'city_id' => $cityMumbai->id]);
        $mumbai->assignRole('Employee');
        $delhi = User::factory()->create(['name' => 'Delhi Person', 'city_id' => $cityDelhi->id]);
        $delhi->assignRole('Employee');

        $response = $this->actingAs($this->admin)->get(route('admin.employees.index', ['city_id' => $cityMumbai->id]));

        $response->assertSee('Mumbai Person');
        $response->assertDontSee('Delhi Person');
    }

    public function test_pagination_limits_results_per_page(): void
    {
        User::factory()->count(20)->create()->each(fn ($u) => $u->assignRole('Employee'));

        $response = $this->actingAs($this->admin)->get(route('admin.employees.index'));

        $response->assertOk();
        $response->assertViewHas('employees', fn ($paginator) => $paginator->count() === 10 && $paginator->total() === 21);
    }

    public function test_employee_detail_page_shows_effective_permissions(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');
        $employee->givePermissionTo('documents.upload');

        $response = $this->actingAs($this->admin)->get(route('admin.employees.show', $employee));

        $response->assertOk();
        $response->assertSee('documents.upload');
    }

    public function test_agent_role_is_excluded_from_employee_listing_and_role_filter(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.employees.index'));

        $response->assertOk();
        $response->assertDontSee('>Agent<', false);
    }
}
