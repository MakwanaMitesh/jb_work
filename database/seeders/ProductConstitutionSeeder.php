<?php

namespace Database\Seeders;

use App\Models\LoanProduct;
use App\Models\CustomerConstitution;
use App\Models\LoanProductConstitution;
use Illuminate\Database\Seeder;

class ProductConstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Home Loan', 'code' => 'HL', 'description' => 'Home purchase, extension, or renovation loan.', 'sort_order' => 1],
            ['name' => 'Business Loan', 'code' => 'BL', 'description' => 'Unsecured or secured loan for business operations.', 'sort_order' => 2],
            ['name' => 'Project Loan', 'code' => 'Project Loan', 'code_actual' => 'PROJECT', 'description' => 'Funding for new projects, expansion, or manufacturing setups.', 'sort_order' => 3],
            ['name' => 'Cash Credit / Overdraft', 'code' => 'CC/OD', 'description' => 'Working capital overdraft facility.', 'sort_order' => 4],
            ['name' => 'Term Loan', 'code' => 'TL', 'description' => 'Long term asset backing loan.', 'sort_order' => 5],
            ['name' => 'Subsidy Loan', 'code' => 'Subsidy', 'code_actual' => 'SUBSIDY', 'description' => 'Government scheme supported subsidy loans.', 'sort_order' => 6],
        ];

        $productInstances = [];
        foreach ($products as $p) {
            $code = $p['code_actual'] ?? $p['code'];
            $productInstances[$code] = LoanProduct::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $p['name'],
                    'description' => $p['description'],
                    'sort_order' => $p['sort_order'],
                    'status' => 'active',
                ]
            );
        }

        $constitutions = [
            ['name' => 'Individual', 'code' => 'INDIVIDUAL', 'description' => 'Salaried or self-employed individuals.', 'sort_order' => 1],
            ['name' => 'Proprietorship', 'code' => 'PROPRIETORSHIP', 'description' => 'Sole proprietorship firms.', 'sort_order' => 2],
            ['name' => 'Partnership', 'code' => 'PARTNERSHIP', 'description' => 'Partnership firms or LLPs.', 'sort_order' => 3],
            ['name' => 'Private Limited', 'code' => 'PVT_LTD', 'description' => 'Private limited companies.', 'sort_order' => 4],
        ];

        $constitutionInstances = [];
        foreach ($constitutions as $c) {
            $constitutionInstances[$c['code']] = CustomerConstitution::firstOrCreate(
                ['code' => $c['code']],
                [
                    'name' => $c['name'],
                    'description' => $c['description'],
                    'sort_order' => $c['sort_order'],
                    'status' => 'active',
                ]
            );
        }

        // Enable common mappings
        $mappings = [
            'HL' => ['INDIVIDUAL', 'PROPRIETORSHIP'],
            'BL' => ['PROPRIETORSHIP', 'PARTNERSHIP', 'PVT_LTD'],
            'PROJECT' => ['PARTNERSHIP', 'PVT_LTD'],
            'CC/OD' => ['PROPRIETORSHIP', 'PARTNERSHIP', 'PVT_LTD'],
            'TL' => ['PROPRIETORSHIP', 'PARTNERSHIP', 'PVT_LTD'],
            'SUBSIDY' => ['PROPRIETORSHIP', 'PARTNERSHIP'],
        ];

        foreach ($mappings as $prodCode => $constCodes) {
            $product = $productInstances[$prodCode] ?? null;
            if (!$product) continue;

            foreach ($constCodes as $constCode) {
                $constitution = $constitutionInstances[$constCode] ?? null;
                if (!$constitution) continue;

                LoanProductConstitution::firstOrCreate([
                    'loan_product_id' => $product->id,
                    'constitution_id' => $constitution->id,
                ], [
                    'status' => 'active'
                ]);
            }
        }
    }
}
