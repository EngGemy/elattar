<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * نقطة البيع + المشتريات + جداول التقارير المُجمَّعة
 */
return new class extends Migration
{
    public function up(): void
    {
        // ═══ نقطة البيع (POS) ═══

        Schema::create('registers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $t->string('code')->unique();                  // POS-01
            $t->string('name');                            // "كاشير المحل"
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('register_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('register_id')->constrained()->restrictOnDelete();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();

            $t->timestamp('opened_at');
            $t->bigInteger('opening_float_minor')->default(0);   // الرصيد الافتتاحي

            $t->timestamp('closed_at')->nullable();
            $t->bigInteger('closing_counted_minor')->nullable(); // ما عدّه الكاشير فعليًا
            $t->bigInteger('expected_minor')->nullable();        // المحسوب من النظام
            $t->bigInteger('variance_minor')->nullable();        // ⚠ العجز/الزيادة = counted - expected

            $t->bigInteger('cash_sales_minor')->default(0);
            $t->bigInteger('card_sales_minor')->default(0);
            $t->bigInteger('other_sales_minor')->default(0);
            $t->unsignedInteger('orders_count')->default(0);

            $t->enum('status', ['open', 'closed'])->default('open');
            $t->text('note')->nullable();
            $t->timestamps();

            $t->index(['register_id', 'status']);
            $t->index(['user_id', 'opened_at']);
        });

        // حركات نقدية داخل الشيفت (سحب / إيداع)
        Schema::create('cash_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('register_session_id')->constrained()->cascadeOnDelete();
            $t->enum('type', ['cash_in', 'cash_out']);
            $t->bigInteger('amount_minor');
            $t->string('reason');
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->timestamp('created_at')->useCurrent();
        });

        // ═══ المشتريات ═══

        Schema::create('suppliers', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->string('contact_person')->nullable();
            $t->string('phone');
            $t->string('email')->nullable();
            $t->string('address')->nullable();
            $t->string('tax_number')->nullable();          // البطاقة الضريبية
            $t->unsignedInteger('payment_terms_days')->default(0); // أجل السداد
            $t->boolean('is_active')->default(true);
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('purchase_orders', function (Blueprint $t) {
            $t->id();
            $t->string('number')->unique();                // PO-2025-0001
            $t->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $t->foreignId('warehouse_id')->constrained()->restrictOnDelete();

            $t->enum('status', ['draft', 'sent', 'partially_received', 'received', 'cancelled'])
              ->default('draft');

            $t->bigInteger('subtotal_minor')->default(0);
            $t->bigInteger('tax_minor')->default(0);
            $t->bigInteger('shipping_minor')->default(0);
            $t->bigInteger('total_minor')->default(0);
            $t->bigInteger('paid_minor')->default(0);

            $t->date('expected_at')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->timestamps();

            $t->index(['status', 'created_at']);
            $t->index('supplier_id');
        });

        Schema::create('purchase_order_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('variant_id')->constrained('product_variants')->restrictOnDelete();
            $t->decimal('qty_ordered', 12, 3);
            $t->decimal('qty_received', 12, 3)->default(0);
            $t->bigInteger('unit_cost_minor');
            $t->bigInteger('line_total_minor');
        });

        // إذن استلام بضاعة — هو ما يُحرّك المخزون ويعيد حساب متوسط التكلفة
        Schema::create('goods_receipts', function (Blueprint $t) {
            $t->id();
            $t->string('number')->unique();                // GRN-2025-0001
            $t->foreignId('purchase_order_id')->constrained()->restrictOnDelete();
            $t->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $t->string('supplier_invoice_no')->nullable();
            $t->text('note')->nullable();
            $t->foreignId('received_by')->constrained('users')->restrictOnDelete();
            $t->timestamp('received_at');
            $t->timestamps();
        });

        Schema::create('goods_receipt_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('goods_receipt_id')->constrained()->cascadeOnDelete();
            $t->foreignId('purchase_order_line_id')->constrained()->restrictOnDelete();
            $t->foreignId('variant_id')->constrained('product_variants')->restrictOnDelete();
            $t->decimal('qty', 12, 3);
            $t->bigInteger('unit_cost_minor');
        });

        // ═══ التقارير المُجمَّعة (Materialized Rollups) ═══
        // Job يومي 00:05 — التقارير تقرأ من هنا بدل مسح ملايين الصفوف

        Schema::create('daily_sales_summary', function (Blueprint $t) {
            $t->id();
            $t->date('date');
            $t->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $t->string('channel');

            $t->unsignedInteger('orders_count')->default(0);
            $t->decimal('items_qty', 14, 3)->default(0);
            $t->bigInteger('gross_minor')->default(0);      // إجمالي قبل الخصم
            $t->bigInteger('discount_minor')->default(0);
            $t->bigInteger('tax_minor')->default(0);
            $t->bigInteger('net_minor')->default(0);        // صافي المبيعات
            $t->bigInteger('cogs_minor')->default(0);       // تكلفة البضاعة المباعة
            $t->bigInteger('profit_minor')->default(0);     // net - cogs
            $t->bigInteger('aov_minor')->default(0);        // متوسط قيمة الطلب
            $t->timestamps();

            $t->unique(['date', 'warehouse_id', 'channel'], 'ux_dss');
            $t->index('date');
        });

        Schema::create('daily_product_summary', function (Blueprint $t) {
            $t->id();
            $t->date('date');
            $t->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $t->decimal('qty_sold', 14, 3)->default(0);
            $t->bigInteger('revenue_minor')->default(0);
            $t->bigInteger('cogs_minor')->default(0);
            $t->bigInteger('profit_minor')->default(0);

            $t->unique(['date', 'variant_id'], 'ux_dps');
            $t->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_product_summary');
        Schema::dropIfExists('daily_sales_summary');
        Schema::dropIfExists('goods_receipt_lines');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('purchase_order_lines');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('cash_movements');
        Schema::dropIfExists('register_sessions');
        Schema::dropIfExists('registers');
    }
};
