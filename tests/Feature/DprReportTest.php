<?php

namespace Tests\Feature;

use App\Models\DprReport;
use App\Models\Lead;
use App\Models\LoanProduct;
use App\Models\User;
use App\Services\DprReportSaver;
use App\Support\DprData;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class DprReportTest extends TestCase
{
    use RefreshDatabase;

    private const TEMPLATE = 'upload/DPR/demo_dpr.docx';
    private const W = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    /** The demo customer's values that must never reach a real report. */
    private const SAMPLE_VALUES = [
        'KIRTI', 'PANTH CREATION', 'Groundnut', 'groundnut', 'Anida', 'Khambha', 'Amreli', 'Kotak', 'RAMKRISHNA', 'embroidery',
        'PENUT', 'FLAKING', 'PEANUT', 'XYZ', 'RECIDENT', '64.00', '48.00', '55.47', '48.62', '23.86', '29.75', '32.63', '36.46',
        'Parekh Wadi', 'Katargam', '00.00 LAKH', '30.00', '10.00',
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
            'business_name' => 'Shah Industries',
            'mobile_number' => '9876501234',
            'email' => 'ravi@shah.test',
            'pan_card' => 'ABCDE1234F',
            'aadhar_card' => '1234 5678 9012',
            'gender' => 'Male',
            'date_of_birth' => '1985-04-12',
            'education' => 'B.Com',
            'mother_name' => 'Sita Shah',
            'business_experience' => '8 years',
            'introduction' => 'A family trading business since 2015.',
            'business_address' => 'Plot 14, MIDC, Pune',
            'business_activity' => 'Manufacturing',
            'business_location' => 'Pune',
            'udyam_registration' => 'UDYAM-MH-01-0000001',
            'gst_number' => '27ABCDE1234F1Z5',
            'fssai_license' => '11520001000123',
            'no_of_manpower' => '12',
            'connectivity' => 'Near the highway',
            'land_and_factory_building' => 'Own factory shed, 5000 sq ft',
            'term_loan_amount' => '3000000',
            'cc_amount' => '1000000',
            'current_loans' => [['bank_name' => 'HDFC', 'loan_type' => 'Vehicle', 'loan_amount' => '500000', 'emi' => '12000', 'outstanding_amount' => '300000']],
            'bank_details' => [['bank_name' => 'State Bank of India', 'account_number' => '1234567890', 'account_type' => 'Current', 'ifsc_code' => 'SBIN0001234']],
            'constitution_of_business' => 'Proprietorship',
            'status' => 'new',
        ]);
    }

    protected function tearDown(): void
    {
        foreach (Lead::withTrashed()->pluck('id') as $id) {
            File::deleteDirectory(storage_path('app/public/dpr/' . $id));
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

    private function save(array $dpr, ?User $user = null, ?Lead $lead = null, array $leadFields = [])
    {
        $lead ??= $this->lead;

        return $this->actingAs($user ?? $this->admin)->put(route('admin.leads.update', $lead), array_merge([
            'name' => $lead->name,
            'mobile_number' => $lead->mobile_number,
            'loan_product_id' => $this->product->id,
            'status' => 'new',
        ], $leadFields, ['dpr' => $dpr]));
    }

    private function generate(?Lead $lead = null)
    {
        return $this->actingAs($this->admin)->post(route('admin.leads.dpr.generate', $lead ?? $this->lead));
    }

    private function generatedPath(?Lead $lead = null): string
    {
        return storage_path('app/public/dpr/' . ($lead ?? $this->lead)->id . '/DPR.docx');
    }

    private function documentXml(string $docxPath): string
    {
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($docxPath) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        return $xml;
    }

    private function xp(string $xml): DOMXPath
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $x = new DOMXPath($dom);
        $x->registerNamespace('w', self::W);

        return $x;
    }

    private function plainText(string $xml): string
    {
        $x = $this->xp($xml);
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
        $x = $this->xp($xml);
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

    private function fullReport(): array
    {
        return [
            'firm_name' => 'Shah Industries', 'constitution' => 'Proprietorship', 'applicant_name' => 'Ravi Shah',
            'business_address' => 'Plot 14, MIDC, Pune', 'activity' => 'Manufacturing of spare parts',
            'land_and_factory' => 'Own factory shed, 5000 sq ft', 'location' => 'MIDC Industrial Area, Pune', 'itr_filing' => 'Last 3 years',
            'present_bank' => 'State Bank of India', 'account_type' => 'Current', 'account_number' => '1234567890', 'ifsc' => 'SBIN0001234',
            'current_loans' => [
                ['bank_name' => 'HDFC', 'loan_type' => 'Vehicle', 'loan_amount' => '500000', 'emi' => '12000', 'outstanding_amount' => '300000'],
                ['bank_name' => 'ICICI', 'loan_type' => 'Business', 'loan_amount' => '1000000', 'emi' => '25000', 'outstanding_amount' => '700000'],
                ['bank_name' => 'Axis', 'loan_type' => 'Equipment', 'loan_amount' => '200000'],
                ['bank_name' => 'SBI', 'loan_type' => 'Overdraft', 'loan_amount' => '300000'],
            ],
            'term_required' => '3000000', 'term_contribution' => '1000000', 'cc_required' => '1000000', 'cc_contribution' => '250000',
            'cost_land' => '1000000', 'cost_building' => '1500000', 'cost_machinery' => '2500000', 'cost_working_capital' => '500000',
            'fin_promoter_capital' => '1250000', 'fin_term_loan' => '3000000', 'fin_cash_credit' => '1000000',
            'machinery' => [
                ['particulars' => 'Lathe machine, model X200', 'qty' => '2 sets', 'amount' => '1200000'],
                ['particulars' => 'Drilling machine', 'qty' => '1', 'amount' => '350000'],
                ['particulars' => 'Compressor', 'qty' => '1', 'amount' => '150000'],
            ],
            'process_steps' => 'Cutting → Turning → Drilling → Finishing',
            'process_output' => 'Machined spare parts',
            'process_description' => 'Raw steel is cut, turned and drilled to drawing.',
            'collateral' => [
                ['type' => 'Residential', 'address' => 'Flat 4, Baner, Pune', 'value' => '4500000'],
                ['type' => 'Land', 'address' => 'Plot 9, Mulshi', 'value' => '2500000'],
                ['type' => 'Commercial', 'address' => 'Shop 2, Camp', 'value' => '1800000'],
            ],
            'wc_amount' => '1000000', 'wc_raw_material' => '600000', 'wc_power_fuel' => '100000', 'wc_labour' => '200000', 'wc_receivables' => '100000',
            'market_potential' => 'Demand for precision spare parts is growing with auto-component exports.',
            'financials' => [
                'sales' => ['p1' => '45.10', 'p2' => '52.30', 'p3' => '28.00'],
                'purchase' => ['p1' => '20.00', 'p2' => '24.50'],
                'net_profit' => ['p1' => '4.10', 'p2' => '5.20', 'p3' => '3.00'],
                'capital' => ['p3' => '41.25'],
            ],
        ];
    }

    // ---- the tab -------------------------------------------------------

    public function test_dpr_tab_comes_right_after_cma(): void
    {
        $html = $this->editPage()->assertOk()->getContent();

        $order = array_map(fn ($id) => strpos($html, 'id="' . $id . '"'), ['documents-tab', 'stock-statement-tab', 'opinion-report-tab', 'cma-tab', 'dpr-tab', 'bank-tab']);
        $this->assertNotContains(false, $order);
        $sorted = $order;
        sort($sorted);
        $this->assertSame($sorted, $order);
        $this->assertStringContainsString('id="dpr-panel"', $html);
    }

    public function test_dpr_fields_are_visible_while_creating_a_lead(): void
    {
        $html = $this->actingAs($this->admin)->get(route('admin.leads.create'))
            ->assertOk()
            ->assertSee('id="dpr-tab"', false)
            ->assertSee('+ Add Loan')
            ->assertSee('+ Add Machinery / Quotation')
            ->assertSee('+ Add Property')
            ->assertDontSee('Save the lead first')
            ->getContent();

        foreach (array_diff(array_keys(DprData::SCALARS), DprData::NOT_ON_FORM) as $field) {
            $this->assertStringContainsString("name=\"dpr[$field]\"", $html, "Missing DPR field {$field}");
            $this->assertDoesNotMatchRegularExpression('/name="dpr\[' . $field . '\]"[^>]*disabled/', $html);
        }
        $this->assertStringNotContainsString('Generate DPR', $html);
    }

    public function test_tab_is_hidden_without_view_permission(): void
    {
        $user = $this->userWith(['leads.edit', 'leads.create']);

        foreach ([$this->editPage($user), $this->actingAs($user)->get(route('admin.leads.create'))] as $response) {
            $response->assertOk()->assertDontSee('id="dpr-tab"', false)->assertDontSee('dpr[firm_name]', false);
        }
    }

    public function test_every_dynamic_dpr_field_is_on_the_form_and_static_text_has_none(): void
    {
        $html = $this->editPage()->getContent();

        foreach (range(0, DprData::MAX_LOANS - 1) as $i) {
            foreach (DprData::LOAN_KEYS as $k) {
                $this->assertStringContainsString("name=\"dpr[current_loans][$i][$k]\"", $html);
            }
        }
        foreach (range(0, DprData::MAX_MACHINERY - 1) as $i) {
            foreach (DprData::MACHINERY_KEYS as $k) {
                $this->assertStringContainsString("name=\"dpr[machinery][$i][$k]\"", $html);
            }
        }
        foreach (range(0, DprData::MAX_COLLATERAL - 1) as $i) {
            foreach (DprData::COLLATERAL_KEYS as $k) {
                $this->assertStringContainsString("name=\"dpr[collateral][$i][$k]\"", $html);
            }
        }
        foreach (array_keys(DprData::FINANCIAL_ROWS) as $row) {
            foreach (array_keys(DprData::FINANCIAL_PERIODS) as $period) {
                $this->assertStringContainsString("name=\"dpr[financials][$row][$period]\"", $html);
            }
        }

        // Totals are calculated, not entered.
        foreach (['total_project_cost', 'total_cost', 'total_finance', 'total_amount', 'machinery_total', 'fin_total'] as $calculated) {
            $this->assertStringNotContainsString('name="dpr[' . $calculated . ']"', $html);
        }
        // The report's static wording is shown, never offered as an input.
        $this->assertStringNotContainsString('DETAILED PROJECT REPORT', $html);
        $this->assertStringNotContainsString('name="dpr[conclusion]"', $html);
    }

    // ---- linking -------------------------------------------------------

    public function test_other_tabs_values_populate_the_matching_dpr_fields(): void
    {
        $d = DprData::for($this->lead);

        $this->assertSame('Shah Industries', $d['firm_name']);
        $this->assertSame('Proprietorship', $d['constitution']);
        $this->assertSame('Ravi Shah', $d['applicant_name']);
        $this->assertSame('Plot 14, MIDC, Pune', $d['business_address']);
        $this->assertSame('ABCDE1234F', $d['pan']);
        $this->assertSame('1234 5678 9012', $d['aadhaar']);
        $this->assertSame('Male', $d['gender']);
        $this->assertSame('9876501234', $d['mobile']);
        $this->assertSame('ravi@shah.test', $d['email']);
        $this->assertSame('B.Com', $d['education']);
        $this->assertSame('Sita Shah', $d['mother_name']);
        $this->assertSame('1985-04-12', $d['date_of_birth']);
        $this->assertSame('8 years', $d['experience']);
        $this->assertSame('A family trading business since 2015.', $d['proprietor_history']);
        $this->assertSame('A family trading business since 2015.', $d['business_profile']);
        $this->assertSame('Manufacturing', $d['activity']);
        $this->assertSame('Own factory shed, 5000 sq ft', $d['land_and_factory']);
        $this->assertSame('UDYAM-MH-01-0000001', $d['udyam']);
        $this->assertSame('27ABCDE1234F1Z5', $d['gst']);
        $this->assertSame('11520001000123', $d['fssai']);
        $this->assertSame('12', $d['manpower']);
        $this->assertSame('Near the highway', $d['connectivity']);
        // Bank details (first row), and the loan amounts
        $this->assertSame('State Bank of India', $d['present_bank']);
        $this->assertSame('1234567890', $d['account_number']);
        $this->assertSame('SBIN0001234', $d['ifsc']);
        $this->assertSame('Current', $d['account_type']);
        $this->assertSame('3000000', $d['term_required']);
        $this->assertSame('1000000', $d['cc_required']);
        $this->assertSame('1000000', $d['wc_amount']);
        // Current loans mirror the lead's
        $this->assertSame('HDFC', $d['current_loans'][0]['bank_name']);
        $this->assertSame('12000', $d['current_loans'][0]['emi']);

        // DPR-only fields start empty.
        foreach (['itr_filing', 'term_contribution', 'cost_land', 'process_steps', 'market_potential'] as $only) {
            $this->assertNull($d[$only]);
        }
        $this->assertSame([], $d['machinery']);
        $this->assertSame([], $d['collateral']);

        $this->editPage()->assertSee('value="Shah Industries"', false)->assertSee('value="HDFC"', false);
    }

    public function test_link_mapping_points_at_real_lead_inputs(): void
    {
        $page = $this->actingAs($this->admin)->get(route('admin.leads.create'))->getContent();

        foreach (DprData::LEAD_LINKS as $field => $link) {
            $this->assertArrayHasKey($field, DprData::SCALARS);
            foreach ($link['sources'] as $source) {
                // bank_details rows are added in the browser, so only the field family has to exist on the page.
                $present = str_contains($page, 'name="' . $source . '"') || str_starts_with($source, 'bank_details[');
                $this->assertTrue($present, "DPR {$field} reads missing input {$source}");
            }
        }
        $this->assertStringContainsString('"path":"firm_name"', $page);
        $this->assertStringContainsString('current_loans[', $page);
    }

    public function test_changing_other_tabs_updates_linked_fields_but_keeps_dpr_data(): void
    {
        $this->save([
            'firm_name' => 'Shah Industries', 'manpower' => '12', 'cost_land' => '1000000', 'machinery' => [['particulars' => 'Lathe', 'amount' => '500000']],
            'collateral' => [['type' => 'Land', 'address' => 'Mulshi', 'value' => '100']], 'process_steps' => 'Cut → Turn',
            'financials' => ['sales' => ['p1' => '45.10']],
        ], null, null, ['business_name' => 'Shah Industries']);

        $this->actingAs($this->admin)->put(route('admin.leads.update', $this->lead), [
            'name' => 'Ravi Shah', 'mobile_number' => '9876501234', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Shah Industries Pvt Ltd', 'no_of_manpower' => '25',
        ]);

        $d = DprData::for($this->lead->fresh());
        $this->assertSame('Shah Industries Pvt Ltd', $d['firm_name']);
        $this->assertSame('25', $d['manpower']);
        $this->assertSame('1000000', $d['cost_land']);
        $this->assertSame('Lathe', $d['machinery'][0]['particulars']);
        $this->assertSame('Mulshi', $d['collateral'][0]['address']);
        $this->assertSame('Cut → Turn', $d['process_steps']);
        $this->assertSame('45.10', $d['financials']['sales']['p1']);
    }

    public function test_a_hand_edited_linked_field_and_hand_edited_loans_are_kept(): void
    {
        $this->save([
            'firm_name' => 'Trade Name For Bank',
            'current_loans' => [['bank_name' => 'Edited Bank', 'loan_type' => 'Vehicle', 'loan_amount' => '1']],
        ]);

        $overrides = $this->lead->dprReport()->first()->linked_overrides;
        $this->assertContains('firm_name', $overrides);
        $this->assertContains('current_loans', $overrides);

        $this->actingAs($this->admin)->put(route('admin.leads.update', $this->lead), [
            'name' => 'Ravi Shah', 'mobile_number' => '9876501234', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Renamed', 'current_loans' => [['bank_name' => 'Another', 'loan_type' => 'X', 'loan_amount' => '9']],
        ]);

        $d = DprData::for($this->lead->fresh());
        $this->assertSame('Trade Name For Bank', $d['firm_name']);
        $this->assertSame('Edited Bank', $d['current_loans'][0]['bank_name']);
    }

    public function test_untouched_loans_keep_following_the_lead(): void
    {
        $this->save(['firm_name' => 'Shah Industries', 'current_loans' => [['bank_name' => 'HDFC', 'loan_type' => 'Vehicle', 'loan_amount' => '500000', 'emi' => '12000', 'outstanding_amount' => '300000']]], null, null, ['business_name' => 'Shah Industries']);

        $this->assertNotContains('current_loans', $this->lead->dprReport()->first()->linked_overrides);

        $this->actingAs($this->admin)->put(route('admin.leads.update', $this->lead), [
            'name' => 'Ravi Shah', 'mobile_number' => '9876501234', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'current_loans' => [['bank_name' => 'HDFC', 'loan_type' => 'Vehicle', 'loan_amount' => '500000', 'emi' => '15000', 'outstanding_amount' => '250000']],
        ]);

        $this->assertSame('15000', DprData::for($this->lead->fresh())['current_loans'][0]['emi']);
    }

    // ---- saving --------------------------------------------------------

    public function test_current_loan_machinery_and_collateral_rows_can_be_added_and_saved(): void
    {
        $this->save($this->fullReport())->assertRedirect(route('admin.leads.index'));

        $row = DprReport::where('lead_id', $this->lead->id)->firstOrFail();
        $this->assertCount(4, $row->current_loans);
        $this->assertCount(3, $row->machinery);
        $this->assertCount(3, $row->collateral);
        $this->assertSame('Lathe machine, model X200', $row->machinery[0]['particulars']);
        $this->assertSame('Land', $row->collateral[1]['type']);
        $this->assertSame('52.30', $row->financials['sales']['p2']);
        $this->assertNotNull($row->saved_at);

        // Empty rows are dropped, limits and choices enforced.
        $this->save(['machinery' => [['particulars' => ''], ['particulars' => 'Only']], 'collateral' => [[], ['type' => 'Land']]]);
        $row = $this->lead->dprReport()->first();
        $this->assertSame(['Only'], array_column($row->machinery, 'particulars'));
        $this->assertSame(['Land'], array_column($row->collateral, 'type'));

        $this->save(['machinery' => array_fill(0, DprData::MAX_MACHINERY + 1, ['particulars' => 'X'])])->assertSessionHasErrors('dpr.machinery');
        $this->save(['collateral' => [['type' => 'Spaceship']]])->assertSessionHasErrors('dpr.collateral.0.type');
        $this->save(['cost_land' => 'lots'])->assertSessionHasErrors('dpr.cost_land');

        $this->editPage()->assertSee('>Only</textarea>', false);
    }

    public function test_create_lead_saves_lead_and_dpr_together(): void
    {
        $this->actingAs($this->admin)->post(route('admin.leads.store'), [
            'name' => 'Meera Joshi', 'mobile_number' => '9811122233', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Joshi Foods',
            'dpr' => ['firm_name' => 'Joshi Foods', 'cost_land' => '500000', 'machinery' => [['particulars' => 'Mixer', 'amount' => '90000']], 'market_potential' => 'Growing demand.'],
        ])->assertRedirect(route('admin.leads.index'));

        $lead = Lead::where('name', 'Meera Joshi')->firstOrFail();
        $this->assertSame('500000', $lead->dprReport->cost_land);
        $this->assertSame('Mixer', $lead->dprReport->machinery[0]['particulars']);

        $this->editPage(null, $lead)->assertSee('value="500000"', false);
        $this->assertSame('Growing demand.', $lead->dprReport->market_potential);
    }

    public function test_lead_creation_works_with_an_untouched_dpr_tab(): void
    {
        $this->actingAs($this->admin)->post(route('admin.leads.store'), [
            'name' => 'No Tab', 'mobile_number' => '9822200001', 'loan_product_id' => $this->product->id, 'status' => 'new',
        ])->assertRedirect(route('admin.leads.index'));

        $this->assertDatabaseHas('leads', ['name' => 'No Tab']);
        $this->assertDatabaseCount('dpr_reports', 0);
    }

    public function test_lead_and_dpr_are_saved_atomically(): void
    {
        $this->app->bind(DprReportSaver::class, fn () => new class extends DprReportSaver {
            public function save(Lead $lead, array $submitted, User $user): DprReport
            {
                throw new \RuntimeException('DPR storage failed');
            }
        });
        $this->withoutExceptionHandling();

        try {
            $this->actingAs($this->admin)->post(route('admin.leads.store'), [
                'name' => 'Should Not Exist', 'mobile_number' => '9833300001', 'loan_product_id' => $this->product->id, 'status' => 'new',
                'dpr' => ['cost_land' => '1'],
            ]);
            $this->fail('The failure should propagate');
        } catch (\RuntimeException $e) {
            $this->assertSame('DPR storage failed', $e->getMessage());
        }

        $this->assertDatabaseMissing('leads', ['name' => 'Should Not Exist']);
    }

    // ---- generation ----------------------------------------------------

    public function test_the_existing_generator_still_works_and_uses_the_lead_data(): void
    {
        $this->generate()->assertRedirect()->assertSessionHas('success');
        $this->actingAs($this->admin)->get(route('admin.leads.dpr.download', $this->lead))->assertOk();

        $xml = $this->documentXml($this->generatedPath());
        $out = $this->plainText($xml);

        $this->assertStringContainsString('Shah Industries', $out);
        $this->assertStringContainsString('Manufacturing', $out);
        $basic = $this->tableRows($xml, 'Name of the Unit');
        $this->assertSame('Shah Industries', $basic[0][2]);
        $this->assertSame('ABCDE1234F', $basic[3][2]);
        $this->assertSame('1234 5678 9012', $basic[4][2]);
        $this->assertSame('Male', $basic[5][2]);
        $this->assertSame('9876501234', $basic[6][2]);
        $this->assertSame('ravi@shah.test', $basic[7][2]);
        $this->assertSame('B.Com', $basic[8][2]);
        $this->assertSame('Sita Shah', $basic[9][2]);
        $this->assertSame('12/04/1985', $basic[10][2]);
        $this->assertSame('8 years', $basic[11][2]);
        $this->assertStringContainsString('Ravi Shah', $basic[2][2]);
        $this->assertStringContainsString('Plot 14, MIDC, Pune', $basic[2][2]);
        $this->assertStringNotContainsString('(name)', $basic[2][2]);
        $this->assertStringNotContainsString('(address)', $basic[2][2]);
    }

    public function test_saved_dpr_values_appear_in_the_generated_document(): void
    {
        $this->save($this->fullReport(), null, null, ['business_name' => 'Shah Industries']);
        $this->generate()->assertSessionHas('success');

        $xml = $this->documentXml($this->generatedPath());
        $out = $this->plainText($xml);

        // Cover, introduction, conclusion
        $this->assertStringContainsString('Manufacturing of spare parts', $out);
        $this->assertStringContainsString('Shah Industries', $out);
        $this->assertStringContainsString('MIDC Industrial Area, Pune', $out);
        $this->assertStringContainsString('₹30,00,000', $out);
        $this->assertStringContainsString('₹10,00,000', $out);
        $this->assertStringContainsString('The proposed Manufacturing of spare parts Project of Shah Industries is technically feasible', $out);
        $this->assertStringContainsString('recommended for sanction of Term Loan of ₹30,00,000 and Cash Credit limit of ₹10,00,000.', $out);

        // Business profile rows
        $profile = $this->tableRows($xml, 'Nature of the Business Activity');
        $this->assertSame('Manufacturing of spare parts', $profile[0][2]);
        $this->assertSame('Own factory shed, 5000 sq ft', $profile[2][2]);
        $this->assertSame('Last 3 years', $profile[8][2]);
        $this->assertStringContainsString('State Bank of India – Current', $profile[9][2]);
        $this->assertStringContainsString('A/C NO. – 1234567890', $profile[9][2]);
        $this->assertStringContainsString('IFSC COAD : SBIN0001234', $profile[9][2]);

        // Current loans: four loans (template has three blank rows, one is added), TOTAL row kept.
        $loans = $this->tableRows($xml, 'OUTSTANDING AMOUNT');
        $this->assertCount(6, $loans); // header + 4 loans + TOTAL
        $this->assertSame(['1', 'HDFC', 'Vehicle', '₹5,00,000', '₹12,000', '₹3,00,000'], $loans[1]);
        $this->assertSame(['2', 'ICICI', 'Business', '₹10,00,000', '₹25,000', '₹7,00,000'], $loans[2]);
        $this->assertSame(['3', 'Axis', 'Equipment', '₹2,00,000', '', ''], $loans[3]);
        $this->assertSame('4', $loans[4][0]);
        $this->assertSame('TOTAL', $loans[5][1]);

        // Required loan, with totals calculated
        $required = $this->tableRows($xml, 'REQUIRED LOAN AMOUNT');
        $this->assertSame(['TERM LOAN', '₹30,00,000', '₹10,00,000', '₹40,00,000'], $required[1]);
        $this->assertSame(['CASH CREDIT', '₹10,00,000', '₹2,50,000', '₹12,50,000'], $required[2]);

        // Project cost and means of finance, with totals calculated
        $cost = $this->tableRows($xml, 'LAND VALUE');
        $this->assertSame('₹10,00,000', $cost[0][1]);
        $this->assertSame('₹15,00,000', $cost[1][1]);
        $this->assertSame('₹25,00,000', $cost[2][1]);
        $this->assertSame('₹5,00,000', $cost[3][1]);
        $this->assertSame(['TOTAL PROJECT COST', '₹55,00,000'], $cost[5]);
        $means = $this->tableRows($xml, 'PRAMOTER CAPITAL');
        $this->assertSame('₹12,50,000', $means[0][1]);
        $this->assertSame('₹30,00,000', $means[1][1]);
        $this->assertSame('₹10,00,000', $means[2][1]);
        $this->assertSame(['TOTAL', '₹52,50,000'], $means[3]);

        // Quotation: three machines, total and term loan required calculated.
        $quotation = $this->tableRows($xml, 'Term Loan Required');
        $this->assertCount(6, $quotation);
        $this->assertSame(['1.', 'Lathe machine, model X200', '2 sets', '₹12,00,000'], $quotation[1]);
        $this->assertSame(['3.', 'Compressor', '1', '₹1,50,000'], $quotation[3]);
        $this->assertSame('₹17,00,000', $quotation[4][3]);
        $this->assertSame('₹30,00,000', $quotation[5][3]);

        // Process
        $this->assertStringContainsString('PROCESS : Cutting → Turning → Drilling → Finishing', $out);
        $this->assertStringContainsString('Machined spare parts', $out);
        $this->assertStringContainsString('Raw steel is cut, turned and drilled to drawing.', $out);

        // Collateral
        $collateral = $this->tableRows($xml, 'TYPE OF PROPERTY');
        $this->assertSame(['1', 'Residential', 'Flat 4, Baner, Pune', '₹45,00,000'], $collateral[1]);
        $this->assertSame(['3', 'Commercial', 'Shop 2, Camp', '₹18,00,000'], $collateral[3]);

        // Working capital sentence, market potential, financial table
        $this->assertStringContainsString('assessed at ₹10,00,000 in the form of Cash Credit limit', $out);
        $this->assertStringContainsString('raw material procurement (₹6,00,000), power & fuel expenses (₹1,00,000), labour charges (₹2,00,000) and receivables (₹1,00,000)', $out);
        $this->assertStringContainsString('Demand for precision spare parts is growing with auto-component exports.', $out);
        $this->assertStringNotContainsString('global groundnut oil market', $out);
        $financial = $this->tableRows($xml, 'NTREST AND DEPRICIATION');
        $this->assertSame(['45.10', '52.30', '28.00'], array_slice($financial[1], 1));
        $this->assertSame(['20.00', '24.50', ''], array_slice($financial[3], 1));
        $this->assertSame(['4.10', '5.20', '3.00'], array_slice($financial[6], 1));
        $this->assertSame(['', '', '41.25'], array_slice($financial[7], 1));
    }

    public function test_sample_data_is_never_copied_and_missing_values_stay_blank(): void
    {
        $bare = Lead::create(['loan_product_id' => $this->product->id, 'name' => 'Bare Lead', 'mobile_number' => '9844400001', 'status' => 'new']);
        $this->generate($bare)->assertSessionHas('success');

        $xml = $this->documentXml($this->generatedPath($bare));
        $out = $this->plainText($xml);

        // The market-potential paragraph is the template's own static prose; it stays unless a market analysis is entered.
        $beyondMarket = implode("\n", array_filter(explode("\n", $out), fn ($line) => ! str_contains($line, 'global groundnut oil market')));
        foreach (self::SAMPLE_VALUES as $sample) {
            $this->assertStringNotContainsString($sample, $beyondMarket, "Template sample '{$sample}' leaked");
        }
        foreach (['N/A', 'XYZ'] as $filler) {
            $this->assertStringNotContainsString($filler, $out);
        }

        // Tables carry no demo rows.
        $loans = $this->tableRows($xml, 'OUTSTANDING AMOUNT');
        foreach (array_slice($loans, 1, 3) as $row) {
            $this->assertSame(['', '', '', '', '', ''], $row);
        }
        $required = $this->tableRows($xml, 'REQUIRED LOAN AMOUNT');
        $this->assertSame(['TERM LOAN', '', '', ''], $required[1]);
        $this->assertSame(['CASH CREDIT', '', '', ''], $required[2]);
        $cost = $this->tableRows($xml, 'LAND VALUE');
        foreach ([0, 1, 2, 3] as $i) {
            $this->assertSame('', $cost[$i][1]);
        }
        $this->assertSame('', $cost[5][1]);
        $means = $this->tableRows($xml, 'PRAMOTER CAPITAL');
        $this->assertSame('', $means[3][1]);
        $quotation = $this->tableRows($xml, 'Term Loan Required');
        $this->assertSame(['', '', '', ''], $quotation[1]);
        $collateral = $this->tableRows($xml, 'TYPE OF PROPERTY');
        foreach (array_slice($collateral, 1) as $row) {
            $this->assertSame(['', '', '', ''], $row);
        }
        $financial = $this->tableRows($xml, 'NTREST AND DEPRICIATION');
        foreach ([1, 3, 5, 6, 7, 8, 9] as $i) {
            $this->assertSame(['', '', ''], array_slice($financial[$i], 1), "financial row {$i}");
        }
        $basic = $this->tableRows($xml, 'Name of the Unit');
        $this->assertSame('', $basic[3][2]);
        $this->assertSame('', $basic[4][2]);
        $profile = $this->tableRows($xml, 'Nature of the Business Activity');
        $this->assertSame('', $profile[0][2]);
        $this->assertSame('', $profile[2][2]);
        $this->assertSame('', $profile[8][2]);

        // The lead's own name is real data, so the applicant line carries it; static headings remain.
        $this->assertStringContainsString('Bare Lead', $basic[2][2]);
        foreach (['DETAILED PROJECT REPORT (DPR)', 'BASIC INFORMATION:', 'CURRENT LOAN DETAILS :', 'REQUIRED LOAN AMOUNT :', 'PROJECT COST :', 'MEANS OF FINANCE ;', 'CONCLUSION :', 'technically feasible, financially viable and bankable'] as $static) {
            $this->assertStringContainsString($static, $out);
        }
    }

    public function test_generated_dpr_keeps_the_templates_structure(): void
    {
        $this->save($this->fullReport());
        $this->generate();

        $master = $this->documentXml(public_path(self::TEMPLATE));
        $xml = $this->documentXml($this->generatedPath());

        // Static headings, fonts and parts are unchanged; no highlighter is left.
        $this->assertStringNotContainsString('w:highlight', $xml);
        $zipM = new ZipArchive();
        $zipM->open(public_path(self::TEMPLATE));
        $zipG = new ZipArchive();
        $zipG->open($this->generatedPath());
        foreach (['word/styles.xml', 'word/numbering.xml', 'word/fontTable.xml', 'word/settings.xml', 'word/theme/theme1.xml'] as $part) {
            $this->assertSame($zipM->getFromName($part), $zipG->getFromName($part), "{$part} changed");
        }
        $zipM->close();
        $zipG->close();

        // Same set of tables, same section headings.
        $this->assertSame(substr_count($master, '<w:tbl>'), substr_count($xml, '<w:tbl>'));
        $out = $this->plainText($xml);
        foreach (['BASIC INFORMATION:', 'BUSINESS PROFILE:', 'CURRENT LOAN DETAILS :', 'REQUIRED LOAN AMOUNT :', 'PROJECT COST :', 'MEANS OF FINANCE ;',
            'QUATATION SUMMARY FOR NEW MACHINARY', '9.PHOTOS OF NEW MACHINARY', '10.MANUFACTURING PROCESS :', '11.PHOTOS OF FINAL PRODUCTS :',
            '12.Details of Collateral Property/CGTMSE', '13. WORKING CAPITAL REQUIREMENT :', '14. MARKET POTENTIAL :', '15.Financial Details in Brief :', '16. CONCLUSION :'] as $heading) {
            $this->assertStringContainsString($heading, $out);
        }
    }

    public function test_master_template_is_never_modified(): void
    {
        $path = public_path(self::TEMPLATE);
        $before = md5_file($path);

        $this->save($this->fullReport());
        $this->generate();

        $this->assertSame($before, md5_file($path));
    }

    // ---- permissions ---------------------------------------------------

    public function test_create_and_update_permissions_decide_whether_the_tab_is_saved(): void
    {
        $viewOnly = $this->userWith(['leads.edit', 'dpr.view']);
        $updater = $this->userWith(['leads.edit', 'dpr.view', 'dpr.update']);
        $creator = $this->userWith(['leads.edit', 'dpr.view', 'dpr.create']);

        $this->save(['cost_land' => '1'], $viewOnly);
        $this->save(['cost_land' => '1'], $updater);
        $this->assertDatabaseCount('dpr_reports', 0);

        $this->save(['cost_land' => '2'], $creator);
        $this->assertSame('2', $this->lead->dprReport()->first()->cost_land);

        $this->save(['cost_land' => '3'], $creator);
        $this->assertSame('2', $this->lead->dprReport()->first()->cost_land);

        $this->save(['cost_land' => '4'], $updater);
        $this->assertSame('4', $this->lead->dprReport()->first()->cost_land);
    }

    public function test_read_only_users_get_disabled_fields(): void
    {
        $viewer = $this->userWith(['leads.edit', 'dpr.view']);

        $this->assertMatchesRegularExpression('/name="dpr\[firm_name\]"[^>]*disabled/', $this->editPage($viewer)->getContent());
    }

    public function test_create_ignores_the_dpr_tab_without_permission(): void
    {
        $user = $this->userWith(['leads.create']);

        $this->actingAs($user)->post(route('admin.leads.store'), [
            'name' => 'Sneaky', 'mobile_number' => '9833300002', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'dpr' => ['cost_land' => '1'],
        ])->assertRedirect(route('admin.leads.index'));

        $this->assertDatabaseHas('leads', ['name' => 'Sneaky']);
        $this->assertDatabaseCount('dpr_reports', 0);
    }

    public function test_generate_and_download_permissions_gate_the_buttons_and_routes(): void
    {
        $this->generate();
        $generator = $this->userWith(['leads.edit', 'dpr.view', 'dpr.generate']);
        $downloader = $this->userWith(['leads.edit', 'dpr.view', 'dpr.download']);

        $this->editPage($generator)->assertSee('Generate DPR')->assertDontSee('Download DPR');
        $this->editPage($downloader)->assertSee('Download DPR')->assertDontSee('Generate DPR');

        $this->actingAs($generator)->get(route('admin.leads.dpr.download', $this->lead))->assertForbidden();
        $this->actingAs($downloader)->post(route('admin.leads.dpr.generate', $this->lead))->assertForbidden();
        $this->actingAs($downloader)->get(route('admin.leads.dpr.download', $this->lead))->assertOk();
    }

    // ---- sub-tabs, photos, conclusion ------------------------------------

    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    private function photo(string $name = 'machine.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(self::PNG));
    }

    public function test_exactly_nine_sub_tabs_appear_in_order_on_create_and_edit(): void
    {
        $expected = ['Basic Information', 'Business Profile', 'Current Loan Details', 'Required Loan Amount', 'Project Cost', 'Means of Finance',
            'Quotation Summary for New Machinery', 'Details of Collateral Property/CGTMSE', 'Financial Details in Brief'];

        foreach ([$this->editPage(), $this->actingAs($this->admin)->get(route('admin.leads.create'))] as $response) {
            $html = $response->assertOk()->getContent();
            preg_match_all('/dpr-sub-btn" data-dpr-target="[a-z_]+">\d+\. ([^<]+)</', $html, $m);
            $this->assertSame($expected, array_map('html_entity_decode', $m[1]));
            preg_match_all('/<div id="dpr-s-([a-z_]+)" class="dpr-sub-panel/', $html, $panels);
            $this->assertCount(9, $panels[1]);
        }
    }

    public function test_total_capital_is_the_sum_of_capital_and_unsecured_loan_unless_given(): void
    {
        $data = DprData::normalize(['financials' => [
            'own_capital' => ['p1' => '10.50', 'p2' => '20', 'p3' => '5'],
            'unsecured_loan' => ['p1' => '4.25', 'p2' => '1'],
            'capital' => ['p3' => '99'],
        ]]);

        $this->assertSame('14.75', $data['financials']['capital']['p1']);
        $this->assertSame('21', $data['financials']['capital']['p2']);
        $this->assertSame('99', $data['financials']['capital']['p3']);
        $this->assertNull(DprData::normalize([])['financials']['capital']['p1']);
    }

    public function test_photos_use_the_lead_documents_upload_system(): void
    {
        Storage::fake('public');

        $create = $this->actingAs($this->admin)->get(route('admin.leads.create'))->assertOk()->getContent();
        $this->assertSame(1, substr_count($create, 'name="documents[machinery_photos][files][]"'));
        $this->assertSame(1, substr_count($create, 'name="documents[final_product_photos][files][]"'));
        $this->assertStringNotContainsString('dpr-s-machinery_photos', $create);

        $this->actingAs($this->admin)->put(route('admin.leads.update', $this->lead), [
            'name' => $this->lead->name, 'mobile_number' => $this->lead->mobile_number, 'loan_product_id' => $this->product->id, 'status' => 'new',
            'documents' => ['machinery_photos' => ['files' => [$this->photo('a.png'), $this->photo('b.png')]], 'final_product_photos' => ['files' => [$this->photo('p.png')]]],
        ])->assertRedirect();

        $this->assertSame(3, $this->lead->leadDocuments()->count());
        $html = $this->editPage(null, $this->lead->fresh())->getContent();
        foreach (['a.png', 'b.png', 'p.png'] as $name) {
            $this->assertStringContainsString($name, $html);
        }
        // Uploaded in the Documents tab; the DPR has no photo sub-tabs.
        $this->assertSame(1, substr_count($html, 'name="documents[machinery_photos][files][]"'));
    }

    public function test_generated_dpr_places_the_uploaded_photos_under_their_headings(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin)->put(route('admin.leads.update', $this->lead), [
            'name' => $this->lead->name, 'mobile_number' => $this->lead->mobile_number, 'loan_product_id' => $this->product->id, 'status' => 'new',
            'documents' => ['machinery_photos' => ['files' => [$this->photo('a.png'), $this->photo('b.png'), $this->photo('c.png')]], 'final_product_photos' => ['files' => [$this->photo('p.png')]]],
        ])->assertRedirect();

        $this->generate()->assertRedirect();

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($this->generatedPath()) === true);
        $xml = $zip->getFromName('word/document.xml');
        $rels = $zip->getFromName('word/_rels/document.xml.rels');
        $types = $zip->getFromName('[Content_Types].xml');
        $media = array_filter(array_map(fn ($i) => $zip->getNameIndex($i), range(0, $zip->numFiles - 1)), fn ($n) => str_starts_with($n, 'word/media/'));
        $zip->close();

        $this->assertCount(4, $media);
        $this->assertSame(4, substr_count($rels, 'relationships/image'));
        $this->assertStringContainsString('Extension="png"', $types);
        $this->assertSame(4, substr_count($xml, 'r:embed="rIdDprPhoto'));

        // Machinery photos sit right after their heading and before the next one.
        $x = $this->xp($xml);
        $positions = [];
        foreach ($x->query('//w:body/w:p') as $i => $p) {
            $t = $p->textContent;
            if (str_contains($t, 'PHOTOS OF NEW MACHINARY')) { $positions['m'] = $i; }
            if (str_contains($t, 'MANUFACTURING PROCESS')) { $positions['proc'] = $i; }
            if (str_contains($t, 'PHOTOS OF FINAL PRODUCTS')) { $positions['f'] = $i; }
            if (str_contains($t, 'Details of Collateral')) { $positions['col'] = $i; }
        }
        $withImages = [];
        foreach ($x->query('//w:body/w:p') as $i => $p) {
            if ($x->query('.//w:drawing[.//*[local-name()="blip"]]', $p)->length) { $withImages[] = $i; }
        }
        $this->assertCount(2, array_filter($withImages, fn ($i) => $i > $positions['m'] && $i < $positions['proc']));
        $this->assertCount(1, array_filter($withImages, fn ($i) => $i > $positions['f'] && $i < $positions['col']));
    }

    public function test_a_report_without_photos_adds_no_media(): void
    {
        $this->generate()->assertRedirect();

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($this->generatedPath()) === true);
        foreach (range(0, $zip->numFiles - 1) as $i) {
            $this->assertStringStartsNotWith('word/media/', $zip->getNameIndex($i));
        }
        $zip->close();
    }

    public function test_row_remove_controls_and_quotation_extras_are_rendered(): void
    {
        $html = $this->editPage()->getContent();

        $this->assertGreaterThanOrEqual(DprData::MAX_LOANS + DprData::MAX_MACHINERY + DprData::MAX_COLLATERAL, substr_count($html, 'dpr-remove'));
        $this->assertMatchesRegularExpression('/>TOTAL<\/td><td><\/td><td class="dpr-calc" id="dpr-total-machinery"/', $html);
        $this->assertStringContainsString('Term Loan Required', $html);
        $this->assertStringContainsString('id="dpr-term-mirror"', $html);
    }

    public function test_saving_the_form_keeps_stored_values_of_fields_it_no_longer_shows(): void
    {
        DprReport::create(['lead_id' => $this->lead->id, 'saved_at' => now(), 'process_steps' => 'Cut → Weld', 'wc_amount' => '700000', 'market_potential' => 'Strong demand.']);

        $this->save(['cost_land' => '123000'])->assertRedirect();

        $row = $this->lead->fresh()->dprReport;
        $this->assertSame('123000', $row->cost_land);
        $this->assertSame('Cut → Weld', $row->process_steps);
        $this->assertSame('700000', $row->wc_amount);
        $this->assertSame('Strong demand.', $row->market_potential);

        $html = $this->editPage()->getContent();
        foreach (DprData::NOT_ON_FORM as $field) {
            $this->assertStringNotContainsString("name=\"dpr[$field]\"", $html);
        }

        $this->generate()->assertRedirect();
        $this->assertStringContainsString('Cut → Weld', $this->plainText($this->documentXml($this->generatedPath())));
    }

    public function test_each_sub_tab_is_a_template_style_table_with_the_templates_labels(): void
    {
        $html = $this->editPage()->getContent();
        preg_match_all('/<div id="dpr-s-[a-z_]+" class="dpr-sub-panel[^"]*">\s*<div class="dpr-doc">(.*?)<\/table>/s', $html, $m);
        $this->assertCount(9, $m[1]);

        // Labels and column headings, in the template's order.
        $expected = [
            ['BASIC INFORMATION:', 'Name of the Unit', 'Constitution', 'Name of Proprietor', 'Pan no.', 'ADHARCARD NO.', 'GENDER', 'MOBILE NO.', 'EMAIL ID.', 'EDUCATION', 'MOTHER NAME', 'DATE OF BIRTH', 'EXPIRIANCE', 'HISTRY /PROFILE OF PROPRIETOR'],
            ['BUSINESS PROFILE:', 'Nature of the Business Activity', 'BUSINESS PROFILE', 'Land and Factory Building', 'UDYAM NO.', 'GST NO.', 'FSSAI LICENSE', 'NUMBER OF MANPOWER', 'CONNECTIVITY', 'ITR Filling', 'Present Banking', 'A/C NO.', 'IFSC COAD'],
            ['CURRENT LOAN DETAILS :', 'SR NO.', 'BANK NAME', 'LOAN TYPE', 'LOAN AMOUNT', 'EMI', 'OUTSTANDING AMOUNT'],
            ['REQUIRED LOAN AMOUNT :', 'LOAN TYPE', 'REQUIRED LOAN AMOUNT', 'PRO. CONTRIBUTION', 'TOTAL AMOUNT', 'TERM LOAN', 'CASH CREDIT'],
            ['PROJECT COST :', 'LAND VALUE', 'BUIDING & CONSTRUCTION', 'PLANT & MACHINARY', 'WORKING CAPITAL', 'TOTAL PROJECT COST'],
            ['MEANS OF FINANCE ;', 'PRAMOTER CAPITAL', 'TERM LOAN', 'CASH CREDIT', 'TOTAL'],
            ['QUATATION SUMMARY FOR NEW MACHINARY', 'Sr. No', 'Particulars', 'Qty', 'Amount (Rs.)'],
            ['Details of Collateral Property/CGTMSE', 'SR NO.', 'TYPE OF PROPERTY', 'ADDRESS', 'APROX VALUE'],
            ['Financial Details in Brief :', 'F.Y. 23-24', 'F.Y. 24-25', 'A.Y 26-27 (provisional)', 'SALES', 'PURCHASE', 'DEPRICIATION', 'CAPITAL', 'UNSECURED LOAN', 'TOTAL CAPITAL + UNSECURED LOAN', 'INVESTMENT'],
        ];
        foreach ($expected as $i => $labels) {
            $text = html_entity_decode(strip_tags(str_replace(['<br>', '</td>', '</th>'], ' ', $m[1][$i])));
            $at = -1;
            foreach ($labels as $label) {
                $pos = stripos($text, $label, $at + 1);
                $this->assertNotFalse($pos, "Panel " . ($i + 1) . " lacks '$label'");
                $this->assertGreaterThan($at, $pos, "'$label' is out of order in panel " . ($i + 1));
                $at = $pos;
            }
        }
    }

    public function test_dpr_styles_are_scoped_to_the_dpr_tables(): void
    {
        $html = $this->editPage()->getContent();
        preg_match('/<style>\s*\/\* The DPR tables only.*?<\/style>/s', $html, $m);
        $this->assertNotEmpty($m);
        $css = preg_replace('/<\/?style>|\/\*.*?\*\//s', '', $m[0]);
        preg_match_all('/([^{}]+)\{/', $css, $selectors);
        $this->assertNotEmpty($selectors[1]);
        foreach ($selectors[1] as $group) {
            foreach (explode(',', $group) as $selector) {
                $this->assertStringStartsWith('#dpr-panel .dpr-doc', trim($selector));
            }
        }
    }
}
