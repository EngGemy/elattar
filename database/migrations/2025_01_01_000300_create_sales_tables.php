<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * المبيعات + العملاء + التسعير
 * المبدأ: الفاتورة وثيقة مجمّدة (Snapshot) — لا تتغيّر أبدًا بعد إصدارها.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── العملاء
        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->string('phone')->unique();
            $t->string('email')->nullable()->unique();
            $t->enum('group', ['retail', 'wholesale', 'vip'])->default('retail');
            $t->text('notes')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('addresses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $t->enum('type', ['shipping', 'billing', 'both'])->default('both');
            $t->string('label')->nullable();               // "المنزل" / "العمل"
            $t->string('recipient_name');
            $t->string('phone');
            $t->string('governorate')->default('الدقهلية');
            $t->string('city');
            $t->string('area')->nullable();
            $t->string('street');
            $t->string('building')->nullable();
            $t->string('landmark')->nullable();            // علامة مميزة
            $t->boolean('is_default')->default(false);
            $t->timestamps();

            $t->index(['customer_id', 'is_default']);
        });

        // ── الكوبونات والعروض
        Schema::create('coupons', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->enum('type', ['percent', 'fixed', 'free_shipping']);
            $t->decimal('value', 10, 4)->default(0);          // 10.0 = 10%  أو  5000 قرش
            $t->bigInteger('min_order_minor')->default(0);    // حد أدنى للطلب
            $t->bigInteger('max_discount_minor')->nullable(); // سقف الخصم
            $t->unsignedInteger('usage_limit')->nullable();   // إجمالي مرات الاستخدام
            $t->unsignedInteger('usage_limit_per_customer')->default(1);
            $t->unsignedInteger('used_count')->default(0);
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();

            $t->index(['is_active', 'starts_at', 'ends_at']);
        });

        Schema::create('coupon_usages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $t->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('order_id')->nullable();
            $t->bigInteger('discount_minor');
            $t->timestamp('created_at')->useCurrent();

            $t->index(['coupon_id', 'customer_id']);
        });

        // ── الشحن
        Schema::create('shipping_zones', function (Blueprint $t) {
            $t->id();
            $t->string('name');                    // "مركز طلخا"
            $t->json('cities');                    // ["طلخا","المنصورة"]
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('shipping_methods', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $t->string('name');                                // "توصيل داخل طلخا"
            $t->enum('rate_type', ['flat', 'by_weight', 'free'])->default('flat');
            $t->bigInteger('base_rate_minor')->default(0);
            $t->bigInteger('per_kg_minor')->default(0);
            $t->bigInteger('free_above_minor')->nullable();    // مجاني فوق مبلغ معيّن
            $t->unsignedInteger('eta_hours')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        // ── الطلبات
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->string('number')->unique();                    // ORD-2025-000001
            $t->foreignId('customer_id')->nullable()->constrained()->nullOnDelete(); // NULL = عميل عابر (POS)
            $t->foreignId('warehouse_id')->constrained()->restrictOnDelete();

            $t->enum('channel', ['online', 'pos', 'whatsapp'])->default('online');

            // حالة الطلب — تديرها State Machine
            $t->string('status')->default('pending');
            $t->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded', 'partially_refunded'])
              ->default('unpaid');

            // كل المبالغ بالقروش
            $t->bigInteger('subtotal_minor')->default(0);
            $t->bigInteger('discount_minor')->default(0);
            $t->bigInteger('tax_minor')->default(0);
            $t->bigInteger('shipping_minor')->default(0);
            $t->bigInteger('total_minor')->default(0);
            $t->bigInteger('paid_minor')->default(0);
            $t->bigInteger('refunded_minor')->default(0);
            $t->bigInteger('cogs_minor')->default(0);         // تكلفة البضاعة المباعة (مجمّدة)

            $t->char('currency', 3)->default('EGP');

            // العناوين مُجمّدة كـ JSON — لا FK. تغيير عنوان العميل لا يمسّ الفواتير القديمة.
            $t->json('shipping_address')->nullable();
            $t->json('billing_address')->nullable();

            $t->foreignId('shipping_method_id')->nullable()->constrained()->nullOnDelete();
            $t->string('shipping_carrier')->nullable();       // شركة الشحن
            $t->string('tracking_number')->nullable();

            $t->string('coupon_code')->nullable();
            $t->string('idempotency_key')->nullable()->unique(); // منع الطلب/الخصم المزدوج
            $t->text('notes')->nullable();
            $t->text('internal_notes')->nullable();

            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('placed_at')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->index(['status', 'placed_at']);
            $t->index(['customer_id', 'placed_at']);
            $t->index(['channel', 'placed_at']);
            $t->index('tracking_number');
        });

        Schema::create('order_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('variant_id')->constrained('product_variants')->restrictOnDelete();

            // لقطة مجمّدة وقت البيع — لو تغيّر اسم/سعر المنتج غدًا، الفاتورة تبقى صحيحة
            $t->string('sku_snapshot');
            $t->string('name_snapshot');
            $t->json('attributes_snapshot')->nullable();

            $t->decimal('qty', 12, 3);
            $t->string('unit');                              // gram / piece
            $t->bigInteger('unit_price_minor');
            $t->bigInteger('cost_minor');                    // ⚠ التكلفة لحظة البيع — أساس تقرير الربح
            $t->bigInteger('line_discount_minor')->default(0);
            $t->decimal('tax_rate', 6, 4)->default(0);
            $t->bigInteger('tax_minor')->default(0);
            $t->bigInteger('line_total_minor');

            $t->index('variant_id');
        });

        // ── سجل تغيّر الحالة (Timeline)
        Schema::create('order_status_history', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->string('from_status')->nullable();
            $t->string('to_status');
            $t->text('note')->nullable();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamp('created_at')->useCurrent();

            $t->index(['order_id', 'created_at']);
        });

        // ── الدفعات (Split Payment: كاش + بطاقة في نفس الفاتورة)
        Schema::create('order_tenders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('register_session_id')->nullable();

            $t->enum('method', ['cash', 'card', 'wallet', 'transfer', 'cod']);
            $t->bigInteger('amount_minor');                  // المبلغ المحتسب
            $t->bigInteger('tendered_minor')->nullable();    // المبلغ المستلم فعليًا
            $t->bigInteger('change_minor')->default(0);      // الباقي
            $t->string('reference')->nullable();             // رقم عملية البطاقة
            $t->enum('status', ['pending', 'captured', 'failed', 'refunded'])->default('captured');
            $t->timestamps();

            $t->index('order_id');
        });

        // ── المرتجعات والاسترداد
        Schema::create('refunds', function (Blueprint $t) {
            $t->id();
            $t->string('number')->unique();                  // RFN-2025-0001
            $t->foreignId('order_id')->constrained()->restrictOnDelete();
            $t->bigInteger('amount_minor');
            $t->enum('reason', ['damaged', 'wrong_item', 'customer_changed_mind', 'expired', 'other']);
            $t->boolean('restock')->default(true);           // هل تُرجَع للمخزون؟
            $t->text('note')->nullable();
            $t->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('refund_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('refund_id')->constrained()->cascadeOnDelete();
            $t->foreignId('order_line_id')->constrained()->restrictOnDelete();
            $t->decimal('qty', 12, 3);
            $t->bigInteger('amount_minor');
        });

        // ── الفواتير (PDF)
        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->string('number')->unique();                  // INV-2025-000001
            $t->foreignId('order_id')->constrained()->restrictOnDelete();
            $t->bigInteger('total_minor');
            $t->string('pdf_path')->nullable();
            $t->timestamp('issued_at');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('refund_lines');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('order_tenders');
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_lines');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipping_zones');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('customers');
    }
};
