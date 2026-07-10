<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جداول الكتالوج: التصنيفات (شجرة متداخلة) + المنتجات + المتغيّرات + الخصائص
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── التصنيفات: Nested Set (شجرة كاملة باستعلام واحد)
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();

            // أعمدة Nested Set — تُدار بواسطة kalnoy/nestedset
            $t->unsignedInteger('_lft')->default(0);
            $t->unsignedInteger('_rgt')->default(0);
            $t->unsignedInteger('depth')->default(0);

            $t->string('name');                       // الاسم بالعربية
            $t->string('slug')->unique();             // للـ URL
            $t->string('icon')->nullable();           // اسم أيقونة Heroicon
            $t->string('image_path')->nullable();     // صورة التصنيف
            $t->text('description')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_featured')->default(false);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->softDeletes();

            $t->index(['_lft', '_rgt']);
            $t->index(['is_active', 'sort_order']);
        });

        // ── فئات الضريبة (VAT: 14% مصر / 15% السعودية)
        Schema::create('tax_classes', function (Blueprint $t) {
            $t->id();
            $t->string('name');                       // "قياسي" / "معفى"
            $t->string('code')->unique();
            $t->decimal('rate', 6, 4)->default(0);    // 0.1400 = 14%
            $t->boolean('is_inclusive')->default(true); // السعر شامل الضريبة؟
            $t->boolean('is_default')->default(false);
            $t->timestamps();
        });

        // ── الخصائص (لون / وزن العبوة / درجة الطحن …)
        Schema::create('attributes', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();             // grind_level
            $t->string('name');                       // درجة الطحن
            $t->enum('type', ['select', 'weight', 'text'])->default('select');
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('attribute_values', function (Blueprint $t) {
            $t->id();
            $t->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $t->string('value');                      // "ناعم" / "خشن"
            $t->string('slug');
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();

            $t->unique(['attribute_id', 'slug']);
        });

        // ── المنتجات
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained()->restrictOnDelete();
            $t->foreignId('tax_class_id')->nullable()->constrained()->nullOnDelete();

            $t->string('sku_root')->unique();         // SPICE-BLK-PEP
            $t->string('name');
            $t->string('slug')->unique();

            // simple  = قطعة واحدة بلا متغيّرات
            // variable= له متغيّرات (لون/حجم)
            // weighted= يُباع بالوزن (بهارات، أعشاب، أرز…)
            $t->enum('type', ['simple', 'variable', 'weighted'])->default('simple');

            $t->text('short_description')->nullable();  // وصف مختصر
            $t->longText('long_description')->nullable(); // وصف تفصيلي (RichEditor)

            $t->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $t->boolean('has_variants')->default(false);
            $t->boolean('is_featured')->default(false);
            $t->unsignedInteger('sort_order')->default(0);

            $t->timestamps();
            $t->softDeletes();

            $t->index(['status', 'category_id']);
            $t->index('is_featured');
        });

        // ── المنتجات المرتبطة (Related / Cross-sell)
        Schema::create('product_relations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();
            $t->enum('relation_type', ['related', 'cross_sell', 'up_sell'])->default('related');
            $t->unsignedInteger('sort_order')->default(0);

            $t->unique(['product_id', 'related_product_id', 'relation_type'], 'ux_prod_rel');
        });

        // ── المتغيّرات: وحدة التعامل الوحيدة في المخزون والطلبات و POS
        Schema::create('product_variants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();

            $t->string('sku')->unique();
            $t->string('barcode')->nullable();

            // كل المبالغ بالقروش (bigint) — صفر أخطاء عشرية
            $t->bigInteger('price_minor');                       // سعر البيع لوحدة القياس
            $t->bigInteger('compare_at_price_minor')->nullable(); // السعر قبل الخصم
            $t->bigInteger('cost_minor')->default(0);            // متوسط التكلفة المتحرك

            // وحدة القياس + أقل كمية بيع
            $t->enum('unit', ['piece', 'gram', 'kg', 'liter', 'ml'])->default('piece');
            $t->decimal('step', 12, 3)->default(1);              // 50 جرام مثلاً
            $t->string('unit_label')->nullable();                // "كيس" / "برطمان 500جم"

            $t->unsignedInteger('weight_grams')->nullable();     // لحساب الشحن
            $t->json('dimensions')->nullable();                  // {l,w,h} سم

            $t->boolean('is_default')->default(false);
            $t->boolean('is_active')->default(true);

            $t->timestamps();
            $t->softDeletes();

            $t->index(['product_id', 'is_active']);
        });

        // فهرس فريد جزئي للباركود (يتجاهل NULL) — PostgreSQL/MySQL 8
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            \DB::statement('CREATE UNIQUE INDEX ux_variant_barcode ON product_variants(barcode) WHERE barcode IS NOT NULL');
        } else {
            Schema::table('product_variants', fn (Blueprint $t) => $t->unique('barcode', 'ux_variant_barcode'));
        }

        // ── ربط المتغيّر بقيم الخصائص
        Schema::create('variant_attribute_values', function (Blueprint $t) {
            $t->id();
            $t->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $t->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $t->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();

            $t->unique(['variant_id', 'attribute_id'], 'ux_variant_attr');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_attribute_values');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_relations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('tax_classes');
        Schema::dropIfExists('categories');
    }
};
