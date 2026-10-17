<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LoanProduct;
use App\Models\OpinionReport;
use App\Models\User;
use App\Services\OpinionReportSaver;
use App\Support\OpinionReportData;
use Carbon\Carbon;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class OpinionReportTest extends TestCase
{
    use RefreshDatabase;

    private const TEMPLATE = 'upload/OR/demo_or.docx';
    private const W = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    /** Demo values of the master that must never reach a real report. */
    private const SAMPLE_VALUES = [
        'SALIM', 'FIROZ', 'LAGADIYA', '30/03/1985', 'RAJULA', 'DHARESHVAR', '01/01/2000', '31-03-2026', 'SEEMA', 'SAMAR',
        'HOUSEWIFE', 'TATVJYOTI', 'VAVERA', 'NON AGRI', 'BUISNESS', 'NEAR TAT',
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
            'name' => 'Asha Mehta',
            'father_full_name' => 'Rajesh Mehta',
            'relationship' => 'Daughter',
            'date_of_birth' => '1990-06-15',
            'place_of_birth' => 'Pune',
            'occupation' => 'Trader',
            'address' => '12 Lake View, Pune',
            'business_address' => 'Shop 5, Market Road, Pune',
            'business_name' => 'Mehta Traders',
            'mobile_number' => '9876501234',
            'bank_loan_details' => ['branch_name' => 'Camp Branch'],
            'status' => 'new',
        ]);
    }

    protected function tearDown(): void
    {
        foreach (Lead::withTrashed()->pluck('id') as $id) {
            File::deleteDirectory(storage_path('app/public/or/' . $id));
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

    private function save(array $opr, ?User $user = null, ?Lead $lead = null, array $leadFields = [])
    {
        $lead ??= $this->lead;

        return $this->actingAs($user ?? $this->admin)->put(route('admin.leads.update', $lead), array_merge([
            'name' => $lead->name,
            'mobile_number' => $lead->mobile_number,
            'loan_product_id' => $this->product->id,
            'status' => 'new',
        ], $leadFields, ['opr' => $opr]));
    }

    private function generate(?Lead $lead = null)
    {
        return $this->actingAs($this->admin)->post(route('admin.leads.or.generate', $lead ?? $this->lead));
    }

    private function generatedPath(?Lead $lead = null): string
    {
        return storage_path('app/public/or/' . ($lead ?? $this->lead)->id . '/OR.docx');
    }

    private function documentXml(string $docxPath): string
    {
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($docxPath) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        return $xml;
    }

    /**
     * @return array{0: DOMDocument, 1: DOMXPath}
     */
    private function load(string $xml): array
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $x = new DOMXPath($dom);
        $x->registerNamespace('w', self::W);

        return [$dom, $x];
    }

    /** All visible text, paragraph per line. */
    private function plainText(string $xml): string
    {
        [, $x] = $this->load($xml);
        $lines = [];
        foreach ($x->query('//w:p') as $p) {
            $lines[] = implode('', array_map(fn ($t) => $t->textContent, iterator_to_array($x->query('.//w:t', $p))));
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<int, array<int, string>> rows of cell texts of the table whose text contains $needle
     */
    private function tableRows(string $xml, string $needle): array
    {
        [, $x] = $this->load($xml);
        foreach ($x->query('//w:tbl') as $tbl) {
            if (! str_contains($tbl->textContent, $needle)) {
                continue;
            }
            $rows = [];
            foreach ($x->query('./w:tr', $tbl) as $tr) {
                $rows[] = array_map(fn ($tc) => trim($tc->textContent), iterator_to_array($x->query('./w:tc', $tr)));
            }

            return $rows;
        }

        return [];
    }

    /** Text of every yellow-highlighted run in the master, grouped per paragraph. */
    private function templateYellowTexts(): array
    {
        $xml = $this->documentXml(public_path(self::TEMPLATE));
        [, $x] = $this->load($xml);
        $out = [];
        foreach ($x->query('//w:p[.//w:highlight]') as $p) {
            $text = '';
            foreach ($x->query('.//w:r[w:rPr/w:highlight]', $p) as $run) {
                $text .= implode('', array_map(fn ($t) => $t->textContent, iterator_to_array($x->query('w:t', $run))));
            }
            if (trim($text) !== '') {
                $out[] = trim($text);
            }
        }

        return $out;
    }

    private function fullReport(): array
    {
        return [
            'deponent_name' => 'Asha Mehta', 'relationship' => 'Daughter', 'father_name' => 'Rajesh Mehta',
            'date_of_birth' => '1990-06-15', 'place_of_birth' => 'Pune', 'occupation' => 'Trader',
            'residential_address' => '12 Lake View, Pune', 'office_address' => 'Shop 5, Market Road, Pune',
            'branch' => 'Camp Branch', 'borrower_name' => 'Mehta Traders',
            'assets_date' => '2026-10-01', 'deponent_date' => '2026-10-02', 'notify_by' => '31st March',
            'properties' => [
                ['place' => 'Plot 7, Baner', 'ownership_origin' => 'Self acquired', 'ownership_type' => 'Individual name', 'encumbered' => 'Bank loan 5 lakh', 'extent_acres' => '0.25', 'market_value' => '4500000', 'case_suit' => 'None pending', 'land_type' => 'Non agricultural', 'ceiling' => 'Does not come in ceiling area'],
                ['place' => 'Farm, Mulshi', 'ownership_origin' => 'Ancestral', 'ownership_type' => 'Joint names', 'extent_acres' => '2', 'market_value' => '900000', 'land_type' => 'Agricultural'],
                ['place' => 'Flat 3, Aundh'],
                ['place' => 'Shop 9, Kothrud', 'market_value' => '2500000'],
            ],
            'outside_india' => 'Flat in Dubai, Rs 80 lakh',
            'liquid_assets' => [
                'cash' => ['amount' => '120000', 'encumbered' => 'Nil'],
                'business_capital' => ['description' => 'Proprietor capital', 'amount' => '850000'],
                'inventory' => ['amount' => '430000'],
            ],
            'gov_securities' => 'NSC Rs 50,000', 'shares' => '100 shares of ABC Ltd', 'life_insurance' => 'LIC Jeevan Anand',
            'life_policy_numbers' => 'LIC-12345', 'life_surrender_value' => 'Rs 1,20,000', 'jewellery' => '200 g gold',
            'associate_investment' => 'Rs 3,00,000 in XYZ Traders', 'intangible_assets' => 'Goodwill Rs 1,00,000',
            'income_per_month' => 'Rs 60,000', 'other_asset' => 'Vehicle worth Rs 4,00,000',
            'borrowings_banks' => 'SBI Rs 10,00,000', 'borrowings_market' => 'Rs 1,00,000', 'borrowings_other' => 'Relatives Rs 50,000',
            'indirect_liability' => 'Guarantee for Rs 2,00,000', 'other_liability' => 'Rs 25,000 unpaid dues',
            'family_members' => [
                ['name' => 'Neha Mehta', 'date_of_birth' => '2000-01-01', 'occupation' => 'Student', 'relation' => 'Sister', 'address' => 'Pune'],
                ['name' => 'Rajesh Mehta', 'age' => '63', 'occupation' => 'Retired', 'relation' => 'Father', 'address' => 'Pune'],
                ['name' => 'Sita Mehta', 'age' => '60', 'occupation' => 'Housewife', 'relation' => 'Mother', 'address' => 'Pune'],
            ],
        ];
    }

    // ---- the tab -------------------------------------------------------

    public function test_opinion_report_tab_comes_right_after_stock_statement(): void
    {
        $html = $this->editPage()->assertOk()->getContent();

        $order = array_map(fn ($id) => strpos($html, 'id="' . $id . '"'), ['documents-tab', 'stock-statement-tab', 'opinion-report-tab', 'bank-tab']);
        $this->assertNotContains(false, $order);
        $sorted = $order;
        sort($sorted);
        $this->assertSame($sorted, $order);
        $this->assertStringContainsString('id="opinion-report-panel"', $html);
    }

    public function test_tab_is_hidden_without_view_permission(): void
    {
        $user = $this->userWith(['leads.edit', 'leads.create']);

        foreach ([$this->editPage($user), $this->actingAs($user)->get(route('admin.leads.create'))] as $response) {
            $response->assertOk()
                ->assertDontSee('id="opinion-report-tab"', false)
                ->assertDontSee('opr[deponent_name]', false);
        }
    }

    public function test_create_lead_shows_the_opinion_report_form_without_a_saved_lead(): void
    {
        $html = $this->actingAs($this->admin)->get(route('admin.leads.create'))
            ->assertOk()
            ->assertSee('id="opinion-report-tab"', false)
            ->assertSee('name="opr[deponent_name]"', false)
            ->assertSee('+ Add Property')
            ->assertSee('+ Add Family Member')
            ->assertDontSee('Save the lead first')
            ->getContent();

        $this->assertDoesNotMatchRegularExpression('/name="opr\[deponent_name\]"[^>]*disabled/', $html);
        $this->assertStringNotContainsString('Generate Opinion Report', $html);
    }

    public function test_every_yellow_value_has_a_form_field_and_static_text_has_none(): void
    {
        $html = $this->editPage()->getContent();

        foreach (array_keys(OpinionReportData::SCALARS) as $field) {
            $this->assertStringContainsString("name=\"opr[$field]\"", $html, "Missing field {$field}");
        }
        foreach (range(0, OpinionReportData::MAX_PROPERTIES - 1) as $i) {
            foreach (OpinionReportData::PROPERTY_KEYS as $k) {
                $this->assertStringContainsString("name=\"opr[properties][$i][$k]\"", $html);
            }
        }
        foreach (range(0, OpinionReportData::MAX_FAMILY - 1) as $i) {
            foreach (OpinionReportData::FAMILY_KEYS as $k) {
                $this->assertStringContainsString("name=\"opr[family_members][$i][$k]\"", $html);
            }
        }
        foreach (array_keys(OpinionReportData::LIQUID_ASSETS) as $row) {
            foreach (OpinionReportData::LIQUID_KEYS as $k) {
                $this->assertStringContainsString("name=\"opr[liquid_assets][$row][$k]\"", $html);
            }
        }

        // Age is shown but never submitted as a lead-level value.
        $this->assertStringNotContainsString('name="opr[age]"', $html);

        // No field for static wording: the legal text is not editable.
        $this->preg(
            '/name="opr\[[^"]+"[^>]*value="[^"]*(solemnly|affirm|hereinafter|undertake)/i',
            $html
        );
        foreach (['solemnly affirm', 'hereinafter referred', 'I undertake and declare'] as $static) {
            $this->assertStringNotContainsString($static, $html);
        }
    }

    private function preg(string $pattern, string $subject): void
    {
        $this->assertDoesNotMatchRegularExpression($pattern, $subject);
    }

    public function test_every_yellow_value_of_the_master_is_replaced_in_the_output(): void
    {
        $this->save($this->fullReport(), null, null, ['business_name' => 'Mehta Traders']);
        $this->generate()->assertSessionHas('success');
        $out = $this->plainText($this->documentXml($this->generatedPath()));

        $yellow = $this->templateYellowTexts();
        $this->assertGreaterThan(20, count($yellow));

        foreach ($yellow as $demo) {
            if (preg_match('/^[\s.\x{2026}_]+$/u', $demo)) {
                continue; // dotted fill-in lines: checked separately below
            }
            if (ctype_digit($demo)) {
                continue; // the "000" amounts: asserted cell by cell in the table tests (it is a valid ending of real numbers)
            }
            // Whole-word match, so a short demo value such as "NA" isn't confused with a longer word.
            $this->assertDoesNotMatchRegularExpression('/(?<![\w])' . preg_quote($demo, '/') . '(?![\w])/u', $out, "Yellow template value '{$demo}' survived");
        }

        // With everything entered, no dotted fill-in line is left over.
        $this->assertStringNotContainsString('……', $out);
    }

    // ---- linking & age -------------------------------------------------

    public function test_other_tabs_values_prefill_the_opinion_report(): void
    {
        $d = OpinionReportData::for($this->lead);

        $this->assertSame('Asha Mehta', $d['deponent_name']);
        $this->assertSame('Rajesh Mehta', $d['father_name']);
        $this->assertSame('Daughter', $d['relationship']);
        $this->assertSame('1990-06-15', $d['date_of_birth']);
        $this->assertSame('Pune', $d['place_of_birth']);
        $this->assertSame('Trader', $d['occupation']);
        $this->assertSame('12 Lake View, Pune', $d['residential_address']);
        $this->assertSame('Shop 5, Market Road, Pune', $d['office_address']);
        $this->assertSame('Camp Branch', $d['branch']);
        $this->assertSame('Mehta Traders', $d['borrower_name']);

        // Report-specific fields start empty (no sample property, family, amounts).
        $this->assertNull($d['assets_date']);
        $this->assertNull($d['deponent_date']);
        $this->assertSame([], $d['properties']);
        $this->assertSame([], $d['family_members']);
        $this->assertNull($d['liquid_assets']['cash']['amount']);

        $this->editPage()->assertSee('value="Asha Mehta"', false)->assertSee('value="Camp Branch"', false);
    }

    public function test_the_branch_falls_back_to_the_selected_bank_and_is_one_field(): void
    {
        $bank = \App\Models\Bank::firstOrCreate(['name' => 'State Bank of India'], ['status' => 'active']);
        $lead = Lead::create(['loan_product_id' => $this->product->id, 'bank_id' => $bank->id, 'name' => 'No Branch', 'mobile_number' => '9811100000', 'status' => 'new']);

        $this->assertSame('State Bank of India', OpinionReportData::for($lead)['branch']);

        // One branch value feeds both mentions in the document.
        $this->save(['branch' => 'Ring Road Branch'], null, null, ['bank_loan_details' => ['branch_name' => 'Ring Road Branch']]);
        $this->generate();
        $out = $this->plainText($this->documentXml($this->generatedPath()));
        $this->assertSame(2, substr_count($out, 'Ring Road Branch'));
    }

    public function test_link_mapping_points_at_real_lead_inputs(): void
    {
        $page = $this->actingAs($this->admin)->get(route('admin.leads.create'))->getContent();

        foreach (OpinionReportData::LEAD_LINKS as $field => $link) {
            $this->assertArrayHasKey($field, OpinionReportData::SCALARS);
            foreach ($link['sources'] as $source) {
                $this->assertStringContainsString('name="' . $source . '"', $page, "Opinion Report {$field} reads missing input {$source}");
            }
        }
        $this->assertStringContainsString('"path":"deponent_name"', $page);
    }

    public function test_changing_the_details_sheet_updates_linked_fields_but_keeps_report_data(): void
    {
        $this->save([
            'deponent_name' => 'Asha Mehta', 'borrower_name' => 'Mehta Traders',
            'properties' => [['place' => 'Plot 7, Baner']], 'family_members' => [['name' => 'Neha Mehta', 'relation' => 'Sister']],
            'liquid_assets' => ['cash' => ['amount' => '1000']], 'jewellery' => '100 g',
        ], null, null, ['business_name' => 'Mehta Traders']);

        $this->actingAs($this->admin)->put(route('admin.leads.update', $this->lead), [
            'name' => 'Asha M. Mehta', 'mobile_number' => '9876501234', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Mehta Traders Pvt Ltd',
        ]);

        $d = OpinionReportData::for($this->lead->fresh());
        $this->assertSame('Asha M. Mehta', $d['deponent_name']);
        $this->assertSame('Mehta Traders Pvt Ltd', $d['borrower_name']);
        $this->assertSame('Plot 7, Baner', $d['properties'][0]['place']);
        $this->assertSame('Neha Mehta', $d['family_members'][0]['name']);
        $this->assertSame('1000', $d['liquid_assets']['cash']['amount']);
        $this->assertSame('100 g', $d['jewellery']);
    }

    public function test_a_hand_edited_linked_field_is_kept(): void
    {
        $this->save(['borrower_name' => 'Trade Name For Bank'], null, null, ['business_name' => 'Mehta Traders']);

        $this->assertContains('borrower_name', $this->lead->opinionReport()->first()->linked_overrides);

        $this->actingAs($this->admin)->put(route('admin.leads.update', $this->lead), [
            'name' => 'Asha Mehta', 'mobile_number' => '9876501234', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Renamed',
        ]);

        $this->assertSame('Trade Name For Bank', OpinionReportData::for($this->lead->fresh())['borrower_name']);
    }

    public function test_age_is_calculated_from_the_date_of_birth(): void
    {
        $this->assertSame(41, OpinionReportData::ageFrom('1985-03-30', Carbon::parse('2026-10-08')));
        $this->assertSame(40, OpinionReportData::ageFrom('1985-12-01', Carbon::parse('2026-10-08')), 'birthday not reached yet this year');
        $this->assertSame(41, OpinionReportData::ageFrom('1985-10-08', Carbon::parse('2026-10-08')), 'birthday today');
        $this->assertNull(OpinionReportData::ageFrom(null));
        $this->assertNull(OpinionReportData::ageFrom(''));

        // The page shows it, read-only, and never submits it.
        $age = Carbon::parse('1990-06-15')->age;
        $html = $this->editPage()->getContent();
        $this->assertMatchesRegularExpression('/id="opr_age"[^>]*value="' . $age . '"[^>]*readonly/', $html);

        // The generated document uses the calculated age.
        $this->generate();
        $this->assertStringContainsString("aged  {$age} (Date of Birth 15/06/1990", $this->plainText($this->documentXml($this->generatedPath())));
    }

    public function test_a_missing_date_of_birth_leaves_the_age_blank(): void
    {
        $lead = Lead::create(['loan_product_id' => $this->product->id, 'name' => 'No Dob', 'mobile_number' => '9822200000', 'status' => 'new']);
        $this->generate($lead);

        $out = $this->plainText($this->documentXml($this->generatedPath($lead)));
        $this->assertMatchesRegularExpression('/aged\s+\(Date of Birth\s*,/', $out);
    }

    // ---- saving --------------------------------------------------------

    public function test_data_is_saved_against_the_lead_and_reloaded_on_edit(): void
    {
        $this->save($this->fullReport())->assertRedirect(route('admin.leads.index'));

        $row = OpinionReport::where('lead_id', $this->lead->id)->firstOrFail();
        $this->assertSame('Plot 7, Baner', $row->properties[0]['place']);
        $this->assertCount(4, $row->properties);
        $this->assertCount(3, $row->family_members);
        $this->assertSame('120000', $row->liquid_assets['cash']['amount']);
        $this->assertSame('2026-10-01', $row->assets_date->format('Y-m-d'));
        $this->assertNotNull($row->saved_at);

        $this->editPage()
            ->assertSee('value="Plot 7, Baner"', false)
            ->assertSee('value="Neha Mehta"', false)
            ->assertSee('value="LIC-12345"', false)
            ->assertSee('value="120000"', false);
    }

    public function test_property_rows_and_family_rows_can_be_added(): void
    {
        $this->save([
            'properties' => [['place' => 'A'], ['place' => 'B'], ['place' => 'C']],
            'family_members' => [['name' => 'One'], ['name' => 'Two']],
        ]);

        $row = $this->lead->opinionReport()->first();
        $this->assertSame(['A', 'B', 'C'], array_column($row->properties, 'place'));
        $this->assertSame(['One', 'Two'], array_column($row->family_members, 'name'));

        // Empty rows are dropped, not stored.
        $this->save(['properties' => [['place' => ''], ['place' => 'Only']], 'family_members' => [[], ['name' => 'Solo']]]);
        $row = $this->lead->opinionReport()->first();
        $this->assertSame(['Only'], array_column($row->properties, 'place'));
        $this->assertSame(['Solo'], array_column($row->family_members, 'name'));

        // The limits of the form are enforced.
        $this->save(['properties' => array_fill(0, OpinionReportData::MAX_PROPERTIES + 1, ['place' => 'X'])])->assertSessionHasErrors('opr.properties');
        $this->save(['relationship' => 'Cousin'])->assertSessionHasErrors('opr.relationship');
    }

    public function test_create_lead_saves_lead_and_opinion_report_together(): void
    {
        $this->actingAs($this->admin)->post(route('admin.leads.store'), [
            'name' => 'Vikram Shah', 'mobile_number' => '9811122233', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'date_of_birth' => '1980-01-01',
            'opr' => ['deponent_name' => 'Vikram Shah', 'properties' => [['place' => 'Plot 1']], 'jewellery' => '50 g', 'family_members' => [['name' => 'Mina', 'relation' => 'Wife']]],
        ])->assertRedirect(route('admin.leads.index'));

        $lead = Lead::where('name', 'Vikram Shah')->firstOrFail();
        $this->assertSame('Plot 1', $lead->opinionReport->properties[0]['place']);
        $this->assertSame('50 g', $lead->opinionReport->jewellery);

        $this->editPage(null, $lead)->assertSee('value="Plot 1"', false)->assertSee('value="Mina"', false);
    }

    public function test_lead_creation_works_with_an_untouched_opinion_report(): void
    {
        $this->actingAs($this->admin)->post(route('admin.leads.store'), [
            'name' => 'No Tab', 'mobile_number' => '9822200001', 'loan_product_id' => $this->product->id, 'status' => 'new',
        ])->assertRedirect(route('admin.leads.index'));

        $this->assertDatabaseHas('leads', ['name' => 'No Tab']);
        $this->assertDatabaseCount('opinion_reports', 0);
    }

    public function test_lead_and_opinion_report_are_saved_atomically(): void
    {
        $this->app->bind(OpinionReportSaver::class, fn () => new class extends OpinionReportSaver {
            public function save(Lead $lead, array $submitted, User $user): OpinionReport
            {
                throw new \RuntimeException('Opinion Report storage failed');
            }
        });
        $this->withoutExceptionHandling();

        try {
            $this->actingAs($this->admin)->post(route('admin.leads.store'), [
                'name' => 'Should Not Exist', 'mobile_number' => '9833300001', 'loan_product_id' => $this->product->id, 'status' => 'new',
                'opr' => ['jewellery' => 'x'],
            ]);
            $this->fail('The failure should propagate');
        } catch (\RuntimeException $e) {
            $this->assertSame('Opinion Report storage failed', $e->getMessage());
        }

        $this->assertDatabaseMissing('leads', ['name' => 'Should Not Exist']);
    }

    // ---- generation ----------------------------------------------------

    public function test_generation_uses_the_saved_values_and_keeps_the_layout(): void
    {
        $this->save($this->fullReport(), null, null, ['business_name' => 'Mehta Traders']);
        $this->generate()->assertSessionHas('success');

        $xml = $this->documentXml($this->generatedPath());
        $out = $this->plainText($xml);

        // Opening paragraph
        $this->assertStringContainsString('I, Asha Mehta Daughter of Rajesh Mehta', $out);
        $this->assertStringContainsString('(Date of Birth 15/06/1990, Place of Birth : Pune)', $out);
        $this->assertStringContainsString('by occupation Trader', $out);
        $this->assertStringContainsString('resident of 12 Lake View, Pune (Office address: Shop 5, Market Road, Pune)', $out);
        // Bank / borrower / dates
        $this->assertStringContainsString('having its Branch at Camp Branch', $out);
        $this->assertStringContainsString('M/s Mehta Traders (Name of the borrower)', $out);
        $this->assertStringContainsString('assets & liabilities as on 01-10-2026', $out);
        $this->assertStringContainsString('notify to the State Bank of India, Camp Branch', $out);
        $this->assertStringContainsString('every year by 31st March', $out);
        $this->assertStringContainsString('Date: 02/10/2026', $out);
        // Outside India (the template's "None" is replaced)
        $this->assertStringContainsString('OWNED BY ME. Flat in Dubai, Rs 80 lakh', $out);
        $this->assertStringNotContainsString('OWNED BY ME. None', $out);
        // Assets and liabilities
        foreach (['NSC Rs 50,000', '100 shares of ABC Ltd', 'LIC Jeevan Anand; Policy No(s): LIC-12345; Surrender value: Rs 1,20,000', '200 g gold',
            'Rs 3,00,000 in XYZ Traders', 'Goodwill Rs 1,00,000', 'Rs 60,000', 'Vehicle worth Rs 4,00,000', 'SBI Rs 10,00,000',
            'Rs 1,00,000', 'Relatives Rs 50,000', 'Guarantee for Rs 2,00,000', 'ANY OTHER LIABILITY NOT COVERED ABOVE. Rs 25,000 unpaid dues'] as $text) {
            $this->assertStringContainsString($text, $out);
        }

        // Property table: four properties -> header + 4 rows (template has 3, one is cloned).
        $props = $this->tableRows($xml, 'Ancestral or');
        $this->assertCount(5, $props);
        $this->assertSame('Plot 7, Baner', $props[1][0]);
        $this->assertSame('Self acquired / Individual name', $props[1][1]);
        $this->assertSame('Bank loan 5 lakh', $props[1][2]);
        $this->assertSame('0.25', $props[1][3]);
        $this->assertSame('4500000', $props[1][4]);
        $this->assertSame('None pending', $props[1][5]);
        $this->assertSame('Non agricultural (Does not come in ceiling area)', $props[1][6]);
        $this->assertSame('Ancestral / Joint names', $props[2][1]);
        $this->assertSame('Agricultural', $props[2][6]);
        $this->assertSame('Shop 9, Kothrud', $props[4][0]);

        // Liquid assets
        $liquid = $this->tableRows($xml, 'If encumbered to what extent');
        $this->assertSame(['Cash and Bank', 'Cash', '120000', 'Nil'], $liquid[1]);
        $this->assertSame(['Business Capital', 'Proprietor capital', '850000', ''], $liquid[2]);
        $this->assertSame(['Inventory', 'Stock', '430000', ''], $liquid[3]);

        // Family: three members, age calculated from DOB where given.
        $family = $this->tableRows($xml, 'RELATION');
        $this->assertCount(4, $family);
        $this->assertSame(['1', 'Neha Mehta', (string) Carbon::parse('2000-01-01')->age, 'Student', 'Sister', 'Pune'], $family[1]);
        $this->assertSame(['2', 'Rajesh Mehta', '63', 'Retired', 'Father', 'Pune'], $family[2]);
        $this->assertSame(['3', 'Sita Mehta', '60', 'Housewife', 'Mother', 'Pune'], $family[3]);

        // Static wording is untouched.
        foreach (['AFFIDAVIT', 'do hereby solemnly affirm and declare on oath as follows', 'a. DETAILS OF IMMOVABLE PROPERTY (IES) IN INDIA OWNED BY ME',
            'c. LIQUID ASSETS', 'k. LIABILITIES:', '4. That the Statements/declarations made in this affidavit in paragraphs 1 to 3 above are true to my knowledge.',
            'I declare that I have the following family members', 'Deponent:'] as $static) {
            $this->assertStringContainsString($static, $out);
        }
    }

    public function test_generated_document_has_no_yellow_highlighting(): void
    {
        $master = $this->documentXml(public_path(self::TEMPLATE));
        $this->assertStringContainsString('w:highlight', $master);

        $this->save($this->fullReport());
        $this->generate();
        $xml = $this->documentXml($this->generatedPath());
        $this->assertStringNotContainsString('w:highlight', $xml);
        $this->assertDoesNotMatchRegularExpression('/w:fill="FFFF00"/i', $xml);

        // ...also when nothing was entered.
        $bare = Lead::create(['loan_product_id' => $this->product->id, 'name' => 'Bare', 'mobile_number' => '9844400001', 'status' => 'new']);
        $this->generate($bare);
        $this->assertStringNotContainsString('w:highlight', $this->documentXml($this->generatedPath($bare)));
    }

    public function test_template_sample_values_are_not_copied_and_missing_values_stay_blank(): void
    {
        $bare = Lead::create(['loan_product_id' => $this->product->id, 'name' => 'Bare', 'mobile_number' => '9844400002', 'status' => 'new']);
        $this->generate($bare)->assertSessionHas('success');

        $xml = $this->documentXml($this->generatedPath($bare));
        $out = $this->plainText($xml);

        foreach (self::SAMPLE_VALUES as $sample) {
            $this->assertStringNotContainsString($sample, $out, "Template sample '{$sample}' leaked");
        }
        $this->assertStringNotContainsString('OWNED BY ME. None', $out);
        foreach (['N/A', 'XYZ'] as $filler) {
            $this->assertStringNotContainsString($filler, $out);
        }

        // Tables: property rows blank, liquid amounts blank (not "000"), family rows blank.
        foreach (array_slice($this->tableRows($xml, 'Ancestral or'), 1) as $row) {
            $this->assertSame(array_fill(0, 7, ''), $row);
        }
        $liquid = $this->tableRows($xml, 'If encumbered to what extent');
        foreach ([1, 2, 3] as $i) {
            $this->assertSame('', $liquid[$i][2]);
            $this->assertSame('', $liquid[$i][3]);
        }
        $family = $this->tableRows($xml, 'RELATION');
        $this->assertSame(['1', '', '', '', '', ''], $family[1]);
        $this->assertSame(['2', '', '', '', '', ''], $family[2]);
        $this->assertCount(3, $family);

        // The affidavit's own sentence structure remains, with blanks where values would go.
        $this->assertStringContainsString('Son/Daughter/Wife' === '' ? '' : 'of', $out);
    }

    public function test_master_template_is_never_modified(): void
    {
        $path = public_path(self::TEMPLATE);
        $before = md5_file($path);

        $this->save($this->fullReport());
        $this->generate();

        $this->assertSame($before, md5_file($path));
    }

    public function test_existing_generation_and_download_still_work(): void
    {
        $this->generate()->assertRedirect()->assertSessionHas('success');
        $this->actingAs($this->admin)->get(route('admin.leads.or.download', $this->lead))->assertOk();
    }

    // ---- permissions ---------------------------------------------------

    public function test_create_and_update_permissions_decide_whether_the_tab_is_saved(): void
    {
        $viewOnly = $this->userWith(['leads.edit', 'or.view']);
        $updater = $this->userWith(['leads.edit', 'or.view', 'or.update']);
        $creator = $this->userWith(['leads.edit', 'or.view', 'or.create']);

        $this->save(['jewellery' => '1'], $viewOnly);
        $this->save(['jewellery' => '1'], $updater);
        $this->assertDatabaseCount('opinion_reports', 0);

        $this->save(['jewellery' => '2'], $creator);
        $this->assertSame('2', $this->lead->opinionReport()->first()->jewellery);

        $this->save(['jewellery' => '3'], $creator);
        $this->assertSame('2', $this->lead->opinionReport()->first()->jewellery);

        $this->save(['jewellery' => '4'], $updater);
        $this->assertSame('4', $this->lead->opinionReport()->first()->jewellery);
    }

    public function test_read_only_users_get_disabled_fields(): void
    {
        $viewer = $this->userWith(['leads.edit', 'or.view']);

        $this->assertMatchesRegularExpression('/name="opr\[deponent_name\]"[^>]*disabled/', $this->editPage($viewer)->getContent());
    }

    public function test_create_ignores_the_opinion_report_without_permission(): void
    {
        $user = $this->userWith(['leads.create']);

        $this->actingAs($user)->post(route('admin.leads.store'), [
            'name' => 'Sneaky', 'mobile_number' => '9833300002', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'opr' => ['jewellery' => 'x'],
        ])->assertRedirect(route('admin.leads.index'));

        $this->assertDatabaseHas('leads', ['name' => 'Sneaky']);
        $this->assertDatabaseCount('opinion_reports', 0);
    }

    public function test_existing_generate_and_download_permissions_still_gate_the_buttons_and_routes(): void
    {
        $this->generate();
        $generator = $this->userWith(['leads.edit', 'or.view', 'or.generate']);
        $downloader = $this->userWith(['leads.edit', 'or.view', 'or.download']);

        $this->editPage($generator)->assertSee('Generate Opinion Report')->assertDontSee('Download Opinion Report');
        $this->editPage($downloader)->assertSee('Download Opinion Report')->assertDontSee('Generate Opinion Report');

        $this->actingAs($generator)->get(route('admin.leads.or.download', $this->lead))->assertForbidden();
        $this->actingAs($downloader)->post(route('admin.leads.or.generate', $this->lead))->assertForbidden();
        $this->actingAs($downloader)->get(route('admin.leads.or.download', $this->lead))->assertOk();
    }
}
