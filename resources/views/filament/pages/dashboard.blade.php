<x-filament-panels::page>
@php
    $fmt  = fn ($m) => number_format(($m ?? 0) / 100, 2);
    $fmtE = fn ($m) => $fmt($m) . ' ج.م';
    $pct  = fn ($v) => ($v >= 0 ? '+' : '') . number_format($v, 1) . '%';
    $c    = $summary['current'];
    $g    = $summary['growth'];
    $t    = $today['current'];
    $y    = $yesterday['current'];
    $maxDaily = collect($dailyTrend)->max('net_minor') ?: 1;
    $chLabels = ['pos' => 'نقطة البيع', 'online' => 'المتجر الإلكتروني'];
@endphp

<style>
.dash{font-family:'Segoe UI',Tahoma,sans-serif;--ink:#1a1410;--ink-soft:#6b5a48;--parchment:#faf6ef;
    --parchment-2:#f3ebe0;--gold:#d4a85a;--gold-deep:#b8892b;--gold-light:#f0d9a8;
    --green:#3b533d;--red:#c0392b;--blue:#1a5276;--hair:rgba(26,20,16,.1);--shadow:0 4px 20px rgba(13,10,8,.08);--r:14px}
.dash-toolbar-brand{display:flex;align-items:center;gap:16px;flex:1;min-width:0}
.dash-logo{height:52px;width:auto;max-width:min(240px,42vw);object-fit:contain;flex-shrink:0;
    filter:drop-shadow(0 4px 14px rgba(212,168,90,.45))}
.dash *{box-sizing:border-box}
.dash-toolbar{display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;
    background:linear-gradient(135deg,#0d0a08 0%,#1a1410 55%,#241a11 100%);border-radius:var(--r);padding:18px 22px;
    color:#fff;margin-bottom:16px;box-shadow:0 12px 40px -8px rgba(13,10,8,.35);
    border:1px solid rgba(212,168,90,.2)}
.dash-toolbar h1{font-family:Georgia,serif;font-size:1.4rem;margin:0 0 4px}
.dash-toolbar .sub{font-size:.75rem;opacity:.65}
.dash-periods{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px}
.dash-chip{padding:6px 14px;border-radius:20px;border:1px solid var(--hair);background:#fff;
    font-size:.76rem;font-weight:600;color:var(--ink-soft);cursor:pointer}
.dash-chip:hover{border-color:var(--gold);color:var(--gold-deep)}
.dash-filters{display:grid;grid-template-columns:1fr 1fr auto;gap:10px;margin-bottom:16px;align-items:end}
.dash-date{width:100%;padding:9px 12px;border:1px solid var(--hair);border-radius:10px;font-size:.85rem}
.dash-date:focus{outline:none;border-color:var(--gold)}
.dash-btn{display:inline-flex;align-items:center;gap:5px;padding:8px 14px;border-radius:9px;
    font-size:.78rem;font-weight:700;cursor:pointer;border:none;white-space:nowrap}
.dash-btn-gold{background:var(--gold);color:var(--ink)}
.dash-btn-outline{background:transparent;border:1px solid rgba(255,255,255,.35);color:#fff}
.dash-btn-green{background:var(--green);color:#fff}
.dash-section{margin-bottom:20px}
.dash-section-title{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;
    color:var(--ink-soft);margin-bottom:10px;padding-bottom:6px;border-bottom:2px solid var(--gold)}
.dash-kpis{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:10px}
.dash-kpi{background:#fff;border:1px solid var(--hair);border-radius:var(--r);padding:14px 16px;
    box-shadow:var(--shadow);position:relative;overflow:hidden}
.dash-kpi::after{content:'';position:absolute;top:0;right:0;left:0;height:3px;background:var(--gold)}
.dash-kpi.dark{background:linear-gradient(135deg,var(--ink),#3d2e1e);border:none}
.dash-kpi.dark::after{background:var(--gold-light)}
.dash-kpi .lbl{font-size:.68rem;color:var(--ink-soft);margin-bottom:3px}
.dash-kpi .val{font-size:1.25rem;font-weight:800;color:var(--ink);line-height:1.1}
.dash-kpi .sub{font-size:.7rem;margin-top:4px;font-weight:600;color:var(--green)}
.dash-kpi.dark .lbl{color:rgba(255,255,255,.55)} .dash-kpi.dark .val{color:var(--gold-light)}
.dash-kpi.dark .sub{color:rgba(255,255,255,.65)}
.dash-grid{display:grid;grid-template-columns:2fr 1fr;gap:16px}
@media(max-width:1100px){.dash-grid{grid-template-columns:1fr}}
.dash-panel{background:#fff;border:1px solid var(--hair);border-radius:var(--r);box-shadow:var(--shadow);
    overflow:hidden;margin-bottom:16px}
.dash-panel-head{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;
    background:var(--parchment);border-bottom:1px solid var(--hair)}
.dash-panel-head h3{margin:0;font-size:.88rem;font-weight:700;color:var(--ink)}
.dash-panel-head a{font-size:.72rem;color:var(--gold-deep);font-weight:600;text-decoration:none}
.dash-table{width:100%;border-collapse:collapse;font-size:.78rem}
.dash-table th{padding:8px 12px;background:var(--ink);color:#fff;font-size:.68rem;font-weight:600;text-align:right}
.dash-table td{padding:8px 12px;border-bottom:1px solid var(--hair);vertical-align:middle}
.dash-table tr:hover td{background:var(--parchment)}
.dash-table .num{font-family:monospace;direction:ltr;text-align:left;font-weight:600}
.dash-badge{display:inline-block;padding:2px 8px;border-radius:12px;font-size:.65rem;font-weight:700}
.dash-badge-ok{background:#e8f5e9;color:var(--green)}
.dash-badge-warn{background:#fff3e0;color:#e65100}
.dash-badge-danger{background:#fdecea;color:var(--red)}
.dash-badge-info{background:#e3f2fd;color:var(--blue)}
.dash-bars{display:flex;align-items:flex-end;gap:3px;height:100px;padding:12px}
.dash-bar{flex:1;max-width:28px;border-radius:3px 3px 0 0;
    background:linear-gradient(180deg,var(--gold),var(--gold-deep));min-height:2px}
.dash-quick{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:8px;margin-bottom:16px}
.dash-quick a{display:flex;flex-direction:column;align-items:center;gap:4px;padding:12px 8px;
    background:#fff;border:1px solid var(--hair);border-radius:10px;text-decoration:none;
    color:var(--ink);font-size:.75rem;font-weight:600;transition:.15s;box-shadow:var(--shadow)}
.dash-quick a:hover{border-color:var(--gold);transform:translateY(-2px)}
.dash-quick a span{font-size:1.3rem}
.dash-quick-order{display:flex;align-items:center;gap:8px;padding:10px 14px;background:#fff8e6;
    border:1px solid var(--gold);border-radius:10px;text-decoration:none;color:var(--ink);
    font-size:.78rem;font-weight:700;transition:.15s;box-shadow:var(--shadow)}
.dash-quick-order:hover{background:var(--gold);color:var(--ink);transform:translateY(-1px)}
.dash-quick-order .amt{margin-right:auto;font-family:monospace;color:var(--gold-deep)}
.dash-quick-order:hover .amt{color:var(--ink)}
.dash-row-link{cursor:pointer;transition:background .12s}
.dash-row-link:hover td{background:var(--parchment)!important}
.dash-row-link a{color:inherit;text-decoration:none;display:contents}
.dash-open-btn{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:7px;
    background:var(--gold);color:var(--ink);font-size:.68rem;font-weight:700;text-decoration:none;white-space:nowrap}
.dash-open-btn:hover{background:var(--gold-deep);color:#fff}
.dash-pulse{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px;margin-bottom:16px}
.dash-pulse .p{background:linear-gradient(135deg,#1a5276,#2980b9);border-radius:10px;padding:12px;color:#fff}
.dash-pulse .p .v{font-size:1.1rem;font-weight:800}
.dash-pulse .p .l{font-size:.65rem;opacity:.8;margin-top:2px}
.dash-empty{padding:24px;text-align:center;color:var(--ink-soft);font-size:.8rem}
.fi-page>header{display:none!important}
[wire\:loading]{opacity:.6;pointer-events:none}
</style>

<div class="dash" wire:loading.class="opacity-60">

    {{-- شريط علوي --}}
    <div class="dash-toolbar">
        <div class="dash-toolbar-brand">
            @if($logoUrl = \App\Support\ShopSettings::logoUrl())
                <img src="{{ $logoUrl }}" alt="{{ \App\Support\ShopSettings::name() }}" class="dash-logo">
            @endif
            <div>
                <h1>لوحة التحكم</h1>
                <div class="sub">{{ $fromDate->format('d/m/Y') }} — {{ $toDate->format('d/m/Y') }} · نظرة شاملة على المتجر</div>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="{{ $links['reports'] }}" class="dash-btn dash-btn-outline">📊 مركز التقارير</a>
            @if (! $isCashier)
                <button type="button" class="dash-btn dash-btn-gold" wire:click="exportAll">📥 Excel شامل</button>
            @endif
        </div>
    </div>

    {{-- نبض اليوم --}}
    @if (! $isCashier)
        <div class="dash-section">
            <div class="dash-section-title">⚡ نبض اليوم</div>
            <div class="dash-pulse">
                <div class="p"><div class="v">{{ $fmtE($t['net_minor']) }}</div><div class="l">مبيعات اليوم</div></div>
                <div class="p" style="background:linear-gradient(135deg,var(--ink),#3d2e1e)">
                    <div class="v">{{ number_format($t['orders_count']) }}</div><div class="l">طلبات اليوم</div>
                </div>
                <div class="p" style="background:linear-gradient(135deg,var(--green),#0e6b32)">
                    <div class="v">{{ $fmtE($t['profit_minor']) }}</div><div class="l">ربح اليوم</div>
                </div>
                <div class="p" style="background:linear-gradient(135deg,#6c3483,#8e44ad)">
                    <div class="v">{{ $fmtE($y['net_minor']) }}</div><div class="l">مبيعات أمس</div>
                </div>
            </div>
        </div>
    @endif

    {{-- فلاتر --}}
    <div class="dash-periods">
        @foreach (['today'=>'اليوم','week'=>'الأسبوع','month'=>'الشهر','quarter'=>'الربع','year'=>'السنة'] as $k => $l)
            <button type="button" class="dash-chip" wire:click="setPeriod('{{ $k }}')">{{ $l }}</button>
        @endforeach
    </div>
    <div class="dash-filters">
        <label><span style="font-size:.72rem;font-weight:600;color:var(--ink-soft)">من</span>
            <input type="date" wire:model.live="from" class="dash-date"></label>
        <label><span style="font-size:.72rem;font-weight:600;color:var(--ink-soft)">إلى</span>
            <input type="date" wire:model.live="to" class="dash-date"></label>
        <a href="{{ $links['pos'] }}" class="dash-btn dash-btn-green" style="text-decoration:none;height:fit-content">🛒 نقطة البيع</a>
    </div>

    {{-- اختصارات --}}
    <div class="dash-quick">
        <a href="{{ $links['orders'] }}"><span>📋</span>الطلبات</a>
        <a href="{{ $links['orders_pending'] }}"><span>🔔</span>معلّقة@if(count($pendingOnline)) ({{ count($pendingOnline) }})@endif</a>
        <a href="{{ $links['inventory'] }}"><span>📦</span>المخزون</a>
        <a href="{{ $links['purchasing'] }}"><span>🛍️</span>المشتريات</a>
        <a href="{{ $links['reports'] }}"><span>📊</span>التقارير</a>
        <a href="{{ $links['pos'] }}"><span>💵</span>الكاشير</a>
        <a href="{{ $links['register_sessions'] }}"><span>🧾</span>الشيفتات</a>
    </div>

    {{-- وصول سريع للطلبات المعلّقة --}}
    @if (count($pendingOnline))
        <div class="dash-section">
            <div class="dash-section-title">⚡ وصول سريع — طلبات أونلاين تحتاج إجراء</div>
            <div style="display:flex;flex-wrap:wrap;gap:8px">
                @foreach ($pendingOnline as $o)
                    <a href="{{ $o['url'] }}" class="dash-quick-order">
                        <span>🔔</span>
                        <span>{{ $o['number'] }}</span>
                        <span style="font-weight:400;color:var(--ink-soft)">{{ $o['customer'] }}</span>
                        <span class="amt">{{ $fmtE($o['total']) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if (! $isCashier)
        {{-- KPIs الفترة --}}
        <div class="dash-section">
            <div class="dash-section-title">📊 ملخص الفترة المحددة</div>
            <div class="dash-kpis">
                <div class="dash-kpi dark">
                    <div class="lbl">صافي المبيعات</div>
                    <div class="val">{{ $fmtE($c['net_minor']) }}</div>
                    <div class="sub">{{ $pct($g['net']) }}</div>
                </div>
                <div class="dash-kpi">
                    <div class="lbl">مجمل الربح</div>
                    <div class="val">{{ $fmtE($c['profit_minor']) }}</div>
                    <div class="sub">{{ $c['gp_percent'] }}% هامش</div>
                </div>
                <div class="dash-kpi">
                    <div class="lbl">عدد الطلبات</div>
                    <div class="val">{{ number_format($c['orders_count']) }}</div>
                    <div class="sub">{{ $pct($g['orders']) }}</div>
                </div>
                <div class="dash-kpi">
                    <div class="lbl">متوسط الطلب</div>
                    <div class="val">{{ $fmtE($c['aov_minor']) }}</div>
                    <div class="sub">{{ $pct($g['aov']) }}</div>
                </div>
                <div class="dash-kpi">
                    <div class="lbl">تكلفة البضاعة</div>
                    <div class="val">{{ $fmtE($c['cogs_minor']) }}</div>
                    <div class="sub">خصم: {{ $fmtE($c['discount_minor']) }}</div>
                </div>
                <div class="dash-kpi">
                    <div class="lbl">قيمة المخزون</div>
                    <div class="val">{{ $fmtE($totalInventory) }}</div>
                    <div class="sub" style="color:var(--red)">راكد: {{ $deadStockCount }}</div>
                </div>
                <div class="dash-kpi">
                    <div class="lbl">العملاء</div>
                    <div class="val">{{ number_format($customers['total']) }}</div>
                    <div class="sub">{{ $customers['buyers'] }} مشتري · {{ $customers['new'] }} جديد</div>
                </div>
                <div class="dash-kpi">
                    <div class="lbl">أوامر شراء معلّقة</div>
                    <div class="val">{{ $purchasing['pending'] }}</div>
                    <div class="sub">{{ $purchasing['received_mtd'] }} مستلمة الشهر</div>
                </div>
            </div>
        </div>
    @endif

    <div class="dash-grid">
        {{-- العمود الأيسر --}}
        <div>
            @if (! $isCashier && count($dailyTrend))
                <div class="dash-panel">
                    <div class="dash-panel-head"><h3>📈 المبيعات اليومية</h3>
                        <a href="{{ $links['reports'] }}">تفاصيل ←</a></div>
                    <div class="dash-bars">
                        @foreach ($dailyTrend as $d)
                            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px"
                                 title="{{ $d->date }}: {{ $fmtE($d->net_minor) }}">
                                <div class="dash-bar" style="height:{{ max(2, round($d->net_minor / $maxDaily * 90)) }}px;width:100%"></div>
                                <span style="font-size:.55rem;color:var(--ink-soft)">{{ \Carbon\Carbon::parse($d->date)->format('d/m') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (! $isCashier && count($channels))
                <div class="dash-panel">
                    <div class="dash-panel-head"><h3>🔀 القنوات</h3></div>
                    <table class="dash-table">
                        <thead><tr><th>القناة</th><th style="text-align:center">طلبات</th><th>مبيعات</th><th>ربح</th><th style="text-align:center">هامش</th></tr></thead>
                        <tbody>
                            @foreach ($channels as $ch)
                                <tr>
                                    <td style="font-weight:600">{{ $chLabels[$ch->channel] ?? $ch->channel }}</td>
                                    <td style="text-align:center">{{ $ch->orders_count }}</td>
                                    <td class="num">{{ $fmtE($ch->net_minor) }}</td>
                                    <td class="num" style="color:var(--green)">{{ $fmtE($ch->profit_minor) }}</td>
                                    <td style="text-align:center"><span class="dash-badge dash-badge-ok">{{ $ch->gp_percent }}%</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if (! $isCashier && count($bestSellers))
                <div class="dash-panel">
                    <div class="dash-panel-head"><h3>🏆 أفضل المنتجات</h3><a href="{{ $links['reports'] }}">الكل ←</a></div>
                    <table class="dash-table">
                        <thead><tr><th>#</th><th>الصنف</th><th>كمية</th><th>إيراد</th><th>ربح</th></tr></thead>
                        <tbody>
                            @foreach ($bestSellers as $i => $r)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td style="font-weight:600">{{ $r->product_name }}</td>
                                    <td style="text-align:center">{{ number_format($r->qty_sold, 0) }}</td>
                                    <td class="num">{{ $fmtE($r->revenue_minor) }}</td>
                                    <td class="num" style="color:var(--green)">{{ $fmtE($r->profit_minor) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if (! $isCashier && count($byCategory))
                <div class="dash-panel">
                    <div class="dash-panel-head"><h3>💰 الربحية بالتصنيف</h3></div>
                    <table class="dash-table">
                        <thead><tr><th>التصنيف</th><th>إيراد</th><th>ربح</th><th style="text-align:center">GP%</th></tr></thead>
                        <tbody>
                            @foreach ($byCategory as $r)
                                <tr>
                                    <td style="font-weight:600">{{ $r->category_name }}</td>
                                    <td class="num">{{ $fmtE($r->revenue_minor) }}</td>
                                    <td class="num" style="color:var(--green)">{{ $fmtE($r->profit_minor) }}</td>
                                    <td style="text-align:center"><span class="dash-badge {{ $r->gp_percent < 20 ? 'dash-badge-warn' : 'dash-badge-ok' }}">{{ $r->gp_percent }}%</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- آخر الطلبات --}}
            <div class="dash-panel">
                <div class="dash-panel-head"><h3>📋 آخر الطلبات</h3><a href="{{ $links['orders'] }}">الكل ←</a></div>
                @if (count($recentOrders))
                    <table class="dash-table">
                        <thead><tr><th>الرقم</th><th>العميل</th><th>القناة</th><th>الحالة</th><th>الإجمالي</th><th>الوقت</th></tr></thead>
                        <tbody>
                            @foreach ($recentOrders as $o)
                                <tr class="dash-row-link">
                                    <td style="font-family:monospace;font-weight:700">
                                        <a href="{{ $o['url'] }}">{{ $o['number'] }}</a>
                                    </td>
                                    <td><a href="{{ $o['url'] }}">{{ $o['customer'] }}</a></td>
                                    <td><a href="{{ $o['url'] }}"><span class="dash-badge dash-badge-info">{{ $chLabels[$o['channel']] ?? $o['channel'] }}</span></a></td>
                                    <td><a href="{{ $o['url'] }}"><span class="dash-badge dash-badge-ok">{{ $o['status'] }}</span></a></td>
                                    <td class="num"><a href="{{ $o['url'] }}">{{ $fmtE($o['total']) }}</a></td>
                                    <td style="font-size:.7rem;color:var(--ink-soft)">
                                        <a href="{{ $o['url'] }}" style="display:flex;align-items:center;gap:6px;justify-content:space-between">
                                            {{ $o['placed'] }}
                                            <span class="dash-open-btn">فتح ←</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="dash-empty">لا توجد طلبات بعد</div>
                @endif
            </div>
        </div>

        {{-- العمود الأيمن --}}
        <div>
            @if (count($pendingOnline))
                <div class="dash-panel" style="border-color:var(--gold)">
                    <div class="dash-panel-head" style="background:#fff8e6">
                        <h3>🔔 طلبات أونلاين معلّقة ({{ count($pendingOnline) }})</h3>
                        <a href="{{ $links['orders'] }}">عرض ←</a>
                    </div>
                    <table class="dash-table">
                        <tbody>
                            @foreach ($pendingOnline as $o)
                                <tr class="dash-row-link">
                                    <td style="font-weight:700;font-family:monospace">
                                        <a href="{{ $o['url'] }}">{{ $o['number'] }}</a>
                                    </td>
                                    <td><a href="{{ $o['url'] }}">{{ $o['customer'] }}</a></td>
                                    <td class="num"><a href="{{ $o['url'] }}">{{ $fmtE($o['total']) }}</a></td>
                                    <td>
                                        <a href="{{ $o['url'] }}" style="display:flex;align-items:center;gap:6px;justify-content:space-between;font-size:.68rem;color:var(--ink-soft)">
                                            {{ $o['placed'] }}
                                            <span class="dash-open-btn">معالجة ←</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if (count($ordersPipeline))
                <div class="dash-panel">
                    <div class="dash-panel-head"><h3>🔄 حالة الطلبات</h3></div>
                    <table class="dash-table">
                        <tbody>
                            @foreach ($ordersPipeline as $p)
                                <tr>
                                    <td><span class="dash-badge dash-badge-ok">{{ \App\Domain\Reports\Actions\DashboardAction::statusLabel($p->status) }}</span></td>
                                    <td style="text-align:center;font-weight:700">{{ $p->cnt }}</td>
                                    <td class="num">{{ $fmtE($p->total_minor) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if (count($lowStock))
                <div class="dash-panel" style="border-color:var(--red)">
                    <div class="dash-panel-head" style="background:#fff5f5">
                        <h3>⚠️ مخزون منخفض</h3><a href="{{ $links['inventory'] }}">المخزون ←</a>
                    </div>
                    <table class="dash-table">
                        <tbody>
                            @foreach ($lowStock as $s)
                                <tr>
                                    <td style="font-weight:600">{{ $s['product'] }}</td>
                                    <td style="text-align:center;color:var(--red);font-weight:700">{{ number_format($s['available'], 0) }}</td>
                                    <td style="font-size:.7rem;color:var(--ink-soft)">حد: {{ number_format($s['reorder'], 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if (! $isCashier && count($deadStock))
                <div class="dash-panel">
                    <div class="dash-panel-head"><h3>💤 مخزون راكد ({{ $deadStockCount }})</h3></div>
                    <table class="dash-table">
                        <tbody>
                            @foreach ($deadStock as $r)
                                <tr>
                                    <td style="font-weight:600">{{ $r->product_name }}</td>
                                    <td class="num" style="color:var(--red)">{{ $fmtE($r->value_minor) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if (count($openSessions))
                <div class="dash-panel">
                    <div class="dash-panel-head"><h3>💵 شيفتات مفتوحة</h3></div>
                    <table class="dash-table">
                        <tbody>
                            @foreach ($openSessions as $s)
                                <tr>
                                    <td style="font-weight:600">{{ $s['cashier'] }}</td>
                                    <td>{{ $s['register'] }}</td>
                                    <td style="text-align:center">{{ $s['orders'] }} طلب</td>
                                    <td class="num">{{ $fmtE($s['cash'] + $s['card']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if (! $isCashier && count($cashiers))
                <div class="dash-panel">
                    <div class="dash-panel-head"><h3>🧾 أداء الكاشيرين</h3><a href="{{ $links['reports'] }}">تفاصيل ←</a></div>
                    <table class="dash-table">
                        <thead><tr><th>الكاشير</th><th>طلبات</th><th>الفرق</th></tr></thead>
                        <tbody>
                            @foreach ($cashiers as $r)
                                <tr>
                                    <td style="font-weight:600">{{ $r->cashier_name }}</td>
                                    <td style="text-align:center">{{ $r->orders_count }}</td>
                                    <td class="num" style="color:{{ $r->total_variance_minor < 0 ? 'var(--red)' : 'var(--green)' }}">
                                        {{ $fmtE($r->total_variance_minor) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
</x-filament-panels::page>
