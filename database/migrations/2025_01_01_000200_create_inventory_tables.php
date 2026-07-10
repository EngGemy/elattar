<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * المخزون: دفتر حركات غير قابل للتعديل (Append-only Ledger)
 * القاعدة الحديدية: لا يُحذف سطر ولا يُعدَّل. الإلغاء = حركة عكسية.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── المخازن / الفروع
        Schema::create('warehouses', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();                 // MAIN / BRANCH-01
            $t->string('name');                           // "المخزن الرئيسي"
            $t->enum('type', ['store', 'branch', 'vehicle'])->default('store');
            $t->string('address')->nullable();
            $t->string('phone')->nullable();
            $t->boolean('is_default')->default(false);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        // ── أرصدة المخزون (Materialized — تُحدَّث داخل Transaction مقفولة)
        Schema::create('stock_levels', function (Blueprint $t) {
            $t->id();
            $t->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $t->foreignId('warehouse_id')->constrained()->cascadeOnDelete();

            $t->decimal('on_hand', 12, 3)->default(0);        // الموجود فعليًا
            $t->decimal('reserved', 12, 3)->default(0);       // محجوز لطلبات لم تُصرف
            $t->decimal('reorder_point', 12, 3)->default(0);  // حد إعادة الطلب
            $t->decimal('reorder_qty', 12, 3)->default(0);    // الكمية المقترح شراؤها

            $t->timestamp('last_movement_at')->nullable();    // لتقرير المخزون الراكد
            $t->timestamps();

            $t->unique(['variant_id', 'warehouse_id'], 'ux_stock_variant_wh');
            $t->index(['warehouse_id', 'on_hand']);
        });

        // available = on_hand - reserved  (عمود محسوب — لا يُخزَّن)
        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'])) {
            \DB::statement('ALTER TABLE stock_levels ADD COLUMN available DECIMAL(12,3)
                            AS (on_hand - reserved) STORED');
            \DB::statement('CREATE INDEX ix_stock_available ON stock_levels(available)');
        } elseif ($driver === 'pgsql') {
            \DB::statement('ALTER TABLE stock_levels ADD COLUMN available DECIMAL(12,3)
                            GENERATED ALWAYS AS (on_hand - reserved) STORED');
            \DB::statement('CREATE INDEX ix_stock_available ON stock_levels(available)');
        }

        // ── دفتر الحركات — Immutable. لا timestamps() ولا softDeletes.
        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('variant_id')->constrained('product_variants')->restrictOnDelete();
            $t->foreignId('warehouse_id')->constrained()->restrictOnDelete();

            $t->enum('type', [
                'purchase',      // استلام من مورد
                'sale',          // بيع
                'return',        // مرتجع من عميل
                'transfer_in',   // تحويل وارد
                'transfer_out',  // تحويل صادر
                'adjustment',    // تسوية جرد
            ]);

            // موجب = دخول، سالب = خروج. لا يساوي صفرًا أبدًا.
            $t->decimal('qty_delta', 12, 3);
            $t->decimal('balance_after', 12, 3);          // الرصيد الجاري للتدقيق
            $t->bigInteger('unit_cost_minor')->default(0); // لتقييم المخزون

            $t->string('reason_code')->nullable();        // damaged / count / customer_return
            $t->text('note')->nullable();

            // مرجع متعدد الأشكال: Order / PurchaseOrder / StockAdjustment / StockTransfer
            $t->nullableMorphs('reference');

            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamp('created_at')->useCurrent();

            $t->index(['variant_id', 'warehouse_id', 'created_at'], 'ix_sm_variant_wh_date');
            $t->index(['type', 'created_at']);
        });

        // ── حجوزات المخزون (مع مهلة انتهاء للسلال المتروكة)
        Schema::create('stock_reservations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $t->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $t->decimal('qty', 12, 3);

            $t->nullableMorphs('reference');              // Order / Cart
            $t->enum('status', ['active', 'released', 'fulfilled'])->default('active');
            $t->timestamp('expires_at')->nullable();      // TTL — Job يفكّ الحجز
            $t->timestamps();

            $t->index(['status', 'expires_at']);
        });

        // ── تسويات الجرد (رأس المستند)
        Schema::create('stock_adjustments', function (Blueprint $t) {
            $t->id();
            $t->string('number')->unique();               // ADJ-2025-0001
            $t->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $t->enum('reason', ['damaged', 'expired', 'count', 'theft', 'other']);
            $t->text('note')->nullable();
            $t->enum('status', ['draft', 'approved'])->default('draft');
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->timestamps();
        });

        Schema::create('stock_adjustment_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('stock_adjustment_id')->constrained()->cascadeOnDelete();
            $t->foreignId('variant_id')->constrained('product_variants')->restrictOnDelete();
            $t->decimal('qty_before', 12, 3);             // الرصيد الدفتري
            $t->decimal('qty_counted', 12, 3);            // الرصيد الفعلي
            $t->decimal('qty_delta', 12, 3);              // الفرق
        });

        // ── التحويل بين المخازن
        Schema::create('stock_transfers', function (Blueprint $t) {
            $t->id();
            $t->string('number')->unique();               // TRF-2025-0001
            $t->foreignId('from_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $t->foreignId('to_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $t->enum('status', ['draft', 'in_transit', 'received', 'cancelled'])->default('draft');
            $t->text('note')->nullable();
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->timestamp('shipped_at')->nullable();
            $t->timestamp('received_at')->nullable();
            $t->timestamps();
        });

        Schema::create('stock_transfer_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('stock_transfer_id')->constrained()->cascadeOnDelete();
            $t->foreignId('variant_id')->constrained('product_variants')->restrictOnDelete();
            $t->decimal('qty', 12, 3);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_lines');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_adjustment_lines');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_levels');
        Schema::dropIfExists('warehouses');
    }
};
