<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>إيصال {{ $order->number }}</title>
    <style>
        /* ══ إيصال حراري 80mm ══ */
        @page { size: 80mm auto; margin: 0; }

        body {
            width: 72mm;
            margin: 0 auto;
            padding: 4mm 0;
            font-family: 'Cairo', monospace;
            font-size: 11px;
            line-height: 1.5;
            color: #000;
        }

        .center { text-align: center; }
        .bold   { font-weight: 700; }

        .shop-name { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
        .shop-info { font-size: 9px; }

        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .meta { font-size: 9px; }
        .meta div { display: flex; justify-content: space-between; }

        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th { text-align: right; border-bottom: 1px solid #000; padding: 3px 0; font-size: 9px; }
        th:last-child, td:last-child { text-align: left; }
        td { padding: 3px 0; vertical-align: top; }

        .item-name { font-size: 10px; }
        .item-qty  { font-size: 9px; color: #444; }

        .totals div {
            display: flex; justify-content: space-between;
            padding: 2px 0; font-size: 11px;
        }
        .totals .grand {
            font-size: 15px; font-weight: 700;
            border-top: 1px solid #000; border-bottom: 1px solid #000;
            padding: 5px 0; margin: 4px 0;
        }

        .footer { font-size: 9px; margin-top: 8px; }

        /* الطباعة المباشرة من المتصفح */
        @media print {
            body { width: 72mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="center">
    <div class="shop-name">{{ $shop['name'] }}</div>
    <div class="shop-info">{{ $shop['tagline'] }}</div>
    <div class="shop-info">{{ $shop['address'] }}</div>
</div>

<hr>

<div class="meta">
    <div><span>الفاتورة</span><span class="bold">{{ $order->number }}</span></div>
    <div><span>التاريخ</span><span>{{ $order->placed_at?->format('Y/m/d H:i') }}</span></div>
    <div><span>الكاشير</span><span>{{ $order->creator?->name ?? '—' }}</span></div>
    @if ($order->customer)
        <div><span>العميل</span><span>{{ $order->customer->name }}</span></div>
    @endif
</div>

<hr>

<table>
    <thead>
        <tr>
            <th>الصنف</th>
            <th style="width:22%">الإجمالي</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($order->lines as $line)
            <tr>
                <td>
                    <div class="item-name">{{ $line->name_snapshot }}</div>
                    <div class="item-qty">
                        {{ $line->quantity()->format() }} × {{ $line->unit_price_minor->format(false) }}
                    </div>
                </td>
                <td>{{ $line->line_total_minor->format(false) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<hr>

<div class="totals">
    <div><span>الإجمالي</span><span>{{ $order->subtotal_minor->format(false) }}</span></div>

    @if ($order->discount_minor->isPositive())
        <div><span>الخصم</span><span>({{ $order->discount_minor->format(false) }})</span></div>
    @endif

    <div><span>ض.ق.م 14%</span><span>{{ $order->tax_minor->format(false) }}</span></div>

    <div class="grand">
        <span>المستحق</span>
        <span>{{ $order->total_minor->format() }}</span>
    </div>

    @foreach ($order->tenders as $t)
        <div><span>{{ $t->method->getLabel() }}</span><span>{{ $t->amount_minor->format(false) }}</span></div>

        @if ($t->tendered_minor && $t->change_minor->isPositive())
            <div><span>المدفوع</span><span>{{ $t->tendered_minor->format(false) }}</span></div>
            <div class="bold"><span>الباقي</span><span>{{ $t->change_minor->format(false) }}</span></div>
        @endif
    @endforeach
</div>

<hr>

<div class="center footer">
    <div>عدد الأصناف: {{ $order->lines->count() }}</div>
    <div style="margin-top:4px">شكرًا لتعاملكم معنا</div>
    <div>البضاعة المباعة لا تُرد ولا تُستبدل بعد ٤٨ ساعة</div>
</div>

<div class="no-print center" style="margin-top:12px">
    <button onclick="window.print()"
            style="padding:8px 20px;background:#000;color:#fff;border:none;border-radius:4px;font-family:inherit;">
        طباعة
    </button>
</div>

<script>
    // الطباعة التلقائية عند الفتح من نقطة البيع
    if (new URLSearchParams(location.search).get('autoprint') === '1') {
        window.onload = () => window.print();
    }
</script>

</body>
</html>
