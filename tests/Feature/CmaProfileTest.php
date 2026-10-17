<?php

namespace Tests\Feature;

use App\Models\CmaProfile;
use App\Models\Lead;
use App\Models\LoanProduct;
use App\Models\User;
use App\Services\CmaProfileSaver;
use App\Support\CmaProfileData;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class CmaProfileTest extends TestCase
{
    use RefreshDatabase;

    private const TEMPLATE = 'upload/CMA/deml_cma.xlsx';

    /** Sample customer data inside the master CMA that must never reach a real one. */
    private const SAMPLE_VALUES = [
        'R R ENTERPRISE', 'BHAVESHKUMAR', 'RAVJIBHAI', 'SOJITRA', 'DNTPS3311D', 'UDYAM-GJ-22-0188146', '9662918888',
        'GOTHAN BRIDGE', 'VELANJA', 'CHOKDI', 'bhaveshsojitra71', '12 year', '9000+', 'Well connected', 'MANUFACTURING.',
    ];

    private User $admin;
    private Lead $lead;
    private LoanProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->admin = User::factory()->create(['status' => 'active']);
        $this->admin->assignRole('Admin');

        $this->product = LoanProduct::firstOrCreate(['code' => 'BL'], ['name' => 'Business Loan', 'status' => 'active']);

        $this->lead = Lead::create([
            'loan_product_id' => $this->product->id,
            'name' => 'Ravi Shah',
            'business_name' => 'Acme Fabricators',
            'pan_card' => 'ABCDE1234F',
            'udyam_registration' => 'UDYAM-MH-01-0000001',
            'mobile_number' => '9876501234',
            'email' => 'ravi@acme.test',
            'business_address' => 'Plot 14, MIDC, Pune',
            'business_activity' => 'Manufacturing',
            'business_experience' => '8 years',
            'business_location' => 'Pune',
            'no_of_manpower' => '12',
            'area_of_premises' => '5000 sq ft',
            'connectivity' => 'Near the highway',
            'status' => 'new',
        ]);
    }

    protected function tearDown(): void
    {
        foreach (Lead::withTrashed()->pluck('id') as $id) {
            File::deleteDirectory(storage_path('app/public/cma/' . $id));
        }

        parent::tearDown();
    }

    // ---- helpers -------------------------------------------------------

    private function userWith(array $permissions): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->givePermissionTo($permissions);

        return $user;
    }

    private function editPage(?User $user = null, ?Lead $lead = null)
    {
        return $this->actingAs($user ?? $this->admin)->get(route('admin.leads.edit', $lead ?? $this->lead));
    }

    private function save(array $cma, ?User $user = null, ?Lead $lead = null, array $leadFields = [])
    {
        $lead ??= $this->lead;

        return $this->actingAs($user ?? $this->admin)->put(route('admin.leads.update', $lead), array_merge([
            'name' => $lead->name,
            'mobile_number' => $lead->mobile_number,
            'loan_product_id' => $this->product->id,
            'status' => 'new',
        ], $leadFields, ['cma' => $cma]));
    }

    private function generate(?Lead $lead = null)
    {
        return $this->actingAs($this->admin)->post(route('admin.leads.cma.generate', $lead ?? $this->lead));
    }

    private function generatedPath(?Lead $lead = null): string
    {
        return storage_path('app/public/cma/' . ($lead ?? $this->lead)->id . '/CMA.xlsx');
    }

    /**
     * @return array<int, string>
     */
    private function sharedStrings(string $xlsxPath): array
    {
        $zip = new ZipArchive();
        $zip->open($xlsxPath);
        $out = [];
        foreach (simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'))->si as $si) {
            $t = (string) ($si->t ?? '');
            foreach ($si->r as $r) {
                $t .= (string) $r->t;
            }
            $out[] = $t;
        }
        $zip->close();

        return $out;
    }

    /**
     * The profile sheet's text cells, ref => text (shared or inline).
     *
     * @return array<string, string>
     */
    private function profile(string $xlsxPath): array
    {
        $strings = $this->sharedStrings($xlsxPath);
        $zip = new ZipArchive();
        $zip->open($xlsxPath);
        $xml = simplexml_load_string($zip->getFromName('xl/worksheets/sheet3.xml'));
        $zip->close();

        $cells = [];
        foreach ($xml->sheetData->row as $row) {
            foreach ($row->c as $c) {
                $type = (string) $c['t'];
                $cells[(string) $c['r']] = match (true) {
                    $type === 'inlineStr' => (string) ($c->is->t ?? ''),
                    $type === 's' => $strings[(int) $c->v] ?? '',
                    default => (string) ($c->v ?? ''),
                };
            }
        }

        return $cells;
    }

    /**
     * Every formula in the workbook: "sheetPart!ref" => formula.
     *
     * @return array<string, string>
     */
    private function formulas(string $xlsxPath): array
    {
        $zip = new ZipArchive();
        $zip->open($xlsxPath);
        $out = [];
        for ($i = 1; $i <= 14; $i++) {
            $xml = $zip->getFromName("xl/worksheets/sheet{$i}.xml");
            if ($xml === false) {
                continue;
            }
            preg_match_all('/<c r="([A-Z]+\d+)"[^>]*>(?:(?!<\/c>).)*?<f[^>]*>(.*?)<\/f>/s', $xml, $m, PREG_SET_ORDER);
            foreach ($m as $hit) {
                $out["sheet{$i}!{$hit[1]}"] = $hit[2];
            }
            preg_match_all('/<c r="([A-Z]+\d+)"[^>]*>(?:(?!<\/c>).)*?<f [^>]*\/>/s', $xml, $shared, PREG_SET_ORDER);
            foreach ($shared as $hit) {
                $out["sheet{$i}!{$hit[1]}"] = $hit[0];
            }
        }
        $zip->close();

        return $out;
    }

    // ---- the tab -------------------------------------------------------

    public function test_cma_tab_comes_right_after_opinion_report(): void
    {
        $html = $this->editPage()->assertOk()->getContent();

        $order = array_map(fn ($id) => strpos($html, 'id="' . $id . '"'), ['documents-tab', 'stock-statement-tab', 'opinion-report-tab', 'cma-tab', 'bank-tab']);
        $this->assertNotContains(false, $order);
        $sorted = $order;
        sort($sorted);
        $this->assertSame($sorted, $order);
        $this->assertStringContainsString('id="cma-panel"', $html);
    }

    public function test_create_lead_shows_the_cma_fields_immediately(): void
    {
        $html = $this->actingAs($this->admin)->get(route('admin.leads.create'))
            ->assertOk()
            ->assertSee('id="cma-tab"', false)
            ->assertDontSee('Save the lead first')
            ->getContent();

        foreach (array_keys(CmaProfileData::FIELDS) as $field) {
            $this->assertStringContainsString("name=\"cma[$field]\"", $html, "Missing CMA field {$field}");
            $this->assertDoesNotMatchRegularExpression('/name="cma\[' . $field . '\]"[^>]*disabled/', $html);
        }
        $this->assertStringNotContainsString('Generate CMA', $html);
    }

    public function test_the_form_is_the_profile_only_not_the_financial_statements(): void
    {
        $html = $this->editPage()->getContent();

        $names = [];
        preg_match_all('/name="cma\[([a-z_]+)\]"/', $html, $m);
        $names = array_unique($m[1]);
        sort($names);
        $expected = array_keys(CmaProfileData::FIELDS);
        sort($expected);

        $this->assertSame($expected, $names);
        $this->assertCount(16, $names);
        foreach (['sales', 'profit', 'turnover', 'depreciation', 'dscr', 'ratio', 'balance_sheet'] as $financial) {
            $this->assertStringNotContainsString('name="cma[' . $financial, $html);
        }
    }

    public function test_tab_and_fields_are_hidden_without_view_permission(): void
    {
        $user = $this->userWith(['leads.edit', 'leads.create']);

        foreach ([$this->editPage($user), $this->actingAs($user)->get(route('admin.leads.create'))] as $response) {
            $response->assertOk()
                ->assertDontSee('id="cma-tab"', false)
                ->assertDontSee('cma[firm_name]', false);
        }
    }

    // ---- linking -------------------------------------------------------

    public function test_existing_lead_data_populates_the_matching_cma_fields(): void
    {
        $d = CmaProfileData::for($this->lead);

        $this->assertSame('Manufacturing', $d['activity']);
        $this->assertSame('Acme Fabricators', $d['firm_name']);
        $this->assertSame('Ravi Shah', $d['proprietor_name']);
        $this->assertSame('ABCDE1234F', $d['pan']);
        $this->assertSame('UDYAM-MH-01-0000001', $d['udyam_no']);
        $this->assertSame('9876501234', $d['mobile']);
        $this->assertSame('ravi@acme.test', $d['email']);
        $this->assertSame('Plot 14, MIDC, Pune', $d['address']);
        $this->assertSame('8 years', $d['business_experience']);
        $this->assertSame('Pune', $d['area_of_operation']);
        $this->assertSame('12', $d['manpower']);
        $this->assertSame('5000 sq ft', $d['space']);
        $this->assertSame('Near the highway', $d['connectivity']);
        $this->assertSame('Pune', $d['location']);

        // Fields with no lead counterpart start empty.
        $this->assertNull($d['electricity_water']);
        $this->assertNull($d['existing_facility']);

        $this->editPage()->assertSee('value="Acme Fabricators"', false)->assertSee('value="ABCDE1234F"', false);
    }

    public function test_link_mapping_points_at_real_lead_inputs(): void
    {
        $page = $this->actingAs($this->admin)->get(route('admin.leads.create'))->getContent();

        foreach (CmaProfileData::LEAD_LINKS as $field => $link) {
            $this->assertArrayHasKey($field, CmaProfileData::FIELDS);
            foreach ($link['sources'] as $source) {
                $this->assertStringContainsString('name="' . $source . '"', $page, "CMA {$field} reads missing input {$source}");
            }
        }
        // The tab's script gets the same mapping (live auto-fill).
        $this->assertStringContainsString('"path":"firm_name"', $page);
        $this->assertStringContainsString('"business_name","firm_name","name"', str_replace('\\/', '/', $page));
    }

    public function test_changing_other_tabs_updates_linked_cma_fields_but_keeps_cma_specific_values(): void
    {
        // As the browser submits it: the linked values copied from the Details Sheet, plus the CMA-only ones.
        $this->save(['firm_name' => 'Acme Fabricators', 'manpower' => '12', 'electricity_water' => 'Yes, 24x7', 'existing_facility' => 'CC limit with SBI'], null, null, ['business_name' => 'Acme Fabricators']);

        $this->actingAs($this->admin)->put(route('admin.leads.update', $this->lead), [
            'name' => 'Ravi Shah', 'mobile_number' => '9876501234', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Acme Fabricators Pvt Ltd', 'no_of_manpower' => '20',
        ]);

        $d = CmaProfileData::for($this->lead->fresh());
        $this->assertSame('Acme Fabricators Pvt Ltd', $d['firm_name']);
        $this->assertSame('20', $d['manpower']);
        $this->assertSame('Yes, 24x7', $d['electricity_water']);
        $this->assertSame('CC limit with SBI', $d['existing_facility']);
    }

    public function test_a_hand_edited_linked_field_is_kept(): void
    {
        $this->save(['firm_name' => 'Trade Name On CMA'], null, null, ['business_name' => 'Acme Fabricators']);

        $this->assertContains('firm_name', $this->lead->cmaProfile()->first()->linked_overrides);

        $this->actingAs($this->admin)->put(route('admin.leads.update', $this->lead), [
            'name' => 'Ravi Shah', 'mobile_number' => '9876501234', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Something Else',
        ]);

        $this->assertSame('Trade Name On CMA', CmaProfileData::for($this->lead->fresh())['firm_name']);
    }

    // ---- saving --------------------------------------------------------

    public function test_cma_values_can_be_edited_and_are_saved_against_the_lead(): void
    {
        $this->save([
            'activity' => 'Trading', 'electricity_water' => 'Yes', 'existing_facility' => 'None',
            'area_of_operation' => 'Maharashtra', 'location' => 'MIDC Industrial Area',
        ])->assertRedirect(route('admin.leads.index'));

        $row = CmaProfile::where('lead_id', $this->lead->id)->firstOrFail();
        $this->assertSame('Trading', $row->activity);
        $this->assertSame('Maharashtra', $row->area_of_operation);
        $this->assertSame('MIDC Industrial Area', $row->location);
        $this->assertNotNull($row->saved_at);

        $this->editPage()->assertSee('value="Maharashtra"', false)->assertSee('value="MIDC Industrial Area"', false);
    }

    public function test_cma_fields_are_validated(): void
    {
        $this->save(['email' => 'not-an-email'])->assertSessionHasErrors('cma.email');
        $this->assertDatabaseCount('cma_profiles', 0);
    }

    public function test_create_lead_saves_lead_and_cma_together(): void
    {
        $this->actingAs($this->admin)->post(route('admin.leads.store'), [
            'name' => 'Meera Joshi', 'mobile_number' => '9811122233', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Joshi Foods',
            'cma' => ['firm_name' => 'Joshi Foods', 'electricity_water' => 'Yes', 'existing_facility' => 'Term loan'],
        ])->assertRedirect(route('admin.leads.index'));

        $lead = Lead::where('name', 'Meera Joshi')->firstOrFail();
        $this->assertSame('Yes', $lead->cmaProfile->electricity_water);
        $this->assertSame('Term loan', $lead->cmaProfile->existing_facility);

        $this->editPage(null, $lead)->assertSee('value="Term loan"', false);
    }

    public function test_lead_creation_works_with_an_untouched_cma_tab(): void
    {
        $this->actingAs($this->admin)->post(route('admin.leads.store'), [
            'name' => 'No Tab', 'mobile_number' => '9822200001', 'loan_product_id' => $this->product->id, 'status' => 'new',
        ])->assertRedirect(route('admin.leads.index'));

        $this->assertDatabaseHas('leads', ['name' => 'No Tab']);
        $this->assertDatabaseCount('cma_profiles', 0);
    }

    public function test_lead_and_cma_are_saved_atomically(): void
    {
        $this->app->bind(CmaProfileSaver::class, fn () => new class extends CmaProfileSaver {
            public function save(Lead $lead, array $submitted, User $user): CmaProfile
            {
                throw new \RuntimeException('CMA storage failed');
            }
        });
        $this->withoutExceptionHandling();

        try {
            $this->actingAs($this->admin)->post(route('admin.leads.store'), [
                'name' => 'Should Not Exist', 'mobile_number' => '9833300001', 'loan_product_id' => $this->product->id, 'status' => 'new',
                'cma' => ['activity' => 'x'],
            ]);
            $this->fail('The failure should propagate');
        } catch (\RuntimeException $e) {
            $this->assertSame('CMA storage failed', $e->getMessage());
        }

        $this->assertDatabaseMissing('leads', ['name' => 'Should Not Exist']);
    }

    // ---- generation ----------------------------------------------------

    public function test_the_existing_generator_still_works_and_uses_the_lead_data(): void
    {
        $this->generate()->assertRedirect()->assertSessionHas('success');
        $this->actingAs($this->admin)->get(route('admin.leads.cma.download', $this->lead))->assertOk();

        $p = $this->profile($this->generatedPath());

        $this->assertStringContainsString('Manufacturing', $p['F12']);
        $title = implode("\n", $this->sharedStrings($this->generatedPath()));
        $this->assertStringContainsString(' Manufacturing', $title); // the Title sheet's business-activity line
        $this->assertStringContainsString('Acme Fabricators', $p['F13']);
        $this->assertStringContainsString('Ravi Shah', $p['F14']);
        $this->assertStringContainsString('ABCDE1234F', $p['F15']);
        $this->assertStringContainsString('UDYAM-MH-01-0000001', $p['F16']);
        $this->assertStringContainsString('9876501234', $p['F17']);
        $this->assertStringContainsString('Plot 14, MIDC, Pune', $p['F18']);
        $this->assertStringContainsString('ravi@acme.test', $p['F20']);
        $this->assertStringContainsString('8 years', $p['F22']);
        $this->assertStringContainsString('Pune', $p['F24']);
        $this->assertStringContainsString('12', $p['F25']);
        $this->assertStringContainsString('5000 sq ft', $p['F28']);
        $this->assertStringContainsString('Near the highway', $p['F29']);
        $this->assertStringContainsString('Pune', $p['F31']);
    }

    public function test_saved_cma_values_replace_every_profile_item(): void
    {
        $this->save([
            'activity' => 'Trading of spares', 'firm_name' => 'Shah Auto Spares', 'proprietor_name' => 'Ravi K Shah',
            'pan' => 'PQRST9876Z', 'udyam_no' => 'UDYAM-MH-09-0099999', 'mobile' => '9000011111', 'email' => 'shah@spares.test',
            "address" => "Shop 3, Station Road\nNashik, Maharashtra 422001",
            'business_experience' => '15 years', 'area_of_operation' => 'Nashik district', 'manpower' => '7',
            'space' => '2500 sq ft', 'connectivity' => 'Main road', 'location' => 'Station Road, Nashik',
            'electricity_water' => 'Yes, both available', 'existing_facility' => 'CC Rs 5 lakh with Bank of India',
        ]);
        $this->generate();

        $p = $this->profile($this->generatedPath());

        $this->assertStringContainsString('Trading of spares', $p['F12']);
        $this->assertStringContainsString('Shah Auto Spares', $p['F13']);
        $this->assertStringContainsString('Ravi K Shah', $p['F14']);
        $this->assertStringContainsString('PQRST9876Z', $p['F15']);
        $this->assertStringContainsString('UDYAM-MH-09-0099999', $p['F16']);
        $this->assertStringContainsString('9000011111', $p['F17']);
        $this->assertStringContainsString('Shop 3, Station Road', $p['F18']);
        $this->assertStringContainsString('Nashik, Maharashtra 422001', $p['F19']);
        $this->assertStringContainsString('shah@spares.test', $p['F20']);
        $this->assertStringContainsString('15 years', $p['F22']);
        $this->assertStringContainsString('Nashik district', $p['F24']);
        $this->assertStringContainsString('7', $p['F25']);
        $this->assertStringContainsString('2500 sq ft', $p['F28']);
        $this->assertStringContainsString('Main road', $p['F29']);
        $this->assertStringContainsString('Station Road, Nashik', $p['F31']);
        $this->assertStringContainsString('Yes, both available', $p['F32']);
        $this->assertStringContainsString('CC Rs 5 lakh with Bank of India', $p['F34']);

        // Current Area of Operation and Location share one template string but are independent values.
        $this->assertNotSame($p['F24'], $p['F31']);

        // The firm name is replaced wherever the workbook prints it (not just on the profile sheet).
        $all = implode("\n", $this->sharedStrings($this->generatedPath()));
        $this->assertStringContainsString('Shah Auto Spares', $all);
        $this->assertStringNotContainsString('R R ENTERPRISE', $all);
    }

    public function test_sample_customer_data_is_never_used_and_missing_values_stay_blank(): void
    {
        $bare = Lead::create(['loan_product_id' => $this->product->id, 'name' => 'Bare Lead', 'mobile_number' => '9844400001', 'status' => 'new']);
        $this->generate($bare)->assertSessionHas('success');

        $path = $this->generatedPath($bare);
        $everything = implode("\n", $this->sharedStrings($path)) . "\n" . implode("\n", $this->profile($path));
        foreach (self::SAMPLE_VALUES as $sample) {
            $this->assertStringNotContainsString($sample, $everything, "Template sample '{$sample}' leaked");
        }

        $p = $this->profile($path);
        // Values with no data are blank: no NA, N/A, dash or dummy.
        foreach (['F12', 'F15', 'F16', 'F20', 'F22', 'F24', 'F28', 'F29', 'F31', 'F32', 'F34'] as $ref) {
            $this->assertSame('', trim($p[$ref] ?? ''), "{$ref} should be blank");
        }
        // The address cell keeps only the template's own punctuation around the (blank) value.
        $this->assertMatchesRegularExpression('/^[\s:,]*$/', $p['F18']);
        $this->assertSame('', trim($p['F19']));
        $this->assertStringNotContainsString('N/A', $everything);
        // The lead's own name is real data, so it does appear.
        $this->assertStringContainsString('Bare Lead', $p['F13']);
    }

    public function test_financial_formulas_and_other_sheets_are_unchanged(): void
    {
        $this->save(['firm_name' => 'Acme Fabricators', 'electricity_water' => 'Yes']);
        $this->generate();

        $master = public_path(self::TEMPLATE);
        $generated = $this->generatedPath();

        $masterFormulas = $this->formulas($master);
        $this->assertGreaterThan(100, count($masterFormulas), 'sanity: the workbook is full of formulas');
        $this->assertSame($masterFormulas, $this->formulas($generated));

        $m = new ZipArchive();
        $m->open($master);
        $g = new ZipArchive();
        $g->open($generated);

        // Every worksheet except the profile sheet is byte-identical, as are styles and the calc chain.
        for ($i = 1; $i <= 14; $i++) {
            if ($i === 3) {
                continue;
            }
            $this->assertSame($m->getFromName("xl/worksheets/sheet{$i}.xml"), $g->getFromName("xl/worksheets/sheet{$i}.xml"), "sheet{$i} changed");
        }
        foreach (['xl/styles.xml', 'xl/workbook.xml', 'xl/calcChain.xml'] as $part) {
            $this->assertSame($m->getFromName($part), $g->getFromName($part), "{$part} changed");
        }

        // The profile sheet differs only in the one cell written separately (F31).
        $strip = fn (string $xml) => preg_replace('/<c r="F31".*?(<\/c>|\/>)/s', '', $xml);
        $this->assertSame($strip($m->getFromName('xl/worksheets/sheet3.xml')), $strip($g->getFromName('xl/worksheets/sheet3.xml')));

        $m->close();
        $g->close();
    }

    public function test_master_template_is_never_modified(): void
    {
        $path = public_path(self::TEMPLATE);
        $before = md5_file($path);

        $this->save(['firm_name' => 'Acme']);
        $this->generate();

        $this->assertSame($before, md5_file($path));
    }

    // ---- permissions ---------------------------------------------------

    public function test_create_and_update_permissions_decide_whether_the_tab_is_saved(): void
    {
        $viewOnly = $this->userWith(['leads.edit', 'cma.view']);
        $updater = $this->userWith(['leads.edit', 'cma.view', 'cma.update']);
        $creator = $this->userWith(['leads.edit', 'cma.view', 'cma.create']);

        $this->save(['existing_facility' => '1'], $viewOnly);
        $this->save(['existing_facility' => '1'], $updater);
        $this->assertDatabaseCount('cma_profiles', 0);

        $this->save(['existing_facility' => '2'], $creator);
        $this->assertSame('2', $this->lead->cmaProfile()->first()->existing_facility);

        $this->save(['existing_facility' => '3'], $creator);
        $this->assertSame('2', $this->lead->cmaProfile()->first()->existing_facility);

        $this->save(['existing_facility' => '4'], $updater);
        $this->assertSame('4', $this->lead->cmaProfile()->first()->existing_facility);
    }

    public function test_read_only_users_get_disabled_fields(): void
    {
        $viewer = $this->userWith(['leads.edit', 'cma.view']);

        $this->assertMatchesRegularExpression('/name="cma\[firm_name\]"[^>]*disabled/', $this->editPage($viewer)->getContent());
    }

    public function test_create_ignores_the_cma_tab_without_permission(): void
    {
        $user = $this->userWith(['leads.create']);

        $this->actingAs($user)->post(route('admin.leads.store'), [
            'name' => 'Sneaky', 'mobile_number' => '9833300002', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'cma' => ['activity' => 'x'],
        ])->assertRedirect(route('admin.leads.index'));

        $this->assertDatabaseHas('leads', ['name' => 'Sneaky']);
        $this->assertDatabaseCount('cma_profiles', 0);
    }

    public function test_generate_and_download_permissions_gate_the_buttons_and_routes(): void
    {
        $this->generate();
        $generator = $this->userWith(['leads.edit', 'cma.view', 'cma.generate']);
        $downloader = $this->userWith(['leads.edit', 'cma.view', 'cma.download']);

        $this->editPage($generator)->assertSee('Generate CMA')->assertDontSee('Download CMA');
        $this->editPage($downloader)->assertSee('Download CMA')->assertDontSee('Generate CMA');

        $this->actingAs($generator)->get(route('admin.leads.cma.download', $this->lead))->assertForbidden();
        $this->actingAs($downloader)->post(route('admin.leads.cma.generate', $this->lead))->assertForbidden();
        $this->actingAs($downloader)->get(route('admin.leads.cma.download', $this->lead))->assertOk();
    }
}
