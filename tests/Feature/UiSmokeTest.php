<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Renders every main admin screen and asserts the markup itself is sane.
 *
 * The rest of the suite only checks routes and data, so it stays green even
 * when a page renders completely broken. This is the guard for the Tailwind →
 * Bootstrap migration: it catches pages that 500 while being rewritten, and
 * catches JS/CSS visibility contracts drifting apart.
 */
class UiSmokeTest extends TestCase
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
    }

    public static function adminScreens(): array
    {
        return [
            'dashboard' => ['dashboard'],
            'leads' => ['admin.leads.index'],
            'agents' => ['admin.agents.index'],
            'employees' => ['admin.employees.index'],
            'cities' => ['admin.cities.index'],
            'banks' => ['admin.banks.index'],
            'roles' => ['admin.roles.index'],
            'users' => ['admin.users.index'],
            'loan products' => ['admin.loan-products.index'],
            'constitutions' => ['admin.constitutions.index'],
            'document types' => ['admin.document-types.index'],
            'assessment years' => ['admin.assessment-years.index'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('adminScreens')]
    public function test_admin_screen_renders(string $routeName): void
    {
        $response = $this->actingAs($this->admin)->get(route($routeName));

        $response->assertOk();

        $html = $response->getContent();

        // A blank or near-blank body means the view blew up without a 500.
        $this->assertGreaterThan(1000, strlen($html), "{$routeName} rendered suspiciously little markup");

        // Blade should be fully compiled — no raw directives leaking through.
        $this->assertStringNotContainsString('@extends', $html);
        $this->assertStringNotContainsString('{{ $', $html);
    }

    /**
     * Phase 0 contract: JavaScript toggles `jb-hidden`, which this project owns,
     * rather than a class supplied by whichever CSS framework is loaded. If a
     * page's JS toggles jb-hidden, nothing on that page may still rely on the
     * framework's own `hidden` for JS-driven visibility.
     */
    public function test_js_visibility_toggles_use_the_project_owned_class(): void
    {
        $files = [resource_path('js/app.js')];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $files[] = $file->getPathname();
            }
        }

        // Guard against the scan silently matching nothing.
        $this->assertGreaterThan(50, count($files), 'Expected to scan the full view tree');

        $offenders = [];

        foreach ($files as $file) {
            $contents = file_get_contents($file);

            /*
             * Three ways the framework-owned `hidden` leaks back in. The first
             * was the obvious one; the other two were missed in the initial
             * sweep and silently broke the "Remove file" buttons, because the
             * markup set `hidden` while JS toggled `jb-hidden`.
             */
            $patterns = [
                "/classList\.(add|remove|toggle|contains)\('hidden'\)/",  // JS toggle
                "/\?\s*''\s*:\s*'hidden'/",                              // Blade ternary
                "/className\s*=\s*'hidden'/",                            // JS assignment
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $contents)) {
                    $offenders[] = str_replace(base_path() . '/', '', $file);
                    break;
                }
            }
        }

        $this->assertSame([], $offenders, 'These files still toggle the framework-owned `hidden` class from JS: ' . implode(', ', $offenders));
    }

    /**
     * Show pages need a record to render, so they can't use the simple
     * route-name provider above.
     */
    public function test_detail_screens_render(): void
    {
        $city = \App\Models\City::create(['name' => 'Austin', 'status' => 'active']);

        $agent = \App\Models\Agent::create([
            'first_name' => 'Bob',
            'last_name' => 'Agent',
            'email' => 'bob.agent@example.com',
            'mobile_number' => '+919876543210',
            'city_id' => $city->id,
            'status' => 'active',
        ]);

        $constitution = \App\Models\CustomerConstitution::create([
            'name' => 'Proprietorship',
            'code' => 'PROP',
            'status' => 'active',
        ]);

        $product = \App\Models\LoanProduct::create([
            'name' => 'Term Loan',
            'code' => 'TL',
            'status' => 'active',
        ]);

        $screens = [
            'agent detail' => route('admin.agents.show', $agent),
            'employee detail' => route('admin.employees.show', $this->admin),
            'constitution detail' => route('admin.constitutions.show', $constitution),
            'loan product detail' => route('admin.loan-products.show', $product),
        ];

        foreach ($screens as $label => $url) {
            $response = $this->actingAs($this->admin)->get($url);

            $response->assertOk();

            $html = $response->getContent();
            $this->assertGreaterThan(1000, strlen($html), "{$label} rendered suspiciously little markup");
            $this->assertStringNotContainsString('{{ $', $html, "{$label} leaked a raw Blade expression");
        }
    }

    /**
     * Create / edit screens, including the shared _form partials that carry
     * the file-upload fields.
     */
    public function test_form_screens_render(): void
    {
        $city = \App\Models\City::create(['name' => 'Austin', 'status' => 'active']);

        $agent = \App\Models\Agent::create([
            'first_name' => 'Bob',
            'last_name' => 'Agent',
            'email' => 'bob.agent@example.com',
            'mobile_number' => '+919876543210',
            'city_id' => $city->id,
            'status' => 'active',
        ]);

        $screens = [
            'agent create' => route('admin.agents.create'),
            'agent edit' => route('admin.agents.edit', $agent),
            'employee create' => route('admin.employees.create'),
            'employee edit' => route('admin.employees.edit', $this->admin),
            'bank create' => route('admin.banks.create'),
            'city create' => route('admin.cities.create'),
            'constitution create' => route('admin.constitutions.create'),
            'loan product create' => route('admin.loan-products.create'),
            'assessment year create' => route('admin.assessment-years.create'),
            'lead create' => route('admin.leads.create'),
            'role create' => route('admin.roles.create'),
            'document type create' => route('admin.document-types.create'),
            'user permissions edit' => route('admin.users.permissions.edit', $this->admin),
        ];

        foreach ($screens as $label => $url) {
            $response = $this->actingAs($this->admin)->get($url);

            $response->assertOk();

            $html = $response->getContent();
            $this->assertGreaterThan(1000, strlen($html), "{$label} rendered suspiciously little markup");
            $this->assertStringNotContainsString('{{ $', $html, "{$label} leaked a raw Blade expression");
        }
    }

    /**
     * Upload fields must carry the data-upload-field hook that
     * window.previewImage / clearFormFile resolve against. Without it those
     * helpers fall back to Tailwind layout classes, which disappear when
     * a page is migrated — breaking image preview with no error.
     */
    public function test_upload_fields_expose_their_js_hook(): void
    {
        $forms = [
            'admin/agents/_form.blade.php',
            'admin/employees/_form.blade.php',
        ];

        foreach ($forms as $form) {
            $path = resource_path('views/' . $form);

            if (! file_exists($path)) {
                continue;
            }

            $contents = file_get_contents($path);

            // Only Bootstrap-migrated forms are expected to carry the hook.
            if (! str_contains($contents, 'jb-form-card')) {
                continue;
            }

            $helperCalls = preg_match_all('/window\.(previewImage|clearImage|clearFormFile)\(/', $contents);
            $hooks = substr_count($contents, 'data-upload-field');

            $this->assertGreaterThan(0, $hooks, "{$form} uses upload helpers but declares no data-upload-field hook");
            $this->assertGreaterThanOrEqual(
                1,
                $hooks,
                "{$form} has {$helperCalls} upload helper calls but only {$hooks} hooks"
            );
        }
    }

    /**
     * leads/show.blade.php is the largest, most JS-coupled page in the app
     * (tabs, native <dialog> modals, a bank-loan verification report table,
     * ITR document chips, an activity timeline). The other render tests only
     * hit the mostly-empty default state; this exercises the populated
     * branches that a bare create-and-visit would skip entirely.
     */
    public function test_lead_detail_screen_renders_with_full_data(): void
    {
        $city = \App\Models\City::create(['name' => 'Austin', 'status' => 'active']);

        $lead = \App\Models\Lead::create([
            'name' => 'Rich Data Lead',
            'mobile_number' => '+919999999999',
            'email' => 'rich.lead@example.com',
            'city_id' => $city->id,
            'status' => 'visit_pending',
            'assigned_employee_id' => $this->admin->id,
            'assigned_at' => now(),
            'bank_loan_details' => [
                'applicant_coapplicant_guarantor_name' => 'Test Applicant',
                'office_tvr_result' => 'Positive',
                'type_of_organization' => ['Proprietorship'],
            ],
            'bank_details' => [
                ['bank_name' => 'Test Bank', 'account_number' => '12345', 'account_type' => 'Savings', 'ifsc_code' => 'TEST0001'],
            ],
            'current_loans' => [
                ['bank_name' => 'Other Bank', 'loan_amount' => '50000', 'loan_type' => 'Personal', 'disburse_date' => '2024-01-01', 'emi' => '2000', 'outstanding_amount' => '40000', 'tenure' => '24 months'],
            ],
        ]);

        \App\Models\Visit::create([
            'lead_id' => $lead->id,
            'employee_id' => $this->admin->id,
            'visit_date' => now(),
            'purpose' => 'Site inspection',
            'status' => 'scheduled',
            'created_by' => $this->admin->id,
        ]);

        \App\Models\LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $this->admin->id,
            'type' => 'created',
            'description' => 'Lead created',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.leads.show', $lead));

        $response->assertOk();

        $html = $response->getContent();
        $this->assertStringContainsString('Rich Data Lead', $html);
        $this->assertStringContainsString('Site inspection', $html);
        $this->assertStringContainsString('Test Bank', $html);
        $this->assertStringContainsString('Bank Loan Verification Report', $html);
        $this->assertStringNotContainsString('{{ $', $html);
    }

    /**
     * leads/_form.blade.php is the other monster page: a 6-tab wizard whose
     * JS dynamically clones ITR cards and bank-detail rows using template
     * strings that must stay in sync with the static Blade markup. This
     * renders the edit form for a lead with existing ITR/bank/loan data so
     * those populated branches — not just the empty-state defaults — run.
     */
    public function test_lead_edit_form_renders_with_populated_wizard_data(): void
    {
        $city = \App\Models\City::create(['name' => 'Austin', 'status' => 'active']);

        $bank = \App\Models\Bank::create(['name' => 'Test Master Bank', 'status' => 'active']);
        $product = \App\Models\LoanProduct::create(['name' => 'Term Loan', 'code' => 'TL', 'status' => 'active']);
        $constitution = \App\Models\CustomerConstitution::create(['name' => 'Proprietorship', 'code' => 'PROP', 'status' => 'active']);

        $lead = \App\Models\Lead::create([
            'name' => 'Wizard Data Lead',
            'mobile_number' => '+919888888888',
            'city_id' => $city->id,
            'status' => 'new',
            'bank_id' => $bank->id,
            'loan_product_id' => $product->id,
            'constitution_id' => $constitution->id,
            'itr_details' => [
                ['assessment_year' => 'A.Y. 2024-25', 'itr_audited' => 'Yes'],
            ],
            'bank_details' => [
                ['bank_name' => 'Wizard Bank', 'account_number' => '999', 'account_type' => 'Current', 'ifsc_code' => 'WIZ0001'],
            ],
            'current_loans' => [
                ['bank_name' => 'Loan Bank', 'loan_amount' => '10000'],
            ],
            'bank_loan_details' => [
                'applicant_coapplicant_guarantor_name' => 'Wizard Applicant',
            ],
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.leads.edit', $lead));

        $response->assertOk();

        $html = $response->getContent();
        $this->assertStringContainsString('Wizard Data Lead', $html);
        $this->assertStringContainsString('Wizard Bank', $html);
        $this->assertStringContainsString('Wizard Applicant', $html);
        $this->assertStringNotContainsString('{{ $', $html);
    }

    /**
     * The inspection sheet PDF must be fully data-driven: ticked/unticked
     * checkbox glyphs (DomPDF cannot render SVG), and no hardcoded branch,
     * manager or narrative text.
     */
    public function test_inspection_sheet_is_data_driven_with_checkbox_glyphs(): void
    {
        $lead = \App\Models\Lead::create([
            'name' => 'Pdf Lead',
            'mobile_number' => '+919777777777',
            'status' => 'new',
        ]);

        $render = fn (array $bld) => view('admin.leads.pdf.inspection_sheet', ['lead' => $lead, 'bld' => $bld])->render();

        $html = $render([
            'type_of_organization' => ['Proprietorship'],
            'nature_of_business' => ['Manufacturing'],
            'office_ownership' => ['RENTED'],
            'branch_name' => 'TEST BRANCH',
            'branch_code' => '99',
            'manager_name' => 'Jane Manager',
            'manager_ss_no' => 'S1',
            'inspection_remarks' => "Line one\nLine two",
        ]);

        $this->assertStringContainsString('&#9745;', $html, 'ticked box glyph missing');
        $this->assertStringContainsString('&#9744;', $html, 'unticked box glyph missing');
        $this->assertSame(3, substr_count($html, '&#9745;'), 'exactly the 3 selected options should be ticked');
        $this->assertStringContainsString('TEST BRANCH (99)', $html);
        $this->assertStringContainsString('Jane Manager', $html);
        $this->assertStringContainsString('Line one<br />', $html);
        $this->assertStringNotContainsString('<svg', $html, 'DomPDF cannot render inline SVG');

        // Wording follows the bank sheet; only the lead's data is substituted.
        $lead->update(['business_name' => 'RADHE CREATION']);
        $auto = $render(['visit_office_date' => '2026-07-28', 'office_organization_address' => 'PLOT 421, SURAT', 'office_tvr_result' => 'Positive']);
        $this->assertStringContainsString('Visited the firm RADHE CREATION on 28/07/2026.', $auto);
        $this->assertStringContainsString('The unit is located at PLOT 421, SURAT and met proprietor Mr Pdf Lead of RADHE CREATION.', $auto);
        $this->assertStringContainsString('Overall visit found positive and satisfactory response.', $auto);

        $lead->update(['business_name' => null]);
        // Title is fixed to SBI whatever bank the lead has.
        $this->assertStringContainsString('State Bank of India', $html);

        $blank = $render([]);
        foreach (['PRASHANT', 'P11937', '0062744', 'BARDOLI'] as $hardcoded) {
            $this->assertStringNotContainsString($hardcoded, $blank, "{$hardcoded} must not be hardcoded");
        }
        $this->assertStringNotContainsString('&#9745;', $blank);
    }

    /**
     * welcome.blade.php (route "/") is Laravel's default scaffold page, out
     * of scope for the admin app migration, but it depended on the Tailwind
     * CSS entry via @vite. Removing that entry without touching this page
     * would 500 it. It was decoupled to always use its baked-in stylesheet.
     */
    public function test_welcome_page_renders_without_the_tailwind_css_entry(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $this->assertStringNotContainsString('resources/css/app.css', $response->getContent());
    }

    /**
     * Guest (auth) screens use a separate Bootstrap layout
     * (layouts/guest-bootstrap.blade.php) introduced in this phase — nothing
     * else exercises it.
     */
    public function test_guest_auth_screens_render(): void
    {
        $screens = [
            'login' => route('login'),
            'forgot password' => route('password.request'),
            'reset password' => route('password.reset', 'test-token'),
        ];

        foreach ($screens as $label => $url) {
            $response = $this->get($url);

            $response->assertOk();

            $html = $response->getContent();
            $this->assertStringContainsString('jb-guest-card', $html, "{$label} did not render the Bootstrap guest layout");
            $this->assertStringNotContainsString('{{ $', $html, "{$label} leaked a raw Blade expression");
        }
    }

    /**
     * These two guest screens require an authenticated user, so they need
     * their own request context rather than the guest-only routes above.
     */
    public function test_authenticated_only_guest_screens_render(): void
    {
        $response = $this->actingAs($this->admin)->get(route('password.confirm'));
        $response->assertOk();
        $this->assertStringContainsString('jb-guest-card', $response->getContent());

        $unverified = \App\Models\User::factory()->unverified()->create();
        $unverified->assignRole('Admin');

        $response = $this->actingAs($unverified)->get(route('verification.notice'));
        $response->assertOk();
        $this->assertStringContainsString('jb-guest-card', $response->getContent());
    }

    /**
     * profile/edit.blade.php and its three partials were already carrying
     * Bootstrap classes (btn, d-flex, data-bs-toggle) while still wrapped in
     * the Tailwind layout — meaning the delete-account modal's JS never
     * loaded and the button did nothing. Confirms the fix: the page now
     * renders on the Bootstrap layout with a working modal trigger.
     */
    public function test_profile_edit_renders_with_functional_delete_modal(): void
    {
        $response = $this->actingAs($this->admin)->get(route('profile.edit'));

        $response->assertOk();

        $html = $response->getContent();
        $this->assertStringContainsString('jb-card', $html);
        $this->assertStringContainsString('data-bs-toggle="modal"', $html);
        $this->assertStringContainsString('bootstrap.bundle.min.js', $html, 'Bootstrap JS must load for the delete-account modal to open');
        $this->assertStringNotContainsString('{{ $', $html);
    }

    /**
     * The admin layout must wire up every global JS contract pages assume
     * exists. Losing one breaks silently — e.g. a delete dialog that opens
     * but cannot be closed. (Originally this compared the Tailwind and
     * Bootstrap layouts during the migration; now that Tailwind's layout is
     * removed, it guards the single remaining layout directly.)
     */
    public function test_admin_layout_provides_required_js_contracts(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/admin-bootstrap.blade.php'));

        $contracts = [
            'data-modal-close' => 'closing confirmation modals',
            'data-toast' => 'flash message toasts',
            'sidebarToggle' => 'mobile sidebar toggle',
            'initSelect2' => 'Select2 initialisation',
        ];

        foreach ($contracts as $hook => $purpose) {
            $this->assertStringContainsString($hook, $layout, "Admin layout is missing the hook for {$purpose}");
        }
    }

    /**
     * The Bootstrap component set is not consumed by a page yet, so nothing
     * else would catch a Blade syntax error in it until a later phase.
     */
    public function test_bootstrap_components_compile(): void
    {
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, [
            'path' => '/admin/cities',
        ]);

        $cases = [
            '<x-primary-button-bs>Save</x-primary-button-bs>' => 'btn-primary',
            '<x-secondary-button-bs>Cancel</x-secondary-button-bs>' => 'btn-secondary',
            '<x-danger-button-bs>Delete</x-danger-button-bs>' => 'btn-danger',
            '<x-input-label-bs value="Name" :required="true" />' => 'jb-field-required',
            '<x-input-error-bs :messages="[\'Required field\']" />' => 'Required field',
            '<x-text-input-bs name="probe" />' => 'form-control',
            '<x-confirmation-modal-bs id="probe-modal" title="Delete?" />' => 'jb-modal',
        ];

        foreach ($cases as $template => $expected) {
            $rendered = \Illuminate\Support\Facades\Blade::render($template);

            $this->assertStringContainsString($expected, $rendered, "Component failed to render: {$template}");
        }

        $rendered = \Illuminate\Support\Facades\Blade::render(
            '<x-pagination-bar-bs :paginator="$paginator" label="cities" />',
            ['paginator' => $paginator]
        );

        $this->assertStringContainsString('jb-pagination-bar', $rendered);
        $this->assertStringContainsString('No cities found', $rendered);
    }

    /**
     * The confirmation modal is opened by page JS calling
     * classList.remove('jb-hidden'). If the component stops rendering that
     * class, every delete dialog in the app silently opens on page load.
     */
    public function test_confirmation_modal_keeps_its_js_contract(): void
    {
        $rendered = \Illuminate\Support\Facades\Blade::render(
            '<x-confirmation-modal-bs id="probe-modal" />'
        );

        $this->assertStringContainsString('jb-hidden', $rendered, 'confirmation-modal-bs must render hidden by default');
        $this->assertStringContainsString('data-modal-close="probe-modal"', $rendered, 'confirmation-modal-bs must keep its close hook');
    }

    /**
     * Every jb- class used in a Blade view must be defined in a stylesheet.
     *
     * An undefined class is not an error in CSS — the element just renders
     * unstyled, which on a migrated page looks like a broken layout rather
     * than a crash. This is the main silent-failure mode of the migration.
     */
    public function test_project_classes_used_in_views_are_defined(): void
    {
        $css = file_get_contents(public_path('css/bootstrap-theme.css'))
            . file_get_contents(public_path('css/jb-tokens.css'));

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS)
        );

        $used = [];

        foreach ($iterator as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            // Stylesheet links reference jb-tokens.css — not a class.
            $contents = preg_replace('#css/jb-[a-z0-9-]+\.css#', '', $contents);

            preg_match_all('/\bjb-[a-z0-9-]+/', $contents, $matches);
            $used = array_merge($used, $matches[0]);
        }

        $used = array_unique($used);

        $this->assertNotEmpty($used, 'Expected views to use project classes');

        /*
         * Two categories are allowed to have no CSS rule:
         *
         * 1. Behaviour hooks — JS selects on them, they were never meant to be
         *    styled. These stay here permanently.
         * 2. Pre-existing gaps — undefined before the migration began, so the
         *    elements render unstyled today. Remove each as its page converts.
         */
        $jsHooks = [
            'jb-permission-checkbox',  // permission-checkboxes.blade.php script
            'jb-module-toggle',        // permission-checkboxes.blade.php script
            'jb-subsection-checkbox',  // permission-checkboxes.blade.php script
            'jb-subsection-toggle',    // permission-checkboxes.blade.php script
            'jb-password-toggle',      // employees/_form.blade.php show/hide password script
            'jb-eye-open',             // employees/_form.blade.php show/hide password script
            'jb-eye-closed',           // employees/_form.blade.php show/hide password script
        ];

        $knownUnstyled = [
            'jb-auth-subtitle',        // auth pages — Phase 7
            'jb-link',                 // auth pages — Phase 7
            'jb-sort-link',            // x-sort-link, currently used by 0 pages
            'jb-sort-link--active',
            'jb-sort-icon',
        ];

        $knownUnstyled = array_merge($knownUnstyled, $jsHooks);

        $undefined = array_values(array_filter(
            $used,
            fn ($class) => ! str_contains($css, '.' . $class) && ! in_array($class, $knownUnstyled, true)
        ));

        $this->assertSame([], $undefined, 'Views use jb- classes with no CSS definition: ' . implode(', ', $undefined));
    }

    /**
     * Every custom property the theme references must actually be defined.
     * An undefined var() fails silently in CSS — the declaration is simply
     * dropped — so a typo shows up as an invisible border or black text rather
     * than an error. This catches it at test time instead.
     */
    public function test_theme_custom_properties_all_resolve(): void
    {
        $tokens = file_get_contents(public_path('css/jb-tokens.css'));
        $theme = file_get_contents(public_path('css/bootstrap-theme.css'));

        preg_match_all('/^\s*(--[a-z0-9-]+)\s*:/mi', $tokens . $theme, $definedMatches);
        $defined = array_unique($definedMatches[1]);

        preg_match_all('/var\((--[a-z0-9-]+)/i', $theme, $usedMatches);
        $used = array_unique($usedMatches[1]);

        // Bootstrap supplies its own --bs-* properties.
        $used = array_filter($used, fn ($t) => ! str_starts_with($t, '--bs-'));

        $this->assertNotEmpty($used, 'Expected the theme to reference design tokens');

        $undefined = array_values(array_diff($used, $defined));

        $this->assertSame([], $undefined, 'Theme references undefined custom properties: ' . implode(', ', $undefined));
    }
}
