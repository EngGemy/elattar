<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Catalog\Models\TaxClass;
use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Pos\Models\Register;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * البنية التحتية للمتجر: أدوار، مستخدمون، ضريبة، مخزن، كاشير.
 * الكتالوج الكامل ينتقل إلى {@see ExpertCatalogSeeder}.
 */
class AttarSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'warehouse_manager', 'cashier', 'support'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@attar.test'],
            ['name' => 'مدير النظام', 'password' => Hash::make('password')]
        );
        $admin->assignRole('admin');

        $cashier = User::firstOrCreate(
            ['email' => 'cashier@attar.test'],
            ['name' => 'كاشير المحل', 'password' => Hash::make('password')]
        );
        $cashier->assignRole('cashier');

        TaxClass::firstOrCreate(
            ['code' => 'VAT_STD'],
            ['name' => 'قياسي 14%', 'rate' => 0.14, 'is_inclusive' => true, 'is_default' => true]
        );

        TaxClass::firstOrCreate(
            ['code' => 'VAT_EXEMPT'],
            ['name' => 'معفى', 'rate' => 0, 'is_inclusive' => true, 'is_default' => false]
        );

        $warehouse = Warehouse::firstOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'المخزن الرئيسي', 'type' => 'store', 'is_default' => true, 'is_active' => true]
        );

        Register::firstOrCreate(
            ['code' => 'POS-01'],
            ['warehouse_id' => $warehouse->id, 'name' => 'كاشير المحل', 'is_active' => true]
        );

        $this->command?->info('AttarSeeder: الأدوار والمخزن والكاشير جاهزة. الكتالوج ← ExpertCatalogSeeder.');
    }
}
