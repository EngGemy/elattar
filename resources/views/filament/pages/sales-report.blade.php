<x-filament-panels::page>
@php
    $fmt  = fn ($minor) => number_format(($minor ?? 0) / 100, 2);
    $fmtE = fn ($minor) => $fmt($minor) . ' ج.م';
    $pct  = fn ($v) => ($v >= 0 ? '+' : '') . number_format($v, 1) . '%';
    $c    = $summary['current'];
    $g    = $summary['growth'];
    $maxDaily = collect($dailyTrend)->max('net_minor') ?: 1;
    $maxRev   = collect($bestSellers)->max('revenue_minor') ?: 1;
    $deadValue = array_sum(array_column($deadStock, 'value_minor'));
    $channelLabels = ['pos' => 'نقطة البيع', 'online' => 'المتجر الإلكتروني'];
    $tabs = [
        'overview'       => ['icon' => '📊', 'label' => 'ملخص الأداء'],
        'daily'          => ['icon' => '📈', 'label' => 'المبيعات اليومية'],
        'products'       => ['icon' => '🏆', 'label' => 'أفضل المنتجات'],
        'profitability'  => ['icon' => '💰', 'label' => 'الربحية'],
        'inventory'      => ['icon' => '📦', 'label' => 'المخزون'],
        'cashiers'       => ['icon' => '🧾', 'label' => 'الكاشيرين'],
    ];
@endphp

<style>
    .rpt{font-family:'Segoe UI',Tahoma,sans-serif;--ink:#241a11;--ink-soft:#5a4630;--parchment:#f8f4ec;
        --parchment-2:#ede4d4;--gold:#b8892b;--gold-deep:#8a6115;--gold-light:#e8d5a8;
        --green:#128c3e;--red:#c0392b;--hair:rgba(36,26,17,.1);--shadow:0 4px 24px rgba(36,26,17,.08);
        --radius:14px}
    .rpt *{box-sizing:border-box}

    /* ── شريط الأدوات ── */
    .rpt-toolbar{display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;justify-content:space-between;
        background:linear-gradient(135deg,var(--ink) 0%,#3d2e1e 100%);border-radius:var(--radius);
        padding:20px 24px;margin-bottom:20px;color:#fff;box-shadow:var(--shadow)}
    .rpt-toolbar h2{font-family:Georgia,serif;font-size:1.35rem;font-weight:700;margin:0 0 4px}
    .rpt-toolbar .sub{font-size:.78rem;opacity:.65}
    .rpt-toolbar-actions{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
    .rpt-periods{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:16px}
    .rpt-chip{padding:6px 14px;border-radius:20px;border:1px solid var(--hair);background:#fff;
        font-size:.78rem;font-weight:600;color:var(--ink-soft);cursor:pointer;transition:.15s}
    .rpt-chip:hover{border-color:var(--gold);color:var(--gold-deep)}
    .rpt-chip.on{background:var(--ink);color:var(--parchment);border-color:var(--ink)}
    .rpt-dates{background:#fff;border:1px solid var(--hair);border-radius:var(--radius);padding:16px 20px;margin-bottom:20px}
    .rpt-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;
        font-size:.82rem;font-weight:700;cursor:pointer;border:none;transition:.15s;white-space:nowrap}
    .rpt-btn-gold{background:var(--gold);color:var(--ink)}
    .rpt-btn-gold:hover{background:var(--gold-deep);color:#fff}
    .rpt-btn-outline{background:transparent;border:1px solid rgba(255,255,255,.35);color:#fff}
    .rpt-btn-outline:hover{background:rgba(255,255,255,.1)}
    .rpt-btn-sm{padding:5px 12px;font-size:.75rem;border-radius:8px}
    .rpt-btn-green{background:#1a6b38;color:#fff}
    .rpt-btn-green:hover{background:var(--green)}

    /* ── تبويبات ── */
    .rpt-tabs{display:flex;gap:4px;background:var(--parchment-2);border-radius:12px;
        padding:5px;margin-bottom:20px;border:1px solid var(--hair);overflow-x:auto;flex-wrap:nowrap}
    .rpt-tab{flex-shrink:0;padding:9px 18px;border-radius:9px;font-size:.82rem;font-weight:600;color:var(--ink-soft);
        cursor:pointer;border:none;background:transparent;transition:.15s;display:flex;align-items:center;gap:6px}
    .rpt-tab:hover{color:var(--ink);background:rgba(255,255,255,.6)}
    .rpt-tab.on{background:#fff;color:var(--ink);box-shadow:0 2px 8px rgba(36,26,17,.1)}

    /* ── بطاقات KPI ── */
    .rpt-kpis{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;margin-bottom:24px}
    .rpt-kpi{background:#fff;border:1px solid var(--hair);border-radius:var(--radius);padding:18px 20px;
        position:relative;overflow:hidden;box-shadow:var(--shadow);transition:transform .15s}
    .rpt-kpi:hover{transform:translateY(-2px)}
    .rpt-kpi::before{content:'';position:absolute;top:0;right:0;left:0;height:3px;background:var(--gold)}
    .rpt-kpi .ico{font-size:1.4rem;margin-bottom:8px}
    .rpt-kpi .lbl{font-size:.72rem;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px}
    .rpt-kpi .val{font-size:1.5rem;font-weight:800;color:var(--ink);line-height:1.1}
    .rpt-kpi .sub{font-size:.75rem;margin-top:6px;font-weight:600}
    .rpt-kpi .sub.up{color:var(--green)} .rpt-kpi .sub.down{color:var(--red)}
    .rpt-kpi.dark{background:linear-gradient(135deg,var(--ink),#3d2e1e);border:none}
    .rpt-kpi.dark::before{background:var(--gold)}
    .rpt-kpi.dark .lbl{color:rgba(255,255,255,.55)} .rpt-kpi.dark .val{color:var(--gold-light)}
    .rpt-kpi.dark .sub{color:rgba(255,255,255,.7)}

    /* ── بطاقات القنوات ── */
    .rpt-channels{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;margin-bottom:24px}
    .rpt-channel{background:#fff;border:1px solid var(--hair);border-radius:var(--radius);padding:18px;box-shadow:var(--shadow)}
    .rpt-channel .ch-name{font-weight:700;font-size:.9rem;margin-bottom:10px;color:var(--ink)}
    .rpt-channel .ch-val{font-size:1.3rem;font-weight:800;color:var(--gold-deep)}
    .rpt-channel .ch-meta{font-size:.75rem;color:var(--ink-soft);margin-top:4px}

    /* ── رسم بياني CSS ── */
    .rpt-chart{background:#fff;border:1px solid var(--hair);border-radius:var(--radius);padding:20px;
        box-shadow:var(--shadow);margin-bottom:24px}
    .rpt-chart-title{font-weight:700;font-size:.95rem;margin-bottom:16px;color:var(--ink);
        display:flex;justify-content:space-between;align-items:center}
    .rpt-bars{display:flex;align-items:flex-end;gap:4px;height:140px;padding-top:8px}
    .rpt-bar-wrap{flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;min-width:0}
    .rpt-bar{width:100%;max-width:36px;border-radius:4px 4px 0 0;background:linear-gradient(180deg,var(--gold),var(--gold-deep));
        transition:height .4s ease;min-height:2px}
    .rpt-bar-lbl{font-size:.6rem;color:var(--ink-soft);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}

    /* ── جداول ── */
    .rpt-panel{background:#fff;border:1px solid var(--hair);border-radius:var(--radius);
        box-shadow:var(--shadow);margin-bottom:24px;overflow:hidden}
    .rpt-panel-head{display:flex;justify-content:space-between;align-items:center;
        padding:16px 20px;border-bottom:1px solid var(--hair);background:var(--parchment)}
    .rpt-panel-head h3{font-weight:700;font-size:.95rem;margin:0;color:var(--ink)}
    .rpt-panel-head .meta{font-size:.75rem;color:var(--ink-soft)}
    .rpt-table{width:100%;border-collapse:collapse;font-size:.82rem}
    .rpt-table thead tr{background:var(--ink);color:#fff}
    .rpt-table th{padding:10px 14px;font-weight:600;font-size:.72rem;letter-spacing:.03em;white-space:nowrap}
    .rpt-table td{padding:10px 14px;border-bottom:1px solid var(--hair);vertical-align:middle}
    .rpt-table tbody tr:hover{background:var(--parchment)}
    .rpt-table tbody tr:last-child td{border-bottom:none}
    .rpt-table .num{font-family:'Courier New',monospace;font-weight:600;text-align:left;direction:ltr}
    .rpt-table .rank{color:var(--ink-soft);font-weight:700;width:32px}
    .rpt-bar-inline{height:6px;background:var(--parchment-2);border-radius:3px;overflow:hidden;min-width:80px}
    .rpt-bar-inline div{height:100%;background:linear-gradient(90deg,var(--gold-deep),var(--gold));border-radius:3px}

    /* ── شارات ── */
    .rpt-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:700}
    .rpt-badge-ok{background:#e8f5e9;color:var(--green)}
    .rpt-badge-warn{background:#fff3e0;color:#e65100}
    .rpt-badge-danger{background:#fdecea;color:var(--red)}

    /* ── تحذير المخزون الراكد ── */
    .rpt-alert{border:2px solid var(--red);border-radius:var(--radius);padding:20px;
        background:linear-gradient(135deg,#fff5f5,#fff);margin-bottom:24px}
    .rpt-alert h4{color:var(--red);font-weight:800;margin:0 0 6px;font-size:.95rem}
    .rpt-dead-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;margin-top:14px}
    .rpt-dead-card{background:#fff;border:1px solid #f5c6c2;border-radius:10px;padding:12px;font-size:.78rem}
    .rpt-dead-card .name{font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .rpt-dead-card .val{color:var(--red);font-weight:700;margin-top:4px}

    /* ── شبكة عمودين ── */
    .rpt-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
    @media(max-width:900px){.rpt-grid-2{grid-template-columns:1fr}}
    .rpt-empty{padding:40px;text-align:center;color:var(--ink-soft);font-size:.88rem}

    /* تنسيق حقول Filament داخل الصفحة */
    .rpt-dates .grid{gap:12px!important}
    .rpt-date-input{width:100%;padding:10px 14px;border:1px solid var(--hair);border-radius:10px;
        font-size:.88rem;background:#fff;color:var(--ink)}
    .rpt-date-input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(184,137,43,.15)}

    /* إخفاء عنوان Filament المكرر */
    .fi-page > header,.fi-header-heading,.fi-page-header-heading{display:none!important}
</style>

<div class="rpt">

    {{-- ══ شريط الأدوات ══ --}}
    <div class="rpt-toolbar">
        <div>
            <h2>مركز التقارير</h2>
            <div class="sub">{{ $fromDate->format('d/m/Y') }} — {{ $toDate->format('d/m/Y') }}</div>
        </div>
        <div class="rpt-toolbar-actions">
            <button type="button" class="rpt-btn rpt-btn-outline" wire:click="export('all')">
                📥 تصدير شامل Excel
            </button>
            <button type="button" class="rpt-btn rpt-btn-gold" wire:click="export('summary')">
                📊 Excel الملخص
            </button>
        </div>
    </div>

    {{-- ══ فترات سريعة ══ --}}
    <div class="rpt-periods">
        @foreach (['today'=>'اليوم','week'=>'هذا الأسبوع','month'=>'هذا الشهر','quarter'=>'هذا الربع','year'=>'هذه السنة'] as $key => $label)
            <button type="button" class="rpt-chip" wire:click="setPeriod('{{ $key }}')">{{ $label }}</button>
        @endforeach
    </div>

    {{-- ══ فلتر التاريخ ══ --}}
    <div class="rpt-dates" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:end">
        <label style="display:flex;flex-direction:column;gap:4px">
            <span style="font-size:.78rem;font-weight:600;color:var(--ink-soft)">من تاريخ</span>
            <input type="date" wire:model.live="from" class="rpt-date-input">
        </label>
        <label style="display:flex;flex-direction:column;gap:4px">
            <span style="font-size:.78rem;font-weight:600;color:var(--ink-soft)">إلى تاريخ</span>
            <input type="date" wire:model.live="to" class="rpt-date-input">
        </label>
    </div>

    {{-- ══ التبويبات ══ --}}
    <div class="rpt-tabs">
        @foreach ($tabs as $key => $t)
            <button type="button"
                    class="rpt-tab {{ $tab === $key ? 'on' : '' }}"
                    wire:click="setTab('{{ $key }}')">
                <span>{{ $t['icon'] }}</span> {{ $t['label'] }}
            </button>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════
         تبويب ١: ملخص الأداء
    ══════════════════════════════════════════ --}}
    @if ($tab === 'overview')
        <div class="rpt-kpis">
            <div class="rpt-kpi dark">
                <div class="ico">💵</div>
                <div class="lbl">صافي المبيعات</div>
                <div class="val">{{ $fmtE($c['net_minor']) }}</div>
                <div class="sub {{ ($g['net'] ?? 0) >= 0 ? 'up' : 'down' }}">{{ $pct($g['net']) }} عن الفترة السابقة</div>
            </div>
            <div class="rpt-kpi">
                <div class="ico">📈</div>
                <div class="lbl">مجمل الربح</div>
                <div class="val">{{ $fmtE($c['profit_minor']) }}</div>
                <div class="sub {{ ($g['profit'] ?? 0) >= 0 ? 'up' : 'down' }}">{{ $c['gp_percent'] }}% هامش · {{ $pct($g['profit']) }}</div>
            </div>
            <div class="rpt-kpi">
                <div class="ico">🛒</div>
                <div class="lbl">عدد الطلبات</div>
                <div class="val">{{ number_format($c['orders_count']) }}</div>
                <div class="sub {{ ($g['orders'] ?? 0) >= 0 ? 'up' : 'down' }}">{{ $pct($g['orders']) }}</div>
            </div>
            <div class="rpt-kpi">
                <div class="ico">🧾</div>
                <div class="lbl">متوسط الطلب</div>
                <div class="val">{{ $fmtE($c['aov_minor']) }}</div>
                <div class="sub {{ ($g['aov'] ?? 0) >= 0 ? 'up' : 'down' }}">{{ $pct($g['aov']) }}</div>
            </div>
            <div class="rpt-kpi">
                <div class="ico">📦</div>
                <div class="lbl">تكلفة البضاعة</div>
                <div class="val">{{ $fmtE($c['cogs_minor']) }}</div>
                <div class="sub">خصومات: {{ $fmtE($c['discount_minor']) }}</div>
            </div>
        </div>

        @if (count($channels))
            <div class="rpt-channels">
                @foreach ($channels as $ch)
                    <div class="rpt-channel">
                        <div class="ch-name">{{ $channelLabels[$ch->channel] ?? $ch->channel }}</div>
                        <div class="ch-val">{{ $fmtE($ch->net_minor) }}</div>
                        <div class="ch-meta">{{ number_format($ch->orders_count) }} طلب · ربح {{ $fmtE($ch->profit_minor) }} · {{ $ch->gp_percent }}% هامش</div>
                    </div>
                @endforeach
            </div>
        @endif

        @if (count($dailyTrend))
            <div class="rpt-chart">
                <div class="rpt-chart-title">
                    <span>اتجاه المبيعات اليومي</span>
                    <button type="button" class="rpt-btn rpt-btn-sm rpt-btn-green" wire:click="export('daily')">📥 Excel</button>
                </div>
                <div class="rpt-bars">
                    @foreach ($dailyTrend as $d)
                        <div class="rpt-bar-wrap" title="{{ $d->date }}: {{ $fmtE($d->net_minor) }}">
                            <div class="rpt-bar" style="height:{{ max(2, round($d->net_minor / $maxDaily * 120)) }}px"></div>
                            <div class="rpt-bar-lbl">{{ \Carbon\Carbon::parse($d->date)->format('d/m') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if (count($deadStock))
            <div class="rpt-alert">
                <h4>⚠️ مخزون راكد — {{ count($deadStock) }} صنفًا · رأس مال مجمّد: {{ $fmtE($deadValue) }}
                    ({{ $totalValue > 0 ? round($deadValue / $totalValue * 100, 1) : 0 }}% من المخزون)</h4>
                <div style="font-size:.78rem;color:var(--ink-soft)">لم يتحرّك منذ ٩٠ يومًا — يُنصح بالتصفية أو إيقاف إعادة الطلب</div>
                <div class="rpt-dead-grid">
                    @foreach (array_slice($deadStock, 0, 8) as $r)
                        <div class="rpt-dead-card">
                            <div class="name">{{ $r->product_name }}</div>
                            <div class="val">{{ $fmtE($r->value_minor) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    {{-- ══════════════════════════════════════════
         تبويب ٢: المبيعات اليومية
    ══════════════════════════════════════════ --}}
    @if ($tab === 'daily')
        <div class="rpt-panel">
            <div class="rpt-panel-head">
                <h3>تفصيل المبيعات اليومي</h3>
                <button type="button" class="rpt-btn rpt-btn-sm rpt-btn-green" wire:click="export('daily')">📥 Excel</button>
            </div>
            <div style="overflow-x:auto">
                <table class="rpt-table">
                    <thead>
                        <tr>
                            <th style="text-align:right">التاريخ</th>
                            <th style="text-align:center">الطلبات</th>
                            <th style="text-align:left">صافي المبيعات</th>
                            <th style="text-align:left">الربح</th>
                            <th style="text-align:left">ت. البضاعة</th>
                            <th style="text-align:left">الخصومات</th>
                            <th style="text-align:center">هامش %</th>
                            <th style="width:12%">النسبة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dailyTrend as $d)
                            @php $gp = $d->net_minor > 0 ? round($d->profit_minor / $d->net_minor * 100, 1) : 0; @endphp
                            <tr>
                                <td style="font-weight:600">{{ \Carbon\Carbon::parse($d->date)->format('d/m/Y') }}</td>
                                <td style="text-align:center">{{ number_format($d->orders_count) }}</td>
                                <td class="num">{{ $fmtE($d->net_minor) }}</td>
                                <td class="num" style="color:var(--green)">{{ $fmtE($d->profit_minor) }}</td>
                                <td class="num">{{ $fmtE($d->cogs_minor) }}</td>
                                <td class="num">{{ $fmtE($d->discount_minor) }}</td>
                                <td style="text-align:center;font-weight:700">{{ $gp }}%</td>
                                <td>
                                    <div class="rpt-bar-inline"><div style="width:{{ round($d->net_minor / $maxDaily * 100) }}%"></div></div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="rpt-empty">لا بيانات في هذه الفترة</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════
         تبويب ٣: أفضل المنتجات
    ══════════════════════════════════════════ --}}
    @if ($tab === 'products')
        <div class="rpt-panel">
            <div class="rpt-panel-head">
                <h3>الأصناف الأكثر إيرادًا <span class="meta">— {{ count($bestSellers) }} صنف</span></h3>
                <button type="button" class="rpt-btn rpt-btn-sm rpt-btn-green" wire:click="export('products')">📥 Excel</button>
            </div>
            <div style="overflow-x:auto">
                <table class="rpt-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th style="text-align:right">الصنف</th>
                            <th style="text-align:right">SKU</th>
                            <th style="text-align:right">التصنيف</th>
                            <th style="text-align:center">الكمية</th>
                            <th style="text-align:left">الإيراد</th>
                            <th style="text-align:left">الربح</th>
                            <th style="text-align:center">GP%</th>
                            <th style="width:14%">مساهمة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bestSellers as $i => $r)
                            <tr>
                                <td class="rank">{{ $i + 1 }}</td>
                                <td style="font-weight:600">{{ $r->product_name }}</td>
                                <td style="font-family:monospace;font-size:.75rem;color:var(--ink-soft)">{{ $r->sku }}</td>
                                <td><span class="rpt-badge rpt-badge-ok">{{ $r->category_name }}</span></td>
                                <td style="text-align:center">{{ number_format($r->qty_sold, 0) }}</td>
                                <td class="num">{{ $fmtE($r->revenue_minor) }}</td>
                                <td class="num" style="color:var(--green)">{{ $fmtE($r->profit_minor) }}</td>
                                <td style="text-align:center">
                                    <span class="rpt-badge {{ $r->gp_percent < 20 ? 'rpt-badge-danger' : 'rpt-badge-ok' }}">{{ $r->gp_percent }}%</span>
                                </td>
                                <td><div class="rpt-bar-inline"><div style="width:{{ round($r->revenue_minor / $maxRev * 100) }}%"></div></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="rpt-empty">لا بيانات في هذه الفترة</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════
         تبويب ٤: الربحية
    ══════════════════════════════════════════ --}}
    @if ($tab === 'profitability')
        <div class="rpt-grid-2">
            <div class="rpt-panel">
                <div class="rpt-panel-head">
                    <h3>الربحية بالتصنيف</h3>
                    <button type="button" class="rpt-btn rpt-btn-sm rpt-btn-green" wire:click="export('categories')">📥 Excel</button>
                </div>
                <div style="overflow-x:auto">
                    <table class="rpt-table">
                        <thead>
                            <tr>
                                <th style="text-align:right">التصنيف</th>
                                <th style="text-align:left">الإيراد</th>
                                <th style="text-align:left">ت. البضاعة</th>
                                <th style="text-align:left">الربح</th>
                                <th style="text-align:center">GP%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($byCategory as $r)
                                <tr>
                                    <td style="font-weight:600">{{ $r->category_name }}</td>
                                    <td class="num">{{ $fmtE($r->revenue_minor) }}</td>
                                    <td class="num">{{ $fmtE($r->cogs_minor) }}</td>
                                    <td class="num" style="color:var(--green)">{{ $fmtE($r->profit_minor) }}</td>
                                    <td style="text-align:center">
                                        <span class="rpt-badge {{ $r->gp_percent < 20 ? 'rpt-badge-warn' : 'rpt-badge-ok' }}">{{ $r->gp_percent }}%</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="rpt-empty">لا بيانات</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rpt-panel">
                <div class="rpt-panel-head">
                    <h3>توزيع المبيعات حسب القناة</h3>
                    <button type="button" class="rpt-btn rpt-btn-sm rpt-btn-green" wire:click="export('channels')">📥 Excel</button>
                </div>
                <div style="overflow-x:auto">
                    <table class="rpt-table">
                        <thead>
                            <tr>
                                <th style="text-align:right">القناة</th>
                                <th style="text-align:center">الطلبات</th>
                                <th style="text-align:left">المبيعات</th>
                                <th style="text-align:left">الربح</th>
                                <th style="text-align:center">GP%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($channels as $ch)
                                <tr>
                                    <td style="font-weight:600">{{ $channelLabels[$ch->channel] ?? $ch->channel }}</td>
                                    <td style="text-align:center">{{ number_format($ch->orders_count) }}</td>
                                    <td class="num">{{ $fmtE($ch->net_minor) }}</td>
                                    <td class="num" style="color:var(--green)">{{ $fmtE($ch->profit_minor) }}</td>
                                    <td style="text-align:center">
                                        <span class="rpt-badge rpt-badge-ok">{{ $ch->gp_percent }}%</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="rpt-empty">لا بيانات</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════
         تبويب ٥: المخزون
    ══════════════════════════════════════════ --}}
    @if ($tab === 'inventory')
        <div class="rpt-kpis" style="margin-bottom:20px">
            <div class="rpt-kpi dark">
                <div class="ico">📦</div>
                <div class="lbl">القيمة الدفترية الإجمالية</div>
                <div class="val">{{ $fmtE($totalValue) }}</div>
            </div>
            <div class="rpt-kpi">
                <div class="ico">⚠️</div>
                <div class="lbl">مخزون راكد</div>
                <div class="val">{{ count($deadStock) }} صنف</div>
                <div class="sub down">{{ $fmtE($deadValue) }} مجمّد</div>
            </div>
            <div class="rpt-kpi">
                <div class="ico">✅</div>
                <div class="lbl">أصناف نشطة</div>
                <div class="val">{{ count($valuation) - count($deadStock) }}</div>
            </div>
        </div>

        <div class="rpt-panel">
            <div class="rpt-panel-head">
                <h3>تقييم المخزون <span class="meta">— {{ count($valuation) }} صنف</span></h3>
                <div style="display:flex;gap:6px">
                    <button type="button" class="rpt-btn rpt-btn-sm rpt-btn-green" wire:click="export('inventory')">📥 Excel</button>
                    @if (count($deadStock))
                        <button type="button" class="rpt-btn rpt-btn-sm" style="background:var(--red);color:#fff" wire:click="export('dead_stock')">📥 راكد</button>
                    @endif
                </div>
            </div>
            <div style="overflow-x:auto;max-height:520px;overflow-y:auto">
                <table class="rpt-table">
                    <thead style="position:sticky;top:0;z-index:1">
                        <tr>
                            <th style="text-align:right">الصنف</th>
                            <th style="text-align:right">المخزن</th>
                            <th style="text-align:center">الموجود</th>
                            <th style="text-align:center">المحجوز</th>
                            <th style="text-align:center">المتاح</th>
                            <th style="text-align:left">القيمة</th>
                            <th style="text-align:center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($valuation as $r)
                            <tr>
                                <td style="font-weight:600">{{ $r->product_name }}</td>
                                <td style="font-size:.75rem;color:var(--ink-soft)">{{ $r->warehouse_name }}</td>
                                <td style="text-align:center">{{ number_format($r->on_hand, 0) }}</td>
                                <td style="text-align:center;color:var(--ink-soft)">{{ number_format($r->reserved, 0) }}</td>
                                <td style="text-align:center;font-weight:700">{{ number_format($r->available, 0) }}</td>
                                <td class="num">{{ $fmtE($r->value_minor) }}</td>
                                <td style="text-align:center">
                                    @if ($r->is_dead_stock)
                                        <span class="rpt-badge rpt-badge-danger">راكد</span>
                                    @else
                                        <span class="rpt-badge rpt-badge-ok">نشط</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════
         تبويب ٦: الكاشيرين
    ══════════════════════════════════════════ --}}
    @if ($tab === 'cashiers')
        <div class="rpt-panel">
            <div class="rpt-panel-head">
                <h3>أداء الكاشيرين — فروق الصندوق</h3>
                <button type="button" class="rpt-btn rpt-btn-sm rpt-btn-green" wire:click="export('cashiers')">📥 Excel</button>
            </div>
            <div style="overflow-x:auto">
                <table class="rpt-table">
                    <thead>
                        <tr>
                            <th style="text-align:right">الكاشير</th>
                            <th style="text-align:center">الشيفتات</th>
                            <th style="text-align:center">الطلبات</th>
                            <th style="text-align:left">مبيعات نقدية</th>
                            <th style="text-align:left">مبيعات بطاقة</th>
                            <th style="text-align:left">صافي الفرق</th>
                            <th style="text-align:center">مرات العجز</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cashiers as $r)
                            <tr>
                                <td style="font-weight:600">{{ $r->cashier_name }}</td>
                                <td style="text-align:center">{{ $r->sessions_count }}</td>
                                <td style="text-align:center">{{ $r->orders_count }}</td>
                                <td class="num">{{ $fmtE($r->cash_sales_minor) }}</td>
                                <td class="num">{{ $fmtE($r->card_sales_minor) }}</td>
                                <td class="num" style="color:{{ $r->total_variance_minor < 0 ? 'var(--red)' : 'var(--green)' }};font-weight:700">
                                    {{ $fmtE($r->total_variance_minor) }}
                                </td>
                                <td style="text-align:center">
                                    @if ($r->shortage_count > 0)
                                        <span class="rpt-badge rpt-badge-danger">{{ $r->shortage_count }}</span>
                                    @else
                                        <span class="rpt-badge rpt-badge-ok">٠</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="rpt-empty">لا شيفتات مغلقة في هذه الفترة</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
</x-filament-panels::page>
