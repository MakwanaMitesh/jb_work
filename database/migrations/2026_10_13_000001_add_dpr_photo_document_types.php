<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The DPR's machinery and final-product photos are ordinary Lead Documents
     * (same upload, download and delete), so they need document types. Existing
     * rows are left alone so admin edits to them survive.
     */
    public function up(): void
    {
        $types = [
            ['name' => 'DPR - Machinery Photos', 'code' => 'machinery_photos', 'description' => 'Photographs of the new machinery, placed in the DPR.', 'sort_order' => 18],
            ['name' => 'DPR - Final Product Photos', 'code' => 'final_product_photos', 'description' => 'Photographs of the final products, placed in the DPR.', 'sort_order' => 19],
        ];

        foreach ($types as $type) {
            DB::table('document_types')->insertOrIgnore($type + [
                'is_required' => false,
                'has_front_back' => false,
                'allow_multiple' => true,
                'allowed_file_types' => json_encode(['jpg', 'jpeg', 'png']),
                'max_file_size_kb' => 10240,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('document_types')->whereIn('code', ['machinery_photos', 'final_product_photos'])->delete();
    }
};
