<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** عطارة: البيع بالجرام/المل من 1 — لا مضاعفات 50 جم إجبارية */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('product_variants')
            ->whereIn('unit', ['gram', 'ml'])
            ->where('step', '>=', 50)
            ->update(['step' => 1]);
    }

    public function down(): void
    {
        // لا rollback — step=1 صحيح للعطارة
    }
};
