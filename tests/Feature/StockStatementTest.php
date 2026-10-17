<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LoanProduct;
use App\Models\StockStatement;
use App\Models\User;
use App\Services\SbiStockStatementGenerator;
use App\Services\StockStatementSaver;
use App\Support\StockStatementData;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class StockStatementTest extends TestCase
{
    use RefreshDatabase;

    private const TEMPLATE = 'upload/SBI_stock_statement/SBI_Stock_statement.xlsx';

    /** Demo values in the master template that must never reach a real statement. */
    private const SAMPLE_VALUES = [
        'sopari', 'soft drinks', 'SUVARN SALES AGENCY', '14300', '858000', '145860', '246675', '937365', '1327926',
        '523400', '331982', '995944', '1851326', '593682', '1257644', '31/07/2026', '11/11/1111', '9,75,000', 'city name',
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
            'name' => 'Ravi Patel',
            'business_name' => 'Patel Traders',
            'mobile_number' => '9876501234',
            'cc_amount' => '5,00,000',
            'business_location' => 'Surat',
            'status' => 'new',
        ]);
    }

    protected function tearDown(): void
    {
        // The existing generator writes under the real storage/app/public path.
        foreach (Lead::withTrashed()->pluck('id') as $id) {
            File::deleteDirectory(storage_path('app/public/sbi-stock-statement/' . $id));
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

    private function save(array $ss, ?User $user = null, ?Lead $lead = null, array $leadFields = [])
    {
        $lead ??= $this->lead;

        return $this->actingAs($user ?? $this->admin)->put(route('admin.leads.update', $lead), array_merge([
            'name' => $lead->name,
            'mobile_number' => $lead->mobile_number,
            'loan_product_id' => $this->product->id,
            'status' => 'new',
        ], $leadFields, ['ss' => $ss]));
    }

    private function generate(?Lead $lead = null)
    {
        return $this->actingAs($this->admin)->post(route('admin.leads.sbi-stock-statement.generate', $lead ?? $this->lead));
    }

    /**
     * @return array<string, array{v: string, f: ?string, s: ?string}> ref => cell
     */
    private function readSheet(string $binary): array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'ssx');
        file_put_contents($tmp, $binary);
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($tmp) === true);

        $shared = [];
        foreach (simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'))->si as $si) {
            $t = (string) ($si->t ?? '');
            foreach ($si->r as $r) {
                $t .= (string) $r->t;
            }
            $shared[] = $t;
        }

        $cells = [];
        $xml = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
        foreach ($xml->sheetData->row as $row) {
            foreach ($row->c as $c) {
                $type = (string) $c['t'];
                $v = match (true) {
                    $type === 'inlineStr' => (string) ($c->is->t ?? ''),
                    $type === 's' => $shared[(int) $c->v] ?? '',
                    default => (string) ($c->v ?? ''),
                };
                $cells[(string) $c['r']] = ['v' => $v, 'f' => isset($c->f) ? (string) $c->f : null, 's' => (string) $c['s'] ?: null];
            }
        }

        $names = [];
        foreach (simplexml_load_string($zip->getFromName('xl/workbook.xml'))->sheets->sheet as $sheet) {
            $names[] = (string) $sheet['name'];
        }
        $cells['__sheets'] = ['v' => implode('|', $names), 'f' => null, 's' => null];

        $zip->close();
        @unlink($tmp);

        return $cells;
    }

    private function generated(?Lead $lead = null): array
    {
        $lead ??= $this->lead;

        return $this->readSheet(file_get_contents(storage_path('app/public/sbi-stock-statement/' . $lead->id . '/SBI_Stock_Statement.xlsx')));
    }

    /**
     * Cells of the master that are blue, and whether they hold a formula / a value.
     *
     * @return array<string, array{formula: bool, value: bool}>
     */
    private function blueCellsOfTemplate(): array
    {
        $zip = new ZipArchive();
        $zip->open(public_path(self::TEMPLATE));
        $styles = simplexml_load_string($zip->getFromName('xl/styles.xml'));
        $sheet = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
        $zip->close();

        $blueFonts = [];
        $i = 0;
        foreach ($styles->fonts->font as $font) {
            if (isset($font->color) && strtoupper((string) $font->color['rgb']) === 'FF0070C0') {
                $blueFonts[$i] = true;
            }
            $i++;
        }
        $blueStyles = [];
        $i = 0;
        foreach ($styles->cellXfs->xf as $xf) {
            if (isset($blueFonts[(int) $xf['fontId']])) {
                $blueStyles[(string) $i] = true;
            }
            $i++;
        }

        $out = [];
        foreach ($sheet->sheetData->row as $row) {
            foreach ($row->c as $c) {
                if (isset($blueStyles[(string) $c['s']])) {
                    $out[(string) $c['r']] = ['formula' => isset($c->f), 'value' => isset($c->v)];
                }
            }
        }

        return $out;
    }

    // ---- the tab -------------------------------------------------------

    public function test_stock_statement_tab_comes_right_after_documents(): void
    {
        $html = $this->editPage()->assertOk()->getContent();

        $order = array_map(fn ($id) => strpos($html, 'id="' . $id . '"'), ['kyc-tab', 'bank-loan-tab', 'application-form-tab', 'documents-tab', 'stock-statement-tab', 'bank-tab', 'business-tab', 'loan-tab']);
        $this->assertNotContains(false, $order);
        $sorted = $order;
        sort($sorted);
        $this->assertSame($sorted, $order);
        $this->assertStringContainsString('id="stock-statement-panel"', $html);
    }

    public function test_tab_and_fields_are_hidden_without_view_permission(): void
    {
        $user = $this->userWith(['leads.edit', 'leads.create']);

        foreach ([$this->editPage($user), $this->actingAs($user)->get(route('admin.leads.create'))] as $response) {
            $response->assertOk()
                ->assertDontSee('id="stock-statement-tab"', false)
                ->assertDontSee('ss[company_name]', false);
        }
    }

    public function test_create_lead_page_shows_the_stock_statement_form_without_a_saved_lead(): void
    {
        $html = $this->actingAs($this->admin)->get(route('admin.leads.create'))
            ->assertOk()
            ->assertSee('id="stock-statement-tab"', false)
            ->assertSee('name="ss[company_name]"', false)
            ->assertSee('Part A: INVENTORIES')
            ->assertDontSee('Save the lead first')
            ->getContent();

        $this->assertDoesNotMatchRegularExpression('/name="ss\[company_name\]"[^>]*disabled/', $html);
        $this->assertStringNotContainsString('Generate Stock Statement', $html);
    }

    public function test_only_the_templates_blue_input_cells_are_collected(): void
    {
        $blue = $this->blueCellsOfTemplate();

        // Every blue cell that holds a typed (non-formula) value is covered by the form...
        $typed = array_keys(array_filter($blue, fn ($c) => $c['value'] && ! $c['formula']));
        // ...except the text runs inside shared strings, which are listed separately.
        $typed = array_values(array_diff($typed, array_keys(SbiStockStatementGenerator::INPUT_TEXT_RUNS)));
        foreach ($typed as $ref) {
            $this->assertContains($ref, SbiStockStatementGenerator::INPUT_CELLS, "Blue input cell {$ref} has no form field");
        }

        // Blue FORMULA cells are never inputs.
        // The one deliberate exception: Stocks In/Out quantity and rate are formulas in the demo
        // (fixed percentages of the opening stock) but are typed values for a real statement.
        $formulas = array_values(array_diff(
            array_keys(array_filter($blue, fn ($c) => $c['formula'])),
            SbiStockStatementGenerator::FORMULA_CELLS_MADE_INPUTS
        ));
        $this->assertNotEmpty($formulas);
        foreach (SbiStockStatementGenerator::FORMULA_CELLS_MADE_INPUTS as $ref) {
            $this->assertTrue($blue[$ref]['formula'] ?? false, "{$ref} is expected to be a template formula");
            $this->assertContains($ref, SbiStockStatementGenerator::INPUT_CELLS);
        }
        foreach ($formulas as $ref) {
            $this->assertNotContains($ref, SbiStockStatementGenerator::INPUT_CELLS, "Formula cell {$ref} must not be an input");
        }

        // Input cells that currently look empty in the demo (blue, no value) still have to be real blue cells —
        // except the newer sections (age analysis, the rest of Major Debtors, Part C), which the template leaves
        // bordered but unstyled rather than blue; those are checked below instead.
        foreach (array_diff(SbiStockStatementGenerator::INPUT_CELLS, SbiStockStatementGenerator::NON_BLUE_INPUT_CELLS) as $ref) {
            $this->assertArrayHasKey($ref, $blue, "{$ref} is not blue in the template");
        }
    }

    public function test_the_non_blue_input_cells_are_genuinely_blank_or_demo_text_not_formulas(): void
    {
        $zip = new ZipArchive();
        $zip->open(public_path(self::TEMPLATE));
        $sheet = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
        $zip->close();

        $byRef = [];
        foreach ($sheet->sheetData->row as $row) {
            foreach ($row->c as $c) {
                $byRef[(string) $c['r']] = ['formula' => isset($c->f), 'value' => isset($c->v)];
            }
        }

        foreach (SbiStockStatementGenerator::NON_BLUE_INPUT_CELLS as $ref) {
            $this->assertFalse($byRef[$ref]['formula'] ?? false, "{$ref} is a formula and must not be an input");
        }
    }

    public function test_the_form_has_a_field_for_each_input_and_none_for_calculated_values(): void
    {
        $html = $this->editPage()->getContent();

        foreach (StockStatementData::ITEM_KEYS as $k) {
            foreach (range(0, StockStatementData::MAX_ITEMS - 1) as $i) {
                $this->assertStringContainsString("name=\"ss[items][$i][$k]\"", $html);
            }
        }
        foreach (array_keys(StockStatementData::SCALARS) as $field) {
            $this->assertStringContainsString("name=\"ss[$field]\"", $html);
        }

        // Value / closing / total / drawing-power cells have no input.
        foreach (['opening_value', 'closing_qty', 'closing_value', 'drawing_power', 'advance_value', 'stock_value', 'book_debts_value'] as $calculated) {
            $this->assertStringNotContainsString($calculated, $html);
        }
        $this->assertStringContainsString('+ Add Item', $html);
    }

    // ---- linking -------------------------------------------------------

    public function test_other_tabs_values_prefill_the_stock_statement(): void
    {
        $lead = Lead::create([
            'loan_product_id' => $this->product->id, 'name' => 'Asha Shah', 'mobile_number' => '9811122233', 'status' => 'new',
            'business_name' => 'ABC Industries', 'cc_amount' => '9,75,000', 'business_location' => 'Surat',
        ]);

        $d = StockStatementData::for($lead);

        $this->assertSame('ABC Industries', $d['company_name']);
        $this->assertSame('9,75,000', $d['cc_limit']);
        $this->assertSame('Surat', $d['place']);
        $this->assertNull($d['statement_date']);
        $this->assertSame([], $d['items']);

        $this->editPage(null, $lead)->assertSee('value="ABC Industries"', false);
    }

    public function test_link_mapping_points_at_real_lead_inputs(): void
    {
        $page = $this->actingAs($this->admin)->get(route('admin.leads.create'))->getContent();

        foreach (StockStatementData::LEAD_LINKS as $field => $link) {
            $this->assertArrayHasKey($field, StockStatementData::SCALARS);
            foreach ($link['sources'] as $source) {
                $this->assertStringContainsString('name="' . $source . '"', $page, "Stock Statement {$field} reads missing input {$source}");
            }
        }
        // The tab's script gets the same mapping (live auto-fill).
        $this->assertStringContainsString('"path":"company_name"', $page);
        $this->assertStringContainsString('"business_name","firm_name","name"', str_replace('\\/', '/', $page));
    }

    public function test_changing_other_tabs_updates_linked_fields_but_keeps_stock_specific_data(): void
    {
        $this->save([
            'company_name' => 'Patel Traders', 'cc_limit' => '5,00,000', 'place' => 'Surat',
            'statement_date' => '2026-10-01', 'items' => [['name' => 'Rice', 'opening_qty' => '10', 'opening_rate' => '50']],
            'debtor_amount' => '1000',
        ], null, null, ['business_name' => 'Patel Traders', 'cc_amount' => '5,00,000', 'business_location' => 'Surat']);

        // Unrelated lead edit through the lead form only (tab not submitted).
        $this->actingAs($this->admin)->put(route('admin.leads.update', $this->lead), [
            'name' => 'Ravi Patel', 'mobile_number' => '9876501234', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Patel Traders Pvt Ltd', 'cc_amount' => '7,50,000',
        ]);

        $d = StockStatementData::for($this->lead->fresh());
        $this->assertSame('Patel Traders Pvt Ltd', $d['company_name'], 'linked field follows');
        $this->assertSame('7,50,000', $d['cc_limit']);
    }

    public function test_stock_specific_values_survive_lead_edits(): void
    {
        $this->save([
            'company_name' => 'Patel Traders', 'statement_date' => '2026-10-01',
            'items' => [['name' => 'Rice', 'opening_qty' => '10', 'opening_rate' => '50']],
            'debtor_number' => '3', 'debtor_amount' => '1000', 'margin_stock' => '25',
        ], null, null, ['business_name' => 'Patel Traders']);

        $this->actingAs($this->admin)->put(route('admin.leads.update', $this->lead), [
            'name' => 'Ravi Patel', 'mobile_number' => '9876501234', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Renamed Co', 'email' => 'x@example.com',
        ]);

        $d = StockStatementData::for($this->lead->fresh());
        $this->assertSame('Renamed Co', $d['company_name']);
        $this->assertSame('Rice', $d['items'][0]['name']);
        $this->assertSame('2026-10-01', $d['statement_date']);
        $this->assertSame('3', $d['debtor_number']);
        $this->assertSame('25', $d['margin_stock']);
    }

    public function test_a_hand_edited_linked_field_is_kept(): void
    {
        $this->save(['company_name' => 'Trade Name On Statement'], null, null, ['business_name' => 'Patel Traders']);

        $this->assertContains('company_name', $this->lead->stockStatement()->first()->linked_overrides);

        $this->actingAs($this->admin)->put(route('admin.leads.update', $this->lead), [
            'name' => 'Ravi Patel', 'mobile_number' => '9876501234', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Another Name',
        ]);

        $this->assertSame('Trade Name On Statement', StockStatementData::for($this->lead->fresh())['company_name']);
    }

    // ---- saving --------------------------------------------------------

    public function test_inventory_items_and_other_inputs_are_saved_and_reloaded(): void
    {
        $this->save([
            'statement_date' => '2026-10-05',
            'items' => [
                ['name' => 'Rice', 'opening_qty' => '10', 'opening_rate' => '50', 'in_qty' => '5', 'in_rate' => '55', 'out_qty' => '3', 'out_rate' => '60', 'qty_in_transit' => '2'],
                ['name' => 'Wheat', 'opening_qty' => '20', 'opening_rate' => '30'],
            ],
            'debtor_number' => '4', 'debtor_amount' => '8000',
            'margin_stock' => '25', 'margin_book_debts' => '50%', 'sub_limit_stock' => '1000', 'sub_limit_book_debts' => '500',
            'summary_amount' => '12,000',
        ])->assertRedirect(route('admin.leads.index'));

        $row = StockStatement::where('lead_id', $this->lead->id)->firstOrFail();
        $this->assertSame('Rice', $row->items[0]['name']);
        $this->assertSame('Wheat', $row->items[1]['name']);
        $this->assertSame('2026-10-05', $row->statement_date->format('Y-m-d'));
        $this->assertSame(4, $row->debtor_number);
        $this->assertNotNull($row->saved_at);

        $this->editPage()
            ->assertSee('value="Rice"', false)
            ->assertSee('value="Wheat"', false)
            ->assertSee('value="50%"', false)
            ->assertSee('value="12,000"', false);
    }

    public function test_more_items_than_the_template_holds_are_rejected(): void
    {
        $this->save(['items' => [['name' => 'A'], ['name' => 'B'], ['name' => 'C']]])->assertSessionHasErrors('ss.items');
        $this->assertDatabaseCount('stock_statements', 0);
    }

    public function test_non_numeric_quantities_are_rejected(): void
    {
        $this->save(['items' => [['name' => 'Rice', 'opening_qty' => 'lots']]])->assertSessionHasErrors('ss.items.0.opening_qty');
    }

    public function test_create_lead_saves_lead_and_stock_statement_together(): void
    {
        $this->actingAs($this->admin)->post(route('admin.leads.store'), [
            'name' => 'Asha Shah', 'mobile_number' => '9811122233', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'ABC Industries',
            'ss' => ['company_name' => 'ABC Industries', 'statement_date' => '2026-10-07', 'items' => [['name' => 'Soap', 'opening_qty' => '100', 'opening_rate' => '12']]],
        ])->assertRedirect(route('admin.leads.index'));

        $lead = Lead::where('name', 'Asha Shah')->firstOrFail();
        $this->assertSame('Soap', $lead->stockStatement->items[0]['name']);
        $this->assertSame('2026-10-07', $lead->stockStatement->statement_date->format('Y-m-d'));

        $this->editPage(null, $lead)->assertSee('value="Soap"', false);
    }

    public function test_lead_creation_works_with_an_untouched_or_partial_stock_statement(): void
    {
        $this->actingAs($this->admin)->post(route('admin.leads.store'), [
            'name' => 'No Tab', 'mobile_number' => '9822200000', 'loan_product_id' => $this->product->id, 'status' => 'new',
        ])->assertRedirect(route('admin.leads.index'));
        $this->actingAs($this->admin)->post(route('admin.leads.store'), [
            'name' => 'Partial', 'mobile_number' => '9822211111', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'ss' => ['debtor_number' => '2'],
        ])->assertRedirect(route('admin.leads.index'));

        $this->assertDatabaseHas('leads', ['name' => 'No Tab']);
        $this->assertDatabaseCount('stock_statements', 1);
    }

    public function test_lead_and_stock_statement_are_saved_atomically(): void
    {
        $this->app->bind(StockStatementSaver::class, fn () => new class extends StockStatementSaver {
            public function save(Lead $lead, array $submitted, User $user): StockStatement
            {
                throw new \RuntimeException('Stock Statement storage failed');
            }
        });
        $this->withoutExceptionHandling();

        try {
            $this->actingAs($this->admin)->post(route('admin.leads.store'), [
                'name' => 'Should Not Exist', 'mobile_number' => '9833300000', 'loan_product_id' => $this->product->id, 'status' => 'new',
                'ss' => ['debtor_number' => '1'],
            ]);
            $this->fail('The failure should propagate');
        } catch (\RuntimeException $e) {
            $this->assertSame('Stock Statement storage failed', $e->getMessage());
        }

        $this->assertDatabaseMissing('leads', ['name' => 'Should Not Exist']);
    }

    // ---- permissions ---------------------------------------------------

    public function test_create_and_update_permissions_decide_whether_the_tab_is_saved(): void
    {
        $viewOnly = $this->userWith(['leads.edit', 'sbi_stock_statement.view']);
        $updater = $this->userWith(['leads.edit', 'sbi_stock_statement.view', 'sbi_stock_statement.update']);
        $creator = $this->userWith(['leads.edit', 'sbi_stock_statement.view', 'sbi_stock_statement.create']);

        $this->save(['debtor_number' => '1'], $viewOnly);
        $this->save(['debtor_number' => '1'], $updater);
        $this->assertDatabaseCount('stock_statements', 0);

        $this->save(['debtor_number' => '2'], $creator);
        $this->assertSame(2, $this->lead->stockStatement()->first()->debtor_number);

        $this->save(['debtor_number' => '3'], $creator);
        $this->assertSame(2, $this->lead->stockStatement()->first()->debtor_number);

        $this->save(['debtor_number' => '4'], $updater);
        $this->assertSame(4, $this->lead->stockStatement()->first()->debtor_number);
    }

    public function test_read_only_users_get_disabled_fields(): void
    {
        $viewer = $this->userWith(['leads.edit', 'sbi_stock_statement.view']);

        $this->assertMatchesRegularExpression('/name="ss\[company_name\]"[^>]*disabled/', $this->editPage($viewer)->getContent());
    }

    public function test_existing_generate_and_download_permissions_still_gate_the_buttons(): void
    {
        $this->generate()->assertRedirect();
        $generator = $this->userWith(['leads.edit', 'sbi_stock_statement.view', 'sbi_stock_statement.generate']);
        $downloader = $this->userWith(['leads.edit', 'sbi_stock_statement.view', 'sbi_stock_statement.download']);

        $this->editPage($generator)->assertSee('Generate Stock Statement')->assertDontSee('Download Stock Statement');
        $this->editPage($downloader)->assertSee('Download Stock Statement')->assertDontSee('Generate Stock Statement');

        $this->actingAs($generator)->get(route('admin.leads.sbi-stock-statement.download', $this->lead))->assertForbidden();
        $this->actingAs($downloader)->post(route('admin.leads.sbi-stock-statement.generate', $this->lead))->assertForbidden();
        $this->actingAs($downloader)->get(route('admin.leads.sbi-stock-statement.download', $this->lead))->assertOk();
    }

    // ---- generation ----------------------------------------------------

    public function test_generation_writes_the_inputs_and_keeps_the_formulas(): void
    {
        $this->save([
            'company_name' => 'Patel Traders', 'cc_limit' => '5,00,000', 'place' => 'Surat', 'statement_date' => '2026-10-05',
            'summary_amount' => '12,000',
            'items' => [
                ['name' => 'Rice', 'opening_qty' => '10', 'opening_rate' => '50', 'in_qty' => '5', 'in_rate' => '55', 'out_qty' => '3', 'out_rate' => '60', 'qty_in_transit' => '2'],
                ['name' => 'Wheat', 'opening_qty' => '20', 'opening_rate' => '30', 'in_qty' => '0', 'in_rate' => '0', 'out_qty' => '4', 'out_rate' => '35'],
            ],
            'debtor_number' => '4', 'debtor_amount' => '8000',
            'margin_stock' => '25', 'margin_book_debts' => '50%', 'sub_limit_stock' => '1000', 'sub_limit_book_debts' => '500',
        ], null, null, ['business_name' => 'Patel Traders', 'cc_amount' => '5,00,000', 'business_location' => 'Surat']);

        $this->generate()->assertSessionHas('success');
        $c = $this->generated();

        $this->assertSame('Stock Statement', $c['__sheets']['v']);

        // Inputs
        $this->assertSame('Rice', $c['A18']['v']);
        $this->assertSame(['10', '50', '5', '55', '3', '60'], [$c['B18']['v'], $c['C18']['v'], $c['E18']['v'], $c['F18']['v'], $c['H18']['v'], $c['I18']['v']]);
        $this->assertSame('2', $c['L18']['v']);
        $this->assertSame('Wheat', $c['A19']['v']);
        $this->assertSame(['4', '8000'], [$c['H43']['v'], $c['J43']['v']]);
        $this->assertStringContainsString('Patel Traders', $c['D4']['v']);
        $this->assertStringContainsString('05/10/2026', $c['N2']['v']);
        $this->assertStringContainsString('Surat', $c['D115']['v']);
        $this->assertStringContainsString('5,00,000', $c['G6']['v']);

        // Template formulas, recomputed from the inputs.
        $this->assertSame(['B18*C18', '500'], [$c['D18']['f'], $c['D18']['v']]);
        $this->assertSame(['E18*F18', '275'], [$c['G18']['f'], $c['G18']['v']]);
        $this->assertSame(['H18*I18', '180'], [$c['J18']['f'], $c['J18']['v']]);
        $this->assertSame(['B18+E18-H18', '12'], [$c['K18']['f'], $c['K18']['v']]);
        $this->assertSame(['I18', '60'], [$c['M18']['f'], $c['M18']['v']]);
        $this->assertSame(['K18*M18', '720'], [$c['N18']['f'], $c['N18']['v']]);
        $this->assertSame(['K19*M19', '560'], [$c['N19']['f'], $c['N19']['v']]); // (20+0-4)*35

        $this->assertSame(['SUM(N18:N19)', '1280'], [$c['F109']['f'], $c['F109']['v']]);
        $this->assertSame(['ROUND(F109*I109,0)', '320'], [$c['H109']['f'], $c['H109']['v']]);
        $this->assertSame('0.25', $c['I109']['v']);
        $this->assertSame(['F109-H109', '960'], [$c['J109']['f'], $c['J109']['v']]);
        $this->assertSame(['J109', '960'], [$c['N109']['f'], $c['N109']['v']]);
        $this->assertSame('1000', $c['L109']['v']);

        $this->assertSame(['SUM(J43:K44)', '8000'], [$c['F111']['f'], $c['F111']['v']]);
        $this->assertSame(['ROUND(F111*50%,0)', '4000'], [$c['H111']['f'], $c['H111']['v']]);
        $this->assertSame('50%', $c['I111']['v']);
        $this->assertSame('500', $c['L111']['v']);

        $this->assertSame(['SUM(F106:G111)', '9280'], [$c['F112']['f'], $c['F112']['v']]);
        $this->assertSame(['SUM(H106:H111)', '4320'], [$c['H112']['f'], $c['H112']['v']]);
        $this->assertSame(['SUM(N106:O111)', '4960'], [$c['N112']['f'], $c['N112']['v']]);
        $this->assertSame(['SUM(L106:M111)', '1500'], [$c['L112']['f'], $c['L112']['v']]);
    }

    public function test_template_sample_values_are_not_copied_and_missing_values_stay_blank(): void
    {
        $bare = Lead::create(['loan_product_id' => $this->product->id, 'name' => 'Bare Lead', 'mobile_number' => '9844400000', 'status' => 'new']);

        $this->generate($bare)->assertSessionHas('success');
        $c = $this->generated($bare);

        $all = implode("\n", array_column(array_filter($c, fn ($cell, $ref) => $ref !== '__sheets', ARRAY_FILTER_USE_BOTH), 'v'));
        foreach (self::SAMPLE_VALUES as $sample) {
            $this->assertStringNotContainsString($sample, $all, "Template sample '{$sample}' leaked");
        }

        // Inputs are blank; nothing shows 0 / NA; calculated cells with no inputs carry no formula.
        foreach (SbiStockStatementGenerator::INPUT_CELLS as $ref) {
            $this->assertSame('', $c[$ref]['v'], "{$ref} should be blank");
        }
        foreach (['D18', 'G18', 'J18', 'K18', 'M18', 'N18', 'D19', 'N19', 'F109', 'H109', 'J109', 'N109', 'F111', 'H111', 'J111', 'N111', 'F112', 'H112', 'J112', 'L112', 'N112'] as $ref) {
            $this->assertSame('', $c[$ref]['v'], "{$ref} should be blank");
            $this->assertNull($c[$ref]['f'], "{$ref} must not keep a formula that would show 0");
        }
        $this->assertStringNotContainsString('N/A', $all);
        $this->assertStringContainsString('Bare Lead', $c['D4']['v'], 'company name still comes from the lead');
    }

    public function test_partial_inputs_only_lay_formulas_where_they_can_calculate(): void
    {
        $this->save(['items' => [['name' => 'Rice', 'opening_qty' => '10', 'opening_rate' => '50']]], null, null, []);
        $this->generate();
        $c = $this->generated();

        $this->assertSame(['B18*C18', '500'], [$c['D18']['f'], $c['D18']['v']]);
        $this->assertSame(['B18+E18-H18', '10'], [$c['K18']['f'], $c['K18']['v']]);
        $this->assertSame('', $c['G18']['v']);
        $this->assertNull($c['G18']['f']);
        $this->assertSame('', $c['N18']['v'], 'closing value needs the closing rate');
        $this->assertSame('', $c['F109']['v']);
    }

    public function test_generation_never_modifies_the_master_template_and_removes_the_stale_calc_chain(): void
    {
        $path = public_path(self::TEMPLATE);
        $before = md5_file($path);

        $this->generate();

        $this->assertSame($before, md5_file($path));

        $zip = new ZipArchive();
        $zip->open(storage_path('app/public/sbi-stock-statement/' . $this->lead->id . '/SBI_Stock_Statement.xlsx'));
        $this->assertFalse($zip->locateName('xl/calcChain.xml'));
        $this->assertStringContainsString('fullCalcOnLoad="1"', $zip->getFromName('xl/workbook.xml'));
        // Static layout is untouched.
        $master = new ZipArchive();
        $master->open($path);
        $this->assertSame($master->getFromName('xl/styles.xml'), $zip->getFromName('xl/styles.xml'));
        preg_match('/<mergeCells.*?<\/mergeCells>/s', $master->getFromName('xl/worksheets/sheet1.xml'), $a);
        preg_match('/<mergeCells.*?<\/mergeCells>/s', $zip->getFromName('xl/worksheets/sheet1.xml'), $b);
        $this->assertSame($a[0], $b[0]);
        $zip->close();
        $master->close();
    }

    public function test_existing_generate_and_download_flow_still_works(): void
    {
        $this->generate()->assertRedirect()->assertSessionHas('success');
        $this->actingAs($this->admin)->get(route('admin.leads.sbi-stock-statement.download', $this->lead))->assertOk();
    }

    // ---- restyled sections: age analysis, the second major debtor, Part C -----------------------------

    public function test_the_nine_template_sections_render_with_the_templates_own_headings(): void
    {
        $html = $this->editPage()->getContent();

        foreach ([
            'Part A: INVENTORIES', 'Part B:', 'BOOK DEBTS', 'Age analysis', 'Part C:', 'OTHER INFORMATION',
            'Notes:', 'Certificates:', 'Summary of stock',
        ] as $needle) {
            $this->assertStringContainsStringIgnoringCase($needle, $html);
        }
        foreach (['Government and semi-government organisations', 'Associates/affiliates/subsidiaries', 'Sales', 'Production', 'Consumption of Raw Materials'] as $needle) {
            $this->assertStringContainsString($needle, $html);
        }
        // The static Notes/Certificates paragraphs are shown, never offered as inputs.
        $this->assertStringContainsString('absolute property of the', $html);
        $this->assertStringNotContainsString('name="ss[note', $html);
        $this->assertStringNotContainsString('name="ss[certificate', $html);
    }

    public function test_age_analysis_and_major_debtors_fields_are_on_the_form(): void
    {
        $html = $this->editPage()->getContent();

        foreach (array_keys(StockStatementData::AGE_CATEGORIES) as $category) {
            foreach (StockStatementData::AGE_KEYS as $key) {
                $this->assertStringContainsString("name=\"ss[age_analysis][$category][$key]\"", $html);
            }
        }
        foreach (['debtor_name', 'debtor_more6_number', 'debtor_more6_amount', 'debtor2_name', 'debtor2_up6_number', 'debtor2_up6_amount', 'debtor2_more6_number', 'debtor2_more6_amount'] as $field) {
            $this->assertStringContainsString("name=\"ss[$field]\"", $html);
        }
        foreach (array_keys(StockStatementData::OTHER_INFO_METRICS) as $metric) {
            foreach (array_keys(StockStatementData::OTHER_INFO_PERIODS) as $period) {
                $this->assertStringContainsString("name=\"ss[other_info][$metric][$period]\"", $html);
            }
        }
        $this->assertStringContainsString('name="ss[sundry_creditors_year_end]"', $html);

        // The age analysis' Total row (35) and each category's "Total book debts" are calculated, never inputs.
        $this->assertStringNotContainsString('name="ss[age_analysis][total]', $html);
        $this->assertStringContainsString('+ Add Debtor', $html);
    }

    public function test_age_analysis_major_debtors_and_other_info_save_and_reload(): void
    {
        $this->save([
            'age_analysis' => [
                'govt' => ['up6_number' => '2', 'up6_amount' => '10000', 'more6_number' => '1', 'more6_amount' => '2000', 'bills_number' => '1', 'bills_amount' => '500'],
                'others' => ['up6_amount' => '3000'],
            ],
            'debtor_name' => 'Shree Traders', 'debtor_number' => '4', 'debtor_amount' => '8000', 'debtor_more6_number' => '1', 'debtor_more6_amount' => '1500',
            'debtor2_name' => 'Om Enterprise', 'debtor2_up6_number' => '2', 'debtor2_up6_amount' => '4000', 'debtor2_more6_number' => '1', 'debtor2_more6_amount' => '900',
            'other_info' => ['sales' => ['projected' => '500000', 'during_month' => '40000'], 'consumption_stores' => ['upto_last_month' => '1200']],
            'sundry_creditors_year_end' => '9000',
        ])->assertRedirect();

        $row = $this->lead->fresh()->stockStatement;
        $this->assertSame('2', $row->age_analysis['govt']['up6_number']);
        $this->assertSame('500', $row->age_analysis['govt']['bills_amount']);
        $this->assertNull($row->age_analysis['associates']['up6_number']);
        $this->assertSame('3000', $row->age_analysis['others']['up6_amount']);
        $this->assertSame('Shree Traders', $row->debtor_name);
        $this->assertSame('Om Enterprise', $row->debtor2_name);
        $this->assertSame('500000', $row->other_info['sales']['projected']);
        $this->assertSame('9000', (string) $row->sundry_creditors_year_end);

        $html = $this->editPage(null, $this->lead->fresh())->getContent();
        $this->assertStringContainsString('value="Shree Traders"', $html);
        $this->assertStringContainsString('value="Om Enterprise"', $html);
        $this->assertStringContainsString('value="500000"', $html);
    }

    public function test_age_analysis_and_other_info_reach_the_generated_workbook(): void
    {
        $this->save([
            'age_analysis' => [
                'govt' => ['up6_number' => '2', 'up6_amount' => '10000', 'more6_number' => '1', 'more6_amount' => '2000'],
            ],
            'debtor_name' => 'Shree Traders', 'debtor_number' => '4', 'debtor_amount' => '8000',
            'debtor2_name' => 'Om Enterprise', 'debtor2_up6_number' => '2', 'debtor2_up6_amount' => '4000',
            'other_info' => ['sales' => ['projected' => '500000']],
            'sundry_creditors_year_end' => '9000',
        ])->assertRedirect();

        $this->generate()->assertSessionHas('success');
        $c = $this->generated();

        $this->assertSame('Shree Traders', $c['B43']['v']);
        $this->assertSame('Om Enterprise', $c['B44']['v']);
        $this->assertSame('4000', $c['J44']['v']);
        // The book-debts total now sums both debtor rows, matching the template's own SUM(J43:K44).
        $this->assertSame(['SUM(J43:K44)', '12000'], [$c['F111']['f'], $c['F111']['v']]);

        $this->assertSame('2', $c['H32']['v']);
        $this->assertSame('10000', $c['I32']['v']);
        $this->assertSame(['H32+J32', '3'], [$c['L32']['f'], $c['L32']['v']]);
        $this->assertSame(['I32+K32', '12000'], [$c['M32']['f'], $c['M32']['v']]);
        $this->assertSame(['SUM(H32:H34)', '2'], [$c['H35']['f'], $c['H35']['v']]);

        $this->assertSame('500000', $c['F47']['v']);
        $this->assertSame('9000', $c['I51']['v']);

        $this->assertSame('', $c['H33']['v'] ?? '');
    }
}
