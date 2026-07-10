<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاتورة {{ $invoice->number }}</title>
    <style>
        /* إيصال حراري 80mm — مثل فواتير المطاعم */
        @page { size: 80mm auto; margin: 0; }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            width: 72mm;
            margin: 0 auto;
            padding: 3mm 0 6mm;
            font-family: 'Courier New', 'Cascadia Mono', monospace;
            font-size: 11px;
            line-height: 1.45;
            color: #000;
            background: #fff;
        }

        .center { text-align: center; }
        .bold   { font-weight: 700; }
        .ltr    { direction: ltr; unicode-bidi: embed; }

        .seal {
            width: 36px; height: 36px; margin: 0 auto 4px;
            border: 2px solid #000; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 700;
        }

        .shop-name { font-size: 15px; font-weight: 700; letter-spacing: -.3px; }
        .shop-sub  { font-size: 9px; margin-top: 2px; }

        .rule, .rule-d {
            border: none;
            margin: 7px 0;
        }
        .rule   { border-top: 2px solid #000; }
        .rule-d { border-top: 1px dashed #000; }

        .meta { font-size: 10px; }
        .meta .row {
            display: flex;
            justify-content: space-between;
            gap: 6px;
            padding: 1px 0;
        }
        .meta .row span:last-child { font-weight: 600; text-align: left; }

        .items { width: 100%; font-size: 10px; }
        .item { padding: 5px 0; border-bottom: 1px dotted #bbb; }
        .item:last-child { border-bottom: none; }
        .item-name { font-weight: 700; font-size: 10px; line-height: 1.3; }
        .item-detail {
            display: flex;
            justify-content: space-between;
            margin-top: 2px;
            font-size: 9px;
            color: #222;
        }
        .item-total { font-weight: 700; font-size: 10px; }

        .totals { font-size: 10px; }
        .totals .row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }
        .totals .grand {
            font-size: 14px;
            font-weight: 700;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 6px 0;
            margin: 5px 0;
        }
        .totals .due { font-size: 12px; font-weight: 700; }

        .footer { font-size: 9px; line-height: 1.55; margin-top: 4px; }
        .footer .thanks { font-size: 11px; font-weight: 700; margin: 4px 0; }

        .barcode {
            font-family: 'Libre Barcode 39', 'Courier New', monospace;
            font-size: 28px;
            letter-spacing: 2px;
            margin: 6px 0 2px;
            line-height: 1;
        }

        .no-print { margin-top: 14px; }
        .no-print button {
            width: 100%;
            padding: 10px;
            background: #000;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        @media print {
            body { width: 72mm; padding: 2mm 0; }
            .no-print { display: none !important; }
        }

        @media screen {
            body {
                box-shadow: 0 0 0 1px #ddd, 0 8px 24px rgba(0,0,0,.12);
                margin: 16px auto;
                padding: 8mm 4mm;
            }
        }
    </style>
</head>
<body>

<div class="center">
    @if($shop['logo_url'] ?? null)
        <img src="{{ $shop['logo_url'] }}" alt="" style="width:auto;height:48px;max-width:280px;object-fit:contain;margin:0 auto 8px;display:block">
    @else
        <div class="seal">ع</div>
    @endif
    <div class="shop-name">{{ $shop['name'] }}</div>
    <div class="shop-sub">{{ $shop['tagline'] }}</div>
    <div class="shop-sub">{{ $shop['address'] }}</div>
</div>

<hr class="rule">

<div class="meta">
    <div class="row"><span>فاتورة</span><span class="ltr">{{ $invoice->number }}</span></div>
    <div class="row"><span>طلب</span><span class="ltr">{{ $order->number }}</span></div>
    <div class="row"><span>التاريخ</span><span class="ltr">{{ $invoice->issued_at->format('d/m/Y H:i') }}</span></div>
    <div class="row"><span>القناة</span><span>{{ $order->channel->getLabel() }}</span></div>
    <div class="row"><span>السداد</span><span>{{ $order->payment_status->getLabel() }}</span></div>
    @php $payMethod = $order->shipping_address['payment_method'] ?? null; @endphp
    @if ($payMethod)
        <div class="row"><span>الدفع</span><span>{{ \App\Support\StorefrontCheckout::paymentLabel($payMethod) }}</span></div>
    @endif
</div>

<hr class="rule-d">

<div class="meta">
    <div class="row"><span>العميل</span><span>{{ $order->customer?->name ?? ($order->shipping_address['recipient_name'] ?? 'عميل') }}</span></div>
    @if ($phone = $order->customer?->phone ?? ($order->shipping_address['phone'] ?? null))
        <div class="row"><span>الهاتف</span><span class="ltr">{{ $phone }}</span></div>
    @endif
    @if ($addr = $order->shipping_address)
        <div class="row"><span>العنوان</span><span>{{ $addr['city'] ?? '' }} — {{ $addr['street'] ?? '' }}</span></div>
    @endif
</div>

<hr class="rule-d">

<div class="items">
    @foreach ($order->lines as $line)
        <div class="item">
            <div class="item-name">{{ $line->name_snapshot }}</div>
            <div class="item-detail">
                <span>{{ $line->quantity()->format() }} × {{ $line->unit_price_minor->format(false) }}</span>
                <span class="item-total">{{ $line->line_total_minor->format(false) }} ج</span>
            </div>
        </div>
    @endforeach
</div>

<hr class="rule-d">

<div class="totals">
    <div class="row"><span>الإجمالي</span><span>{{ $order->subtotal_minor->format(false) }} ج</span></div>

    @if ($order->discount_minor->isPositive())
        <div class="row"><span>خصم @if($order->coupon_code)({{ $order->coupon_code }})@endif</span><span>-{{ $order->discount_minor->format(false) }} ج</span></div>
    @endif

    <div class="row"><span>ض.ق.م 14%</span><span>{{ $order->tax_minor->format(false) }} ج</span></div>

    @if ($order->shipping_minor->isPositive())
        <div class="row"><span>شحن</span><span>{{ $order->shipping_minor->format(false) }} ج</span></div>
    @endif

    <div class="row grand">
        <span>الإجمالي</span>
        <span>{{ $order->total_minor->format() }}</span>
    </div>

    @foreach ($order->tenders as $t)
        <div class="row"><span>{{ $t->method->getLabel() }}</span><span>{{ $t->amount_minor->format(false) }} ج</span></div>
        @if ($t->change_minor->isPositive())
            <div class="row bold"><span>الباقي</span><span>{{ $t->change_minor->format(false) }} ج</span></div>
        @endif
    @endforeach

    @if ($order->paid_minor->isPositive())
        <div class="row"><span>مدفوع</span><span>{{ $order->paid_minor->format(false) }} ج</span></div>
    @endif

    @if ($order->balanceDue()->isPositive())
        <div class="row due"><span>متبقي</span><span>{{ $order->balanceDue()->format() }}</span></div>
    @endif
</div>

<hr class="rule">

<div class="center footer">
    <div>عدد الأصناف: {{ $order->lines->count() }}</div>
    <div class="thanks">شكرًا لتعاملكم معنا</div>
    <div>الأسعار شاملة ضريبة القيمة المضافة</div>
    <div style="margin-top:3px">البضاعة المباعة لا تُرد بعد ٤٨ ساعة</div>
    <div class="barcode ltr">*{{ $order->number }}*</div>
</div>

<div class="no-print center">
    <button type="button" onclick="window.print()">🖨 طباعة الإيصال</button>
</div>

<script>
    if (new URLSearchParams(location.search).get('autoprint') === '1') {
        window.addEventListener('load', () => setTimeout(() => window.print(), 300));
    }
</script>

</body>
</html>
