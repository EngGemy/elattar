<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Models\TaxClass;
use App\Domain\Inventory\Models\StockLevel;
use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Pos\Models\Register;
use App\Domain\Shared\Enums\ProductStatus;
use App\Domain\Shared\Enums\ProductType;
use App\Domain\Shared\Enums\UnitOfMeasure;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * تحميل بيانات متجر «عبد القادر العطّار» الفعلية — 26 منتجًا، 5 تصنيفات.
 *
 * منطق التحويل من الواجهة القديمة:
 *   sell:"weight" ⟵ السعر بالكيلو، الوحدة جرام، الخطوة 50 جم
 *   sell:"piece"  ⟵ السعر بالقطعة، الوحدة قطعة، الخطوة 1
 */
class AttarSeeder extends Seeder
{
    public function run(): void
    {
        // ═══ الأدوار والصلاحيات ═══
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

        // ═══ فئات الضريبة ═══
        $vat = TaxClass::firstOrCreate(
            ['code' => 'VAT_STD'],
            ['name' => 'قياسي 14%', 'rate' => 0.14, 'is_inclusive' => true, 'is_default' => true]
        );

        TaxClass::firstOrCreate(
            ['code' => 'VAT_EXEMPT'],
            ['name' => 'معفى', 'rate' => 0, 'is_inclusive' => true, 'is_default' => false]
        );

        // ═══ المخزن ونقطة البيع ═══
        $warehouse = Warehouse::firstOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'المخزن الرئيسي', 'type' => 'store', 'is_default' => true, 'is_active' => true]
        );

        Register::firstOrCreate(
            ['code' => 'POS-01'],
            ['warehouse_id' => $warehouse->id, 'name' => 'كاشير المحل', 'is_active' => true]
        );

        // ═══ التصنيفات ═══
        $catNames = [
            'بهارات'       => 'heroicon-o-fire',
            'أعشاب'        => 'heroicon-o-sparkles',
            'زيوت وسمن'    => 'heroicon-o-beaker',
            'سكر ومحليات'  => 'heroicon-o-cake',
            'بقالة'        => 'heroicon-o-shopping-bag',
        ];

        $categories = [];
        $i = 0;
        foreach ($catNames as $name => $icon) {
            $categories[$name] = Category::firstOrCreate(
                ['slug' => \Str::slug($name, '-', 'ar')],
                ['name' => $name, 'icon' => $icon, 'sort_order' => $i++, 'is_active' => true, 'is_featured' => true]
            );
        }

        // ═══ المنتجات — البيانات الفعلية من الواجهة ═══
        // [الاسم، التصنيف، طريقة البيع، السعر بالجنيه، الوصف، وحدة القطعة]
        $products = [
            // ── بهارات (السعر بالكيلو)
            ['فلفل أسود',       'بهارات',      'weight', 320,  'حبّ فلفل أسود فاخر يُطحن طازجًا.',      null],
            ['كمّون',           'بهارات',      'weight', 180,  'كمّون أصفر عالي الجودة.',               null],
            ['كركم',            'بهارات',      'weight', 150,  'كركم ذهبي نقي، لون وطعم أصيل.',        null],
            ['قرفة',            'بهارات',      'weight', 260,  'عيدان قرفة عطرة للطبخ والمشروبات.',    null],
            ['هيل (حبهان)',     'بهارات',      'weight', 1100, 'حبهان أخضر فاخر للقهوة العربية.',      null],
            ['قرنفل',           'بهارات',      'weight', 420,  'قرنفل كامل عبق للطبخ والمشروبات.',     null],
            ['شطة حمراء',       'بهارات',      'weight', 200,  'فلفل أحمر حار مطحون.',                 null],
            ['بهارات مشكّلة',    'بهارات',      'weight', 280,  'خلطة العطّار لجميع اللحوم.',            null],

            // ── أعشاب
            ['زنجبيل مجفف',     'أعشاب',       'weight', 240,  'جذور زنجبيل مجففة للمناعة.',           null],
            ['بابونج',          'أعشاب',       'weight', 280,  'زهر بابونج مهدّئ.',                    null],
            ['نعناع مجفف',      'أعشاب',       'weight', 160,  'أوراق نعناع منعشة ومهضّمة.',            null],
            ['كركديه',          'أعشاب',       'weight', 170,  'زهر كركديه أحمر.',                     null],
            ['يانسون',          'أعشاب',       'weight', 190,  'بذور يانسون مهدّئة للمعدة.',            null],

            // ── زيوت وسمن
            ['زيت عباد شمس',    'زيوت وسمن',   'piece',  75,   'زيت طهي نقي للقلي والطبخ.',            'لتر'],
            ['زيت ذرة',         'زيوت وسمن',   'piece',  85,   'زيت ذرة صافٍ خفيف.',                   'لتر'],
            ['زيت زيتون',       'زيوت وسمن',   'piece',  320,  'زيت زيتون بكر ممتاز.',                 'عبوة 750مل'],
            ['سمن بلدي',        'زيوت وسمن',   'weight', 420,  'سمن طبيعي بطعم غني.',                  null],

            // ── سكر ومحليات
            ['سكر أبيض',        'سكر ومحليات', 'weight', 38,   'سكر ناعم مكرر.',                       null],
            ['عسل نحل',         'سكر ومحليات', 'piece',  180,  'عسل نحل طبيعي صافٍ.',                  'برطمان 500جم'],
            ['عسل أسود',        'سكر ومحليات', 'piece',  60,   'عسل أسود بلدي غني بالحديد.',           'عبوة'],

            // ── بقالة
            ['أرز مصري',        'بقالة',       'weight', 32,   'أرز أبيض حبة قصيرة عالي الجودة.',      null],
            ['مكرونة',          'بقالة',       'piece',  22,   'مكرونة قمح ممتازة.',                   'كيس'],
            ['دقيق فاخر',       'بقالة',       'weight', 28,   'دقيق أبيض للخبز والمعجنات.',           null],
            ['عدس أصفر',        'بقالة',       'weight', 55,   'عدس أصفر مقشور نظيف.',                 null],
            ['فول مدمس',        'بقالة',       'weight', 48,   'فول حب مختار للتدميس.',                null],
            ['شاي أسود',        'بقالة',       'weight', 220,  'شاي أسود ثقيل معتّق.',                 null],
        ];

        // هامش ربح تقديري 30% ⟵ التكلفة = السعر × 0.70
        $costRatio = 0.70;

        foreach ($products as $idx => [$name, $catName, $sell, $priceEgp, $desc, $unitLabel]) {
            $isWeighted = $sell === 'weight';

            $product = Product::firstOrCreate(
                ['sku_root' => 'ATR-' . str_pad((string) ($idx + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'category_id'       => $categories[$catName]->id,
                    'tax_class_id'      => $vat->id,
                    'name'              => $name,
                    'slug'              => \Str::slug($name, '-', 'ar') . '-' . ($idx + 1),
                    'type'              => $isWeighted ? ProductType::Weighted : ProductType::Simple,
                    'short_description' => $desc,
                    'status'            => ProductStatus::Active,
                    'has_variants'      => false,
                    'is_featured'       => $idx < 6,
                    'sort_order'        => $idx,
                ]
            );

            // كل منتج بسيط له متغيّر افتراضي واحد — يوحّد التعامل مع المخزون
            $variant = ProductVariant::firstOrCreate(
                ['sku' => $product->sku_root . '-D'],
                [
                    'product_id'  => $product->id,
                    'barcode'     => '622' . str_pad((string) (100000 + $idx), 10, '0', STR_PAD_LEFT),
                    'price_minor' => (int) round($priceEgp * 100),
                    'cost_minor'  => (int) round($priceEgp * $costRatio * 100),
                    'unit'        => $isWeighted ? UnitOfMeasure::Gram : UnitOfMeasure::Piece,
                    // البهارات والأعشاب: من جرام واحد (step=1)
                    'step'        => $isWeighted ? '1.000' : '1.000',
                    'unit_label'  => $unitLabel,
                    'weight_grams' => $isWeighted ? null : 1000,
                    'is_default'  => true,
                    'is_active'   => true,
                ]
            );

            // رصيد افتتاحي: 50 كجم للأوزان، 100 قطعة للقطع
            StockLevel::firstOrCreate(
                ['variant_id' => $variant->id, 'warehouse_id' => $warehouse->id],
                [
                    'on_hand'          => $isWeighted ? 50000 : 100,   // 50000 جم = 50 كجم
                    'reserved'         => 0,
                    'reorder_point'    => $isWeighted ? 5000 : 10,     // 5 كجم أو 10 قطع
                    'reorder_qty'      => $isWeighted ? 25000 : 50,
                    'last_movement_at' => now(),
                ]
            );
        }

        $this->command->info('تم تحميل ' . count($products) . ' منتجًا في ' . count($categories) . ' تصنيفات.');
    }
}
