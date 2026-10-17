<?php

namespace Tests\Feature;

use App\Models\ApplicationForm;
use App\Models\Lead;
use App\Models\LoanProduct;
use App\Models\User;
use App\Services\ApplicationFormGenerator;
use App\Services\ApplicationFormSaver;
use App\Support\ApplicationFormCellMap;
use App\Support\ApplicationFormData;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class ApplicationFormTest extends TestCase
{
    use RefreshDatabase;

    private const SAMPLE_VALUES = [
        'SUVARNA', 'SUVARNA SALES AGENCY', 'RAJULA', 'SHREEJI', 'KHADPITH', 'GUJARAT', 'SKANABAR55', 'SANJAYBHAI',
        '9879891659', 'BNYPK4855R', 'UDYAM-GJ-02-0031290', 'WHOLSALE', 'STOCK PURCHASE', '09,75,000', '3715240',
        '5650800', '6526600', '7179260', '664463', '858750', '1003401', '1123714', '17/08/2026', 'Tarun', 'GRADUATE',
        '8501 8388 6627',
    ];

    private User $admin;
    private Lead $lead;
    private LoanProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->admin = User::factory()->create(['status' => 'active']);
        $this->admin->assignRole('Admin');

        $this->product = LoanProduct::firstOrCreate(['code' => 'BL'], ['name' => 'Business Loan', 'status' => 'active']);

        $this->lead = Lead::create([
            'loan_product_id' => $this->product->id,
            'name' => 'Ravi Patel',
            'business_name' => 'Patel Traders',
            'mobile_number' => '9876501234',
            'email' => 'ravi@example.com',
            'pan_card' => 'ABCDE1234F',
            'aadhar_card' => '1234 5678 9012',
            'gender' => 'Male',
            'date_of_birth' => '1985-04-12',
            'business_address' => 'Shop 4, Ring Road, Surat 395002',
            'address' => '22 Garden Society, Surat',
            'business_activity' => 'Wholesale grains',
            'education' => 'B.Com',
            'udyam_registration' => 'UDYAM-GJ-22-0001234',
            'constitution_of_business' => 'Proprietorship',
            'cc_amount' => '9,75,000',
            'status' => 'new',
        ]);
    }

    // ---- helpers -------------------------------------------------------

    private function userWith(array $permissions): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->givePermissionTo($permissions);

        return $user;
    }

    private function editPage(User $user, ?Lead $lead = null)
    {
        return $this->actingAs($user)->get(route('admin.leads.edit', $lead ?? $this->lead));
    }

    /**
     * Submits the lead form (with the Application Form tab's fields) the way the browser does.
     */
    private function save(array $af, ?User $user = null, ?Lead $lead = null, array $leadFields = [])
    {
        $lead ??= $this->lead;

        return $this->actingAs($user ?? $this->admin)->put(route('admin.leads.update', $lead), [
            'name' => $lead->name,
            'mobile_number' => $lead->mobile_number,
            'loan_product_id' => $this->product->id,
            'status' => 'new',
            'af' => $af,
        ] + $leadFields);
    }

    private function generate(?User $user = null, ?Lead $lead = null)
    {
        return $this->actingAs($user ?? $this->admin)->post(route('admin.leads.application-form.generate', $lead ?? $this->lead));
    }

    /**
     * @return array<string, array<string, array{v: string, f: ?string, s: ?string}>> sheet name => ref => cell
     */
    private function readWorkbook(string $binary): array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'afx');
        file_put_contents($tmp, $binary);
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($tmp) === true, 'Generated file is a valid zip');

        $shared = [];
        $ss = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
        foreach ($ss->si as $si) {
            $text = (string) ($si->t ?? '');
            foreach ($si->r as $r) {
                $text .= (string) $r->t;
            }
            $shared[] = $text;
        }

        $workbook = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        $rels = [];
        foreach (simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'))->Relationship as $rel) {
            $rels[(string) $rel['Id']] = (string) $rel['Target'];
        }

        $sheets = [];
        foreach ($workbook->sheets->sheet as $sheet) {
            $rid = (string) $sheet->attributes('r', true)['id'];
            $xml = simplexml_load_string($zip->getFromName('xl/' . $rels[$rid]));
            $cells = [];
            foreach ($xml->sheetData->row as $row) {
                foreach ($row->c as $c) {
                    $type = (string) $c['t'];
                    $v = match (true) {
                        $type === 'inlineStr' => (string) ($c->is->t ?? '') . implode('', array_map(fn ($r) => (string) $r->t, iterator_to_array($c->is->r, false))),
                        $type === 's' => $shared[(int) $c->v] ?? '',
                        default => (string) ($c->v ?? ''),
                    };
                    $cells[(string) $c['r']] = ['v' => $v, 'f' => isset($c->f) ? (string) $c->f : null, 's' => (string) $c['s'] ?: null];
                }
            }
            $sheets[(string) $sheet['name']] = $cells;
        }

        $zip->close();
        @unlink($tmp);

        return $sheets;
    }

    private function generatedWorkbook(?Lead $lead = null): array
    {
        $lead ??= $this->lead;

        return $this->readWorkbook(Storage::disk('local')->get(ApplicationFormGenerator::relativePath($lead)));
    }

    // ---- tab visibility & access --------------------------------------

    public function test_application_form_tab_appears_with_permission(): void
    {
        $user = $this->userWith(['leads.edit', 'application_form.view']);

        $this->editPage($user)
            ->assertOk()
            ->assertSee('id="application-form-tab"', false)
            ->assertSee('id="application-form-panel"', false);
    }

    public function test_tab_order_puts_application_form_and_documents_before_the_other_tabs(): void
    {
        $html = $this->editPage($this->admin)->getContent();

        $order = array_map(fn ($id) => strpos($html, 'id="' . $id . '"'), ['kyc-tab', 'bank-loan-tab', 'application-form-tab', 'documents-tab', 'bank-tab', 'business-tab', 'loan-tab']);

        $this->assertNotContains(false, $order);
        $sorted = $order;
        sort($sorted);
        $this->assertSame($sorted, $order, 'Tabs must run: Details Sheet, Bank Loan, Application Form, Documents, Bank Details, Business Details, Loan Details');
    }

    public function test_application_form_tab_is_blocked_without_permission(): void
    {
        $user = $this->userWith(['leads.edit']);

        $this->editPage($user)
            ->assertOk()
            ->assertDontSee('id="application-form-tab"', false)
            ->assertDontSee('id="application-form-panel"', false);

        $this->actingAs($user)->get(route('admin.leads.application-form.show', $this->lead))->assertForbidden();

        // The tab isn't theirs to submit: their lead save works, the Application Form part is ignored.
        $this->save(['enterprise_name' => 'X'], $user)->assertRedirect(route('admin.leads.index'));
        $this->assertDatabaseCount('application_forms', 0);
    }

    public function test_view_permission_opens_the_tab_from_the_direct_url(): void
    {
        $user = $this->userWith(['leads.edit', 'application_form.view']);

        $this->actingAs($user)->get(route('admin.leads.application-form.show', $this->lead))
            ->assertRedirect(route('admin.leads.edit', $this->lead));
    }

    public function test_create_page_shows_the_full_application_form_without_a_saved_lead(): void
    {
        $html = $this->actingAs($this->admin)->get(route('admin.leads.create'))
            ->assertOk()
            ->assertSee('id="application-form-tab"', false)
            ->assertSee('id="application-form-panel"', false)
            ->assertSee('af[enterprise_name]', false)
            ->assertDontSee('Save the lead first')
            ->getContent();

        // Bank Loan, Application Form, Documents, then the rest, as on edit, and the fields are live (not disabled).
        $this->assertTrue(strpos($html, 'id="bank-loan-tab"') < strpos($html, 'id="application-form-tab"')
            && strpos($html, 'id="application-form-tab"') < strpos($html, 'id="documents-tab"')
            && strpos($html, 'id="documents-tab"') < strpos($html, 'id="bank-tab"'));
        $this->assertStringNotContainsString('name="af[enterprise_name]" disabled', $html);
        // Generate/Download need a saved file, so they are not offered yet.
        $this->assertStringNotContainsString('Generate Application Form', $html);
    }

    public function test_create_page_hides_the_tab_without_permission(): void
    {
        $user = $this->userWith(['leads.create']);

        $this->actingAs($user)->get(route('admin.leads.create'))
            ->assertOk()
            ->assertDontSee('id="application-form-tab"', false)
            ->assertDontSee('af[enterprise_name]', false);
    }

    /**
     * @return array<string, string> table id => its HTML
     */
    private function tableSections(string $html): array
    {
        $t1 = strpos($html, 'id="af-t1"');
        $t2 = strpos($html, 'id="af-t2"');
        $t3 = strpos($html, 'id="af-t3"');
        $end = strlen($html);

        return [
            't1' => substr($html, $t1, $t2 - $t1),
            't2' => substr($html, $t2, $t3 - $t2),
            't3' => substr($html, $t3, $end - $t3),
        ];
    }

    public function test_every_table_1_field_is_on_the_form(): void
    {
        $t1 = $this->tableSections($this->actingAs($this->admin)->get(route('admin.leads.create'))->getContent())['t1'];

        foreach (ApplicationFormCellMap::scalarFieldCells() as $field => [$sheet]) {
            if ($sheet === ApplicationFormCellMap::SHEET_1) {
                $this->assertStringContainsString('name="af[' . $field . ']"', $t1, "Table 1 is missing {$field}");
            }
        }
    }

    public function test_every_table_2_field_is_on_the_form(): void
    {
        $t2 = $this->tableSections($this->actingAs($this->admin)->get(route('admin.leads.create'))->getContent())['t2'];

        foreach (ApplicationFormCellMap::scalarFieldCells() as $field => [$sheet]) {
            if ($sheet === ApplicationFormCellMap::SHEET_2) {
                $this->assertStringContainsString('name="af[' . $field . ']"', $t2, "Table 2 is missing {$field}");
            }
        }
        for ($i = 0; $i < ApplicationFormData::MAX_ASSOCIATES; $i++) {
            foreach (ApplicationFormData::ASSOCIATE_KEYS as $k) {
                $this->assertStringContainsString("name=\"af[associate_concerns][$i][$k]\"", $t2);
            }
        }
        foreach (array_keys(ApplicationFormData::EXISTING_FACILITIES) as $f) {
            foreach (ApplicationFormData::FACILITY_KEYS as $k) {
                $this->assertStringContainsString("name=\"af[existing_facilities][$f][$k]\"", $t2);
            }
        }
        foreach (array_keys(ApplicationFormData::PROPOSED_FACILITIES) as $f) {
            foreach (ApplicationFormData::PROPOSED_KEYS as $k) {
                $this->assertStringContainsString("name=\"af[proposed_facilities][$f][$k]\"", $t2);
            }
        }
        for ($i = 0; $i < ApplicationFormData::MAX_MACHINERY; $i++) {
            foreach (ApplicationFormData::MACHINERY_KEYS as $k) {
                $this->assertStringContainsString("name=\"af[machinery][$i][$k]\"", $t2);
            }
        }
    }

    public function test_every_table_3_field_is_on_the_form(): void
    {
        $t3 = $this->tableSections($this->actingAs($this->admin)->get(route('admin.leads.create'))->getContent())['t3'];

        foreach (ApplicationFormCellMap::scalarFieldCells() as $field => [$sheet]) {
            if ($sheet === ApplicationFormCellMap::SHEET_3) {
                $this->assertStringContainsString('name="af[' . $field . ']"', $t3, "Table 3 is missing {$field}");
            }
        }
        foreach (array_keys(ApplicationFormData::PERIODS) as $period) {
            foreach (ApplicationFormData::PERIOD_KEYS as $k) {
                $this->assertStringContainsString("name=\"af[performance][$period][$k]\"", $t3);
            }
        }
        foreach (array_keys(ApplicationFormData::OBLIGATIONS) as $o) {
            $this->assertStringContainsString("name=\"af[statutory][$o][status]\"", $t3);
            $this->assertStringContainsString("name=\"af[statutory][$o][remarks]\"", $t3);
        }
    }

    public function test_every_red_dynamic_cell_in_the_master_has_a_form_field_mapping(): void
    {
        $zip = new ZipArchive();
        $zip->open(public_path(ApplicationFormGenerator::TEMPLATE_PATH));
        $styles = simplexml_load_string($zip->getFromName('xl/styles.xml'));
        $shared = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
        $workbook = simplexml_load_string($zip->getFromName('xl/workbook.xml'));

        $redFonts = [];
        $i = 0;
        foreach ($styles->fonts->font as $font) {
            if (isset($font->color) && (string) $font->color['rgb'] === 'FFFF0000') {
                $redFonts[$i] = true;
            }
            $i++;
        }
        $redStyles = [];
        $i = 0;
        foreach ($styles->cellXfs->xf as $xf) {
            if (isset($redFonts[(int) $xf['fontId']])) {
                $redStyles[(string) $i] = true;
            }
            $i++;
        }
        $strings = [];
        foreach ($shared->si as $si) {
            $t = (string) ($si->t ?? '');
            foreach ($si->r as $r) {
                $t .= (string) $r->t;
            }
            $strings[] = $t;
        }

        $written = ApplicationFormCellMap::writtenCells();
        $labels = ApplicationFormCellMap::STATIC_RED_LABELS;
        $checked = 0;

        foreach ($workbook->sheets->sheet as $n => $sheet) {
            $name = (string) $sheet['name'];
            $xml = simplexml_load_string($zip->getFromName('xl/worksheets/sheet' . (array_search($name, ['Table 1', 'Table 2', 'Table 3']) + 1) . '.xml'));
            foreach ($xml->sheetData->row as $row) {
                foreach ($row->c as $c) {
                    if (! isset($redStyles[(string) $c['s']]) || ! isset($c->v)) {
                        continue;
                    }
                    $text = (string) $c['t'] === 's' ? ($strings[(int) $c->v] ?? '') : (string) $c->v;
                    if (in_array(trim($text), $labels, true)) {
                        continue;
                    }
                    $checked++;
                    $this->assertContains((string) $c['r'], $written[$name], "Red cell {$name}!{$c['r']} ('{$text}') has no form field mapped to it");
                }
            }
        }
        $zip->close();

        $this->assertGreaterThan(50, $checked);
    }

    public function test_all_three_tables_render_with_the_templates_sections(): void
    {
        $this->editPage($this->admin)
            ->assertOk()
            ->assertSee('Table 1')->assertSee('Table 2')->assertSee('Table 3')
            ->assertSee('A. For Office Use')->assertSee('B. Business Information')
            ->assertSee('C. Background of Proprietor')
            ->assertSee('D. Associate Concerns')->assertSee('E. Banking / Credit Facilities Existing')
            ->assertSee('F. Credit Facilities Proposed')->assertSee('G. Working Capital')
            ->assertSee('H. Term Loan')
            ->assertSee('I. Past Performance')->assertSee('J. Status Regarding Statutory Obligations')
            ->assertSee('K. Declaration');
    }

    public function test_statutory_obligations_match_the_master_templates_wording(): void
    {
        $zip = new ZipArchive();
        $zip->open(public_path(ApplicationFormGenerator::TEMPLATE_PATH));
        $strings = $zip->getFromName('xl/sharedStrings.xml');
        $zip->close();

        foreach (ApplicationFormData::OBLIGATIONS as $label) {
            $this->assertStringContainsString(htmlspecialchars($label, ENT_XML1), $strings, "Template lacks '{$label}'");
        }
    }

    // ---- prefill & storage --------------------------------------------

    public function test_existing_lead_data_is_prefilled(): void
    {
        $data = ApplicationFormData::for($this->lead);

        $this->assertSame('Patel Traders', $data['enterprise_name']);
        $this->assertSame('Proprietary', $data['constitution']);
        $this->assertSame('Shop 4, Ring Road, Surat 395002', $data['business_address']);
        $this->assertSame('395002', $data['pincode']);
        $this->assertSame('9876501234', $data['mobile_number']);
        $this->assertSame('ravi@example.com', $data['email']);
        $this->assertSame('Wholesale grains', $data['activity_existing']);
        $this->assertSame('Ravi Patel', $data['applicant_name']);
        $this->assertSame('1985-04-12', $data['date_of_birth']);
        $this->assertSame('M', $data['sex']);
        $this->assertSame('B.Com', $data['qualification']);
        $this->assertSame('PAN', $data['id_proof_type']);
        $this->assertSame('ABCDE1234F', $data['pan_din_number']);
        $this->assertSame('Aadhaar Card', $data['address_proof_type']);
        $this->assertSame('UDYAM AADHAR [UDYAM-GJ-22-0001234]', $data['registration_no_act']);
        $this->assertSame('975000', $data['proposed_facilities']['cash_credit']['amount']);

        $this->editPage($this->admin)
            ->assertSee('value="Patel Traders"', false)
            ->assertSee('value="ravi@example.com"', false)
            ->assertSee('value="ABCDE1234F"', false);
    }

    public function test_application_specific_data_is_saved_and_remembered(): void
    {
        $this->save([
            'application_serial_no' => 'APP-77',
            'category' => 'Kishor',
            'enterprise_name' => 'Patel Traders & Co',
            'constitution' => 'Partnership',
            'commencement_date' => '2018-02-01',
            'associate_concerns' => [['name' => 'Patel Agro', 'bank' => 'SBI']],
            'existing_facilities' => ['savings' => ['bank' => 'SBI'], 'cash_credit' => ['limit' => '500000']],
            'machinery' => [['type' => 'Weighing scale', 'total_cost' => '12000']],
            'performance' => ['past_year_1' => ['net_sales' => '900000']],
            'statutory' => ['msme' => ['status' => 'Yes', 'remarks' => 'Udyam filed']],
            'declaration_date' => '2026-10-07',
        ])->assertRedirect(route('admin.leads.index'));

        $form = ApplicationForm::where('lead_id', $this->lead->id)->firstOrFail();
        $this->assertSame('APP-77', $form->application_serial_no);
        $this->assertSame('Partnership', $form->constitution);
        $this->assertSame('2018-02-01', $form->commencement_date->format('Y-m-d'));
        $this->assertSame('Patel Agro', $form->associate_concerns[0]['name']);
        $this->assertSame('Yes', $form->statutory['msme']['status']);
        $this->assertNotNull($form->created_by);

        $this->editPage($this->admin)
            ->assertSee('value="APP-77"', false)
            ->assertSee('value="Patel Agro"', false)
            ->assertSee('value="Udyam filed"', false)
            ->assertSee('value="Patel Traders &amp; Co"', false);
    }

    public function test_cleared_field_stays_blank_instead_of_snapping_back_to_the_lead_value(): void
    {
        $this->save(['enterprise_name' => 'Patel Traders', 'constitution' => 'Proprietary', 'email' => '']);

        $this->assertNull(ApplicationFormData::for($this->lead->fresh())['email']);
    }

    public function test_save_validates_choices_and_pincode(): void
    {
        $this->save(['constitution' => 'Cooperative', 'pincode' => '12'])
            ->assertSessionHasErrors(['af.constitution', 'af.pincode']);

        $this->assertDatabaseCount('application_forms', 0);
    }

    public function test_create_and_update_permissions_decide_whether_the_tab_is_saved(): void
    {
        $viewOnly = $this->userWith(['leads.edit', 'application_form.view']);
        $updater = $this->userWith(['leads.edit', 'application_form.view', 'application_form.update']);
        $creator = $this->userWith(['leads.edit', 'application_form.view', 'application_form.create']);

        $this->save(['application_serial_no' => '1'], $viewOnly);
        $this->save(['application_serial_no' => '1'], $updater); // nothing saved yet: needs create
        $this->assertDatabaseCount('application_forms', 0);

        $this->save(['application_serial_no' => '2'], $creator);
        $this->assertSame('2', $this->lead->applicationForm()->first()->application_serial_no);

        $this->save(['application_serial_no' => '3'], $creator); // saved already: needs update
        $this->assertSame('2', $this->lead->applicationForm()->first()->application_serial_no);

        $this->save(['application_serial_no' => '4'], $updater);
        $this->assertSame('4', $this->lead->applicationForm()->first()->application_serial_no);
    }

    public function test_read_only_users_see_disabled_controls_and_no_save_button(): void
    {
        $viewer = $this->userWith(['leads.edit', 'application_form.view']);

        $html = $this->editPage($viewer)->getContent();

        $this->assertStringContainsString('name="af[enterprise_name]" ', $html);
        $this->assertMatchesRegularExpression('/name="af\[enterprise_name\]"\s+disabled/', $html);
    }

    // ---- Details Sheet -> Application Form linking ---------------------

    /**
     * What the browser submits on Create: the Details Sheet values, plus the
     * Application Form fields the tab's script copied from them.
     */
    private function createPayload(array $lead = [], array $af = []): array
    {
        return array_merge([
            'name' => 'Asha Shah',
            'mobile_number' => '9811122233',
            'loan_product_id' => $this->product->id,
            'status' => 'new',
        ], $lead, ['af' => $af]);
    }

    public function test_link_mapping_is_complete_and_points_at_real_inputs_and_cells(): void
    {
        $createPage = $this->actingAs($this->admin)->get(route('admin.leads.create'))->getContent();
        $cells = ApplicationFormCellMap::scalarFieldCells();

        $this->assertNotEmpty(ApplicationFormData::LEAD_LINKS);

        foreach (ApplicationFormData::LEAD_LINKS as $path => $link) {
            // Details Sheet side: every source is a real input on the lead form.
            foreach ($link['sources'] as $source) {
                $this->assertStringContainsString('name="' . $source . '"', $createPage, "Link {$path} reads missing lead input {$source}");
            }
            // Excel side: the Application Form field lands in a cell (nested paths are table cells).
            $this->assertTrue(isset($cells[$path]) || str_starts_with($path, 'proposed_facilities.'), "Link {$path} has no Excel cell");
        }

        // The tab's script receives the same mapping.
        $this->assertStringContainsString('"path":"enterprise_name"', $createPage);
        $this->assertStringContainsString('"business_name","firm_name","name"', str_replace('\/', '/', $createPage));
    }

    public function test_details_sheet_values_become_application_form_values(): void
    {
        $lead = Lead::create([
            'loan_product_id' => $this->product->id, 'name' => 'Asha Shah', 'mobile_number' => '9811122233', 'status' => 'new',
            'business_name' => 'ABC Industries', 'email' => 'abc@gmail.com', 'pan_card' => 'ABCDE1234F',
            'business_address' => 'Surat', 'constitution_of_business' => 'Private Limited',
        ]);

        $d = ApplicationFormData::for($lead);

        $this->assertSame('ABC Industries', $d['enterprise_name']);
        $this->assertSame('9811122233', $d['mobile_number']);
        $this->assertSame('abc@gmail.com', $d['email']);
        $this->assertSame('ABCDE1234F', $d['pan_din_number']);
        $this->assertSame('Surat', $d['business_address']);
        $this->assertSame('Pvt. Ltd.', $d['constitution']);
    }

    public function test_changing_details_sheet_values_updates_linked_fields_but_not_application_only_ones(): void
    {
        $this->save(['application_serial_no' => 'APP-9', 'branch_name' => 'Manual Branch', 'enterprise_name' => 'Patel Traders', 'email' => 'ravi@example.com'], null, null, ['business_name' => 'Patel Traders', 'email' => 'ravi@example.com']);

        $before = ApplicationFormData::for($this->lead->fresh());
        $this->assertSame('Patel Traders', $before['enterprise_name']);

        // Details Sheet edit (business name + email) — via the lead form, without touching the tab.
        $this->actingAs($this->admin)->put(route('admin.leads.update', $this->lead), [
            'name' => 'Ravi Patel', 'mobile_number' => '9876501234', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Patel Traders Pvt Ltd', 'email' => 'new@example.com',
        ]);

        $after = ApplicationFormData::for($this->lead->fresh());
        $this->assertSame('Patel Traders Pvt Ltd', $after['enterprise_name'], 'linked field follows the Details Sheet');
        $this->assertSame('new@example.com', $after['email']);
        $this->assertSame('APP-9', $after['application_serial_no'], 'Application-only field is preserved');
    }

    public function test_a_hand_edited_linked_field_keeps_its_value_when_the_lead_changes(): void
    {
        $this->save(['enterprise_name' => 'Trade Name Override'], null, null, ['business_name' => 'Patel Traders']);

        $this->assertContains('enterprise_name', $this->lead->applicationForm()->first()->linked_overrides);

        $this->actingAs($this->admin)->put(route('admin.leads.update', $this->lead), [
            'name' => 'Ravi Patel', 'mobile_number' => '9876501234', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Totally New Name',
        ]);

        $this->assertSame('Trade Name Override', ApplicationFormData::for($this->lead->fresh())['enterprise_name']);
    }

    public function test_unedited_linked_fields_are_not_recorded_as_overrides(): void
    {
        // The browser copies the Details Sheet values into the tab; submitting them unchanged is not an override.
        $this->save(['enterprise_name' => 'Patel Traders', 'email' => 'ravi@example.com', 'mobile_number' => '9876501234']);

        $this->assertNotContains('enterprise_name', $this->lead->applicationForm()->first()->linked_overrides);
        $this->assertNotContains('email', $this->lead->applicationForm()->first()->linked_overrides);
    }

    // ---- Create Lead saves both together ------------------------------

    public function test_create_lead_saves_the_lead_and_application_form_together(): void
    {
        $this->actingAs($this->admin)->post(route('admin.leads.store'), $this->createPayload(
            ['business_name' => 'ABC Industries', 'email' => 'abc@gmail.com'],
            [
                'enterprise_name' => 'ABC Industries', 'email' => 'abc@gmail.com',
                'application_serial_no' => 'NEW-1', 'category' => 'Tarun',
                'existing_facilities' => ['savings' => ['bank' => 'HDFC']],
                'performance' => ['present_year' => ['net_sales' => '1200000']],
            ]
        ))->assertRedirect(route('admin.leads.index'));

        $lead = Lead::where('name', 'Asha Shah')->firstOrFail();
        $form = $lead->applicationForm;

        $this->assertNotNull($form);
        $this->assertSame('NEW-1', $form->application_serial_no);
        $this->assertSame('HDFC', $form->existing_facilities['savings']['bank']);
        $this->assertSame('1200000', $form->performance['present_year']['net_sales']);
        $this->assertNotNull($form->saved_at);

        $this->editPage($this->admin, $lead)
            ->assertSee('value="NEW-1"', false)
            ->assertSee('value="HDFC"', false)
            ->assertSee('value="ABC Industries"', false);
    }

    public function test_create_lead_works_with_a_partly_filled_or_untouched_application_form(): void
    {
        $this->actingAs($this->admin)->post(route('admin.leads.store'), $this->createPayload([], ['application_serial_no' => 'ONLY-THIS']))
            ->assertRedirect(route('admin.leads.index'))->assertSessionHasNoErrors();

        $this->actingAs($this->admin)->post(route('admin.leads.store'), $this->createPayload(['name' => 'No Tab Lead', 'mobile_number' => '9822233344'], []))
            ->assertRedirect(route('admin.leads.index'));

        $this->assertDatabaseHas('leads', ['name' => 'Asha Shah']);
        $this->assertDatabaseHas('leads', ['name' => 'No Tab Lead']);
        $this->assertDatabaseCount('application_forms', 1);
    }

    public function test_an_invalid_application_form_stops_the_lead_from_being_created(): void
    {
        $this->actingAs($this->admin)->post(route('admin.leads.store'), $this->createPayload([], ['constitution' => 'Bogus']))
            ->assertSessionHasErrors('af.constitution');

        $this->assertDatabaseMissing('leads', ['name' => 'Asha Shah']);
    }

    public function test_lead_and_application_form_are_saved_atomically(): void
    {
        $this->app->bind(ApplicationFormSaver::class, fn () => new class extends ApplicationFormSaver {
            public function save(Lead $lead, array $submitted, User $user): \App\Models\ApplicationForm
            {
                throw new \RuntimeException('Application Form storage failed');
            }
        });
        $this->withoutExceptionHandling();

        try {
            $this->actingAs($this->admin)->post(route('admin.leads.store'), $this->createPayload([], ['application_serial_no' => 'X']));
            $this->fail('The failure should have propagated');
        } catch (\RuntimeException $e) {
            $this->assertSame('Application Form storage failed', $e->getMessage());
        }

        $this->assertDatabaseMissing('leads', ['name' => 'Asha Shah']);
        $this->assertDatabaseCount('application_forms', 0);
    }

    public function test_create_ignores_the_application_form_without_permission(): void
    {
        $user = $this->userWith(['leads.create']);

        $this->actingAs($user)->post(route('admin.leads.store'), $this->createPayload([], ['application_serial_no' => 'SNEAKY']))
            ->assertRedirect(route('admin.leads.index'));

        $this->assertDatabaseHas('leads', ['name' => 'Asha Shah']);
        $this->assertDatabaseCount('application_forms', 0);
    }

    // ---- Documents tab table ------------------------------------------

    public function test_documents_tab_is_a_simple_table_with_upload_download_and_remove(): void
    {
        Storage::fake('public');
        $type = \App\Models\DocumentType::create(['name' => 'Aadhar Proof', 'code' => 'AADHAR_PROOF', 'status' => 'active', 'is_required' => false, 'sort_order' => 1]);
        Storage::disk('public')->put('lead-documents/aadhar.pdf', 'pdf-bytes');
        $doc = \App\Models\LeadDocument::create([
            'lead_id' => $this->lead->id, 'document_type_id' => $type->id, 'file_path' => 'lead-documents/aadhar.pdf',
            'original_name' => 'aadhar.pdf', 'mime_type' => 'application/pdf', 'file_size' => 9, 'uploaded_by' => $this->admin->id,
        ]);

        $html = $this->editPage($this->admin)->assertOk()->getContent();
        $this->assertStringNotContainsString('<th>File</th>', $html);
        $this->assertStringNotContainsString('>View</th>', $html);
        foreach (['Document Name', 'Upload', 'Download', 'Remove', 'Aadhar Proof', 'Delete'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringContainsString(route('admin.leads.documents.download', [$this->lead, $doc]), $html);

        // Download attaches the file; View (inline) opens it in the browser.
        $download = $this->actingAs($this->admin)->get(route('admin.leads.documents.download', [$this->lead, $doc]));
        $this->assertStringContainsString('attachment', $download->headers->get('content-disposition'));
        $view = $this->actingAs($this->admin)->get(route('admin.leads.documents.download', [$this->lead, $doc, 'inline' => 1]));
        $this->assertStringContainsString('inline', $view->headers->get('content-disposition'));

        // Without download/delete permission the links and button are not offered.
        $limited = $this->userWith(['leads.edit', 'documents.view']);
        $limitedHtml = $this->editPage($limited)->getContent();
        $this->assertStringNotContainsString('deleteLeadDocument(\'' , $limitedHtml);
    }

    // ---- generation ----------------------------------------------------

    public function test_generated_workbook_has_the_three_sheets_with_unchanged_names(): void
    {
        $this->generate()->assertRedirect(route('admin.leads.edit', $this->lead))->assertSessionHas('success');

        $this->assertSame(['Table 1', 'Table 2', 'Table 3'], array_keys($this->generatedWorkbook()));
    }

    public function test_dynamic_values_replace_the_template_values(): void
    {
        $this->save([
            'application_serial_no' => 'APP-77',
            'category' => 'Kishor',
            'branch_name' => 'Ring Road Branch',
            'enterprise_name' => 'Patel Traders & <Co>',
            'constitution' => 'Partnership',
            'business_address' => 'Shop 4, Ring Road, Surat',
            'state' => 'Gujarat',
            'pincode' => '395002',
            'business_premises' => 'Owned',
            'mobile_country_code' => '91',
            'mobile_number' => '9876501234',
            'commencement_date' => '2018-02-09',
            'is_registered' => 'Yes',
            'social_category' => 'SC',
            'minority_community' => 'Jains',
            'applicant_name' => 'Ravi Patel',
            'date_of_birth' => '1985-04-12',
            'sex' => 'M',
            'residential_address' => '22 Garden Society',
            'applicant_mobile' => '9876501234',
            'existing_facilities' => ['savings' => ['bank' => 'SBI', 'limit' => '50000', 'security' => 'N.A.']],
            'proposed_facilities' => ['cash_credit' => ['amount' => '500000', 'purpose' => 'Stock'], 'term_loan' => ['amount' => '250000']],
            'wc_actual_sales_1' => '3000000',
            'machinery' => [['type' => 'Press', 'total_cost' => '1000', 'loan_required' => '800'], ['type' => 'Lathe', 'total_cost' => '500']],
            'repayment_period' => '5 years',
            'moratorium_period' => '6 months',
            'performance' => ['past_year_2' => ['net_sales' => '100', 'net_profit' => '10'], 'next_year' => ['net_worth' => '700']],
            'statutory' => ['msme' => ['status' => 'Yes'], 'drug_license' => ['status' => 'N.A.', 'remarks' => 'Not required']],
            'declaration_date' => '2026-10-07',
            'declaration_place' => 'Surat',
        ]);
        $this->generate()->assertSessionHas('success');

        $wb = $this->generatedWorkbook();
        $t1 = $wb['Table 1'];
        $t2 = $wb['Table 2'];
        $t3 = $wb['Table 3'];

        $this->assertSame('APP-77', $t1['F7']['v']);
        $this->assertSame('Kishor', $t1['X7']['v']);
        $this->assertSame('Patel Traders & <Co>', $t1['A7']['v']);
        $this->assertSame('Patel Traders & <Co>', $t1['D9']['v']);
        $this->assertSame('Ring Road Branch', $t1['O7']['v']);
        $this->assertSame('Shop 4, Ring Road, Surat', $t1['D11']['v']);
        $this->assertSame(['3', '9', '5', '0', '0', '2'], array_map(fn ($c) => $t1[$c]['v'], ['W12', 'Y12', 'AA12', 'AC12', 'AD12', 'AF12']));
        $this->assertSame('91', $t1['R14']['v']);
        $this->assertSame('9876501234', $t1['S14']['v']);
        $this->assertSame(['0', '9', '0', '2', '2', '0', '1', '8'], array_map(fn ($c) => $t1[$c]['v'], ['M18', 'O18', 'Q18', 'S18', 'U18', 'Y18', 'AB18', 'AE18']));
        $this->assertSame('Ravi Patel', $t1['B26']['v']);
        $this->assertSame('31149', $t1['H26']['v']); // Excel serial for 12 Apr 1985
        $this->assertSame('M', $t1['K26']['v']);
        $this->assertSame('22 Garden Society Mob: 9876501234', $t1['M26']['v']);

        // The chosen option is ticked and highlighted; the others are plain.
        $this->assertSame('Partnership √', $t1['J10']['v']);
        $this->assertSame('42', $t1['J10']['s']);
        $this->assertSame(['Proprietary', '40'], [$t1['D10']['v'], $t1['D10']['s']]);
        $this->assertSame('Owned √', $t1['Y13']['v']);
        $this->assertSame('Rented', $t1['S13']['v']);
        $this->assertSame('Yes √', $t1['O19']['v']);
        $this->assertSame('No', $t1['U19']['v']);
        $this->assertSame('SC √', $t1['O22']['v']);
        $this->assertSame('OBC', $t1['T22']['v']);
        $this->assertSame('Jains √', $t1['T23']['v']);
        foreach (['C10', 'AJ11', 'R13', 'M19', 'Q19', 'M22', 'C23'] as $stray) {
            $this->assertSame('', $t1[$stray]['v'], "{$stray} should not carry the template's demo tick");
        }

        $this->assertSame('SBI', $t2['E7']['v']);
        $this->assertSame('50000', $t2['J7']['v']);
        $this->assertSame('N.A.', $t2['R7']['v']);
        $this->assertSame('500000', $t2['F16']['v']);
        $this->assertSame('Stock', $t2['I16']['v']);
        $this->assertSame('750000', $t2['F19']['v']);
        $this->assertSame('3000000', $t2['A23']['v']);
        $this->assertSame('Press', $t2['A26']['v']);
        $this->assertSame('Lathe', $t2['A27']['v']);
        $this->assertSame(['1500', 'SUM(K26:K27)'], [$t2['K28']['v'], $t2['K28']['f']]);
        $this->assertSame(['800', 'SUM(U26:U27)'], [$t2['U28']['v'], $t2['U28']['f']]);
        $this->assertSame('Repayment: 5 years, Moratorium: 6 months', $t2['O29']['v']);

        $this->assertSame('100', $t3['B4']['v']);
        $this->assertSame('10', $t3['B5']['v']);
        $this->assertSame('700', $t3['I6']['v']);
        $this->assertSame('YES', $t3['E10']['v']);
        $this->assertSame('N.A.', $t3['E11']['v']);
        $this->assertSame('Not required', $t3['H11']['v']);
        $this->assertSame('Date: 07/10/2026', $t3['A18']['v']);
        $this->assertSame('Place:Surat', $t3['A19']['v']);
    }

    public function test_red_sample_values_are_not_copied_and_missing_values_stay_blank(): void
    {
        // No data at all: every dynamic cell must come out empty.
        $wb = $this->readWorkbook((new ApplicationFormGenerator())->build(ApplicationFormData::normalize([])));

        $values = [];
        foreach ($wb as $cells) {
            foreach ($cells as $cell) {
                $values[] = $cell['v'];
            }
        }
        $everything = implode("\n", $values);
        foreach (self::SAMPLE_VALUES as $sample) {
            $this->assertStringNotContainsString($sample, $everything, "Sample value '{$sample}' leaked into the generated file");
        }

        $t1 = $wb['Table 1'];
        $t2 = $wb['Table 2'];
        $t3 = $wb['Table 3'];

        // Missing → blank (never N/A, NA, 0 or a dummy).
        foreach (['O7', 'X7', 'L12', 'S14', 'R14', 'D15', 'M16', 'H26', 'K26', 'W12', 'M18'] as $ref) {
            $this->assertSame('', $t1[$ref]['v'], "Table 1 {$ref}");
        }
        foreach (['E7', 'J7', 'M7', 'F16', 'F18', 'F19', 'A23', 'B23', 'K28', 'O28', 'U28', 'O29'] as $ref) {
            $this->assertSame('', $t2[$ref]['v'], "Table 2 {$ref}");
        }
        $this->assertNull($t2['K28']['f'], 'Empty totals must not show 0');
        foreach (['B4', 'D4', 'F4', 'I4', 'B5', 'B6', 'E9', 'E10', 'E14', 'H9'] as $ref) {
            $this->assertSame('', $t3[$ref]['v'], "Table 3 {$ref}");
        }
        $this->assertSame('Date: ', $t3['A18']['v']);
        $this->assertSame('Place:', $t3['A19']['v']);

        // No option is highlighted when nothing is chosen.
        foreach (['D10', 'J10', 'O19', 'T22', 'S13'] as $ref) {
            $this->assertNotSame('42', $t1[$ref]['s'], "{$ref} should not look selected");
            $this->assertStringNotContainsString('√', $t1[$ref]['v']);
        }

        // Static labels survive.
        $this->assertSame('State', $t1['D12']['v']);
        $this->assertSame('PIN Code', $t1['R12']['v']);
        $this->assertSame('Business Premises', $t1['D13']['v']);
        $this->assertSame('Mobile No.', $t1['L14']['v']);
    }

    public function test_no_red_font_cell_keeps_template_content_apart_from_static_labels(): void
    {
        $binary = (new ApplicationFormGenerator())->build(ApplicationFormData::normalize([]));

        $tmp = tempnam(sys_get_temp_dir(), 'afs');
        file_put_contents($tmp, $binary);
        $zip = new ZipArchive();
        $zip->open($tmp);
        $styles = simplexml_load_string($zip->getFromName('xl/styles.xml'));
        $zip->close();
        @unlink($tmp);

        $redFonts = [];
        $idx = 0;
        foreach ($styles->fonts->font as $font) {
            if (isset($font->color) && (string) $font->color['rgb'] === 'FFFF0000') {
                $redFonts[$idx] = true;
            }
            $idx++;
        }
        $redStyles = [];
        $idx = 0;
        foreach ($styles->cellXfs->xf as $xf) {
            if (isset($redFonts[(int) $xf['fontId']])) {
                $redStyles[(string) $idx] = true;
            }
            $idx++;
        }

        $wb = $this->readWorkbook($binary);
        $allowed = ['State', 'PIN Code', 'Business Premises', 'Mobile No.', 'Rented', 'Owned']; // static labels (Rented/Owned are option labels)
        $checked = 0;
        foreach ($wb as $sheet => $cells) {
            foreach ($cells as $ref => $cell) {
                if ($cell['s'] !== null && isset($redStyles[$cell['s']]) && $cell['v'] !== '') {
                    $checked++;
                    $this->assertContains($cell['v'], $allowed, "{$sheet}!{$ref} still holds '{$cell['v']}'");
                }
            }
        }
        $this->assertGreaterThan(0, $checked);
    }

    public function test_static_template_text_and_structure_are_preserved(): void
    {
        $this->generate();

        $tmp = tempnam(sys_get_temp_dir(), 'afs');
        file_put_contents($tmp, Storage::disk('local')->get(ApplicationFormGenerator::relativePath($this->lead)));
        $generated = new ZipArchive();
        $generated->open($tmp);
        $master = new ZipArchive();
        $master->open(public_path(ApplicationFormGenerator::TEMPLATE_PATH));

        // Styles, strings (incl. the declaration wording), theme, images, page setup are byte-identical.
        foreach (['xl/styles.xml', 'xl/sharedStrings.xml', 'xl/theme/theme1.xml', 'xl/drawings/drawing1.xml', 'xl/media/image1.jpeg', 'xl/media/image2.jpeg', 'xl/printerSettings/printerSettings1.bin'] as $part) {
            $this->assertSame($master->getFromName($part), $generated->getFromName($part), "{$part} changed");
        }

        // Merged cells and print setup per sheet are unchanged.
        foreach ([1, 2, 3] as $n) {
            $m = $master->getFromName("xl/worksheets/sheet{$n}.xml");
            $g = $generated->getFromName("xl/worksheets/sheet{$n}.xml");
            foreach (['mergeCells', 'pageSetup', 'pageMargins', 'cols', 'drawing'] as $tag) {
                preg_match("/<{$tag}\\b.*?(<\\/{$tag}>|\\/>)/s", $m, $mm);
                preg_match("/<{$tag}\\b.*?(<\\/{$tag}>|\\/>)/s", $g, $gm);
                $this->assertSame($mm[0] ?? null, $gm[0] ?? null, "sheet{$n} {$tag} changed");
            }
        }

        $generated->close();
        $master->close();
        @unlink($tmp);

        $wb = $this->generatedWorkbook();
        $this->assertSame('Name of the Bank:STATE BANK OF INDIA', $wb['Table 1']['A3']['v']);
        $this->assertStringStartsWith('K. Declaration:', $wb['Table 3']['A15']['v']);
        $this->assertSame('Space for Photo', $wb['Table 3']['A16']['v']);
    }

    public function test_master_template_is_never_modified(): void
    {
        $before = md5_file(public_path(ApplicationFormGenerator::TEMPLATE_PATH));
        $mtime = filemtime(public_path(ApplicationFormGenerator::TEMPLATE_PATH));

        $this->generate();
        $this->generate();

        $this->assertSame($before, md5_file(public_path(ApplicationFormGenerator::TEMPLATE_PATH)));
        $this->assertSame($mtime, filemtime(public_path(ApplicationFormGenerator::TEMPLATE_PATH)));
    }

    public function test_generation_requires_the_core_fields_but_not_every_field(): void
    {
        $this->save(['enterprise_name' => '', 'constitution' => '', 'business_address' => '', 'applicant_name' => '']);

        $this->generate()
            ->assertRedirect(route('admin.leads.edit', $this->lead))
            ->assertSessionHasErrors('af_generate');

        Storage::disk('local')->assertMissing(ApplicationFormGenerator::relativePath($this->lead));

        // Only the four required fields — everything else stays empty — is enough.
        $this->save(['enterprise_name' => 'Patel Traders', 'constitution' => 'Proprietary', 'business_address' => 'Surat', 'applicant_name' => 'Ravi Patel']);
        $this->generate()->assertSessionHas('success');
        Storage::disk('local')->assertExists(ApplicationFormGenerator::relativePath($this->lead));
    }

    public function test_generating_does_not_turn_lead_defaults_into_a_blank_saved_form(): void
    {
        $this->generate()->assertSessionHas('success');

        $this->assertNull($this->lead->applicationForm()->first()->saved_at);
        $this->assertSame('Patel Traders', ApplicationFormData::for($this->lead->fresh())['enterprise_name']);
        $this->assertSame('Patel Traders', $this->generatedWorkbook()['Table 1']['D9']['v']);
    }

    public function test_regeneration_replaces_the_file_and_keeps_the_latest(): void
    {
        $this->generate();
        $this->save(['enterprise_name' => 'Renamed Traders', 'constitution' => 'Proprietary', 'business_address' => 'Surat', 'applicant_name' => 'Ravi Patel']);
        $this->generate();

        $this->assertSame('Renamed Traders', $this->generatedWorkbook()['Table 1']['D9']['v']);
        $this->assertSame([ApplicationFormGenerator::relativePath($this->lead)], Storage::disk('local')->allFiles('application_form/' . $this->lead->id));
    }

    // ---- permissions on generate / download ---------------------------

    public function test_generate_is_forbidden_without_permission_and_its_button_is_hidden(): void
    {
        $user = $this->userWith(['leads.edit', 'application_form.view', 'application_form.download']);

        $this->generate($user)->assertForbidden();
        Storage::disk('local')->assertMissing(ApplicationFormGenerator::relativePath($this->lead));

        $this->editPage($user)->assertDontSee('Generate Application Form');
        $this->editPage($this->userWith(['leads.edit', 'application_form.view', 'application_form.generate']))->assertSee('Generate Application Form');
    }

    public function test_download_requires_permission(): void
    {
        $this->generate();
        $generator = $this->userWith(['leads.edit', 'application_form.view', 'application_form.generate']);
        $downloader = $this->userWith(['leads.edit', 'application_form.view', 'application_form.download']);

        $this->actingAs($generator)->get(route('admin.leads.application-form.download', $this->lead))->assertForbidden();
        $this->editPage($generator)->assertDontSee('Download Application Form');

        $this->editPage($downloader)->assertSee('Download Application Form');
        $response = $this->actingAs($downloader)->get(route('admin.leads.application-form.download', $this->lead));
        $response->assertOk();
        $this->assertStringContainsString('Application_Form_' . $this->lead->id . '.xlsx', $response->headers->get('content-disposition'));

        $sheets = $this->readWorkbook($response->streamedContent());
        $this->assertSame(['Table 1', 'Table 2', 'Table 3'], array_keys($sheets));
    }

    public function test_download_is_a_404_until_the_form_has_been_generated(): void
    {
        $this->actingAs($this->admin)->get(route('admin.leads.application-form.download', $this->lead))->assertNotFound();
    }

    public function test_a_lead_cannot_download_another_leads_application_form(): void
    {
        $other = Lead::create(['name' => 'Other Lead', 'mobile_number' => '9000000000', 'status' => 'new']);
        $this->generate();

        // Lead B has no file of its own; Lead A's file is not reachable through Lead B's URL.
        $this->actingAs($this->admin)->get(route('admin.leads.application-form.download', $other))->assertNotFound();

        $this->save(['enterprise_name' => 'Other Lead', 'constitution' => 'Proprietary', 'business_address' => 'Pune', 'applicant_name' => 'Other Person'], null, $other);
        $this->generate(null, $other)->assertSessionHas('success');
        $mine = $this->actingAs($this->admin)->get(route('admin.leads.application-form.download', $this->lead))->streamedContent();
        $theirs = $this->actingAs($this->admin)->get(route('admin.leads.application-form.download', $other))->streamedContent();

        $this->assertSame('Patel Traders', $this->readWorkbook($mine)['Table 1']['D9']['v']);
        $this->assertSame('Other Lead', $this->readWorkbook($theirs)['Table 1']['D9']['v']);

        // A tampered stored path must not redirect the download to someone else's file.
        $other->applicationForm()->update(['generated_path' => ApplicationFormGenerator::relativePath($this->lead)]);
        $this->actingAs($this->admin)->get(route('admin.leads.application-form.download', $other))->assertNotFound();
    }

    public function test_generated_files_are_not_publicly_reachable(): void
    {
        $this->generate();

        Storage::disk('public')->assertMissing(ApplicationFormGenerator::relativePath($this->lead));
    }
}
