<x-filament-panels::page>
@php
    /** @var \App\Domain\Sales\Models\Order $order */
    $statusLabel = $order->status->label();
    $statusColor = $order->status->color();
    $payLabel = $order->payment_status->getLabel();
    $payColor = match ($order->payment_status->value) {
        'paid'                => 'ok',
        'partial'             => 'warn',
        'refunded', 'partially_refunded' => 'gray',
        default               => 'danger',
    };
    $progressWidth = $isFinal && ! $isCancelled && ! $isReturned
        ? '100%'
        : ($stepIndex >= 0 ? (($stepIndex / (count($steps) - 1)) * 100) . '%' : '0%');
    $statusMap = collect($steps)->keyBy('key');
    $history = $order->statusHistory->sortBy('created_at');
@endphp

<style>
.ov{font-family:'Segoe UI',Tahoma,sans-serif;--ink:#241a11;--ink-soft:#5a4630;--parchment:#f8f4ec;
    --parchment-2:#ede4d4;--gold:#b8892b;--gold-deep:#8a6115;--gold-light:#e8d5a8;
    --green:#128c3e;--red:#c0392b;--blue:#1a5276;--hair:rgba(36,26,17,.1);--shadow:0 4px 20px rgba(36,26,17,.07);--r:14px}
.ov *{box-sizing:border-box}
.fi-page>header .fi-header-heading{display:none!important}
.ov-hero{display:grid;grid-template-columns:1fr auto;gap:16px;align-items:center;
    background:linear-gradient(135deg,var(--ink),#3d2e1e);border-radius:var(--r);padding:22px 26px;
    color:#fff;margin-bottom:16px;box-shadow:var(--shadow)}
@media(max-width:800px){.ov-hero{grid-template-columns:1fr}}
.ov-hero h1{font-family:Georgia,serif;font-size:1.65rem;margin:0 0 6px;letter-spacing:.02em}
.ov-hero .meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px}
.ov-pill{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;
    font-size:.72rem;font-weight:700;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2)}
.ov-pill.gold{background:var(--gold);color:var(--ink);border:none}
.ov-total{text-align:left}
.ov-total .lbl{font-size:.7rem;opacity:.65;margin-bottom:2px}
.ov-total .amt{font-family:Georgia,serif;font-size:2rem;font-weight:800;color:var(--gold-light);line-height:1}
.ov-total .sub{font-size:.72rem;opacity:.6;margin-top:4px}

.ov-stepper{background:#fff;border:1px solid var(--hair);border-radius:var(--r);padding:20px 22px;
    margin-bottom:16px;box-shadow:var(--shadow)}
.ov-stepper-title{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;
    color:var(--ink-soft);margin-bottom:16px}
.ov-track{position:relative;display:flex;justify-content:space-between;align-items:flex-start;padding:0 4px}
.ov-track::before{content:'';position:absolute;top:18px;right:18px;left:18px;height:4px;
    background:var(--parchment-2);border-radius:4px;z-index:0}
.ov-track-fill{position:absolute;top:18px;right:18px;height:4px;border-radius:4px;z-index:1;
    background:linear-gradient(90deg,var(--gold),var(--green));transition:width .4s}
.ov-step{position:relative;z-index:2;display:flex;flex-direction:column;align-items:center;gap:6px;flex:1}
.ov-dot{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;
    font-size:.95rem;background:#fff;border:3px solid var(--parchment-2);transition:.2s}
.ov-step.done .ov-dot{background:var(--green);border-color:var(--green);color:#fff;font-size:.75rem;font-weight:800}
.ov-step.current .ov-dot{background:var(--gold);border-color:var(--gold-deep);box-shadow:0 0 0 4px rgba(184,137,43,.25)}
.ov-step-name{font-size:.68rem;font-weight:700;color:var(--ink-soft);text-align:center;max-width:64px}
.ov-step.done .ov-step-name,.ov-step.current .ov-step-name{color:var(--ink)}
.ov-alert{padding:14px 18px;border-radius:10px;margin-bottom:16px;font-size:.82rem;font-weight:600;display:flex;align-items:center;gap:10px}
.ov-alert.cancel{background:#fdecea;color:var(--red);border:1px solid #f5c6c6}
.ov-alert.return{background:#fff3e0;color:#e65100;border:1px solid #ffe0b2}
.ov-next{background:linear-gradient(135deg,#fff8e6,#fef3cd);border:1px solid var(--gold);border-radius:10px;
    padding:14px 18px;margin-bottom:16px;display:flex;gap:12px;align-items:flex-start}
.ov-next .icon{font-size:1.4rem;line-height:1}
.ov-next .label{font-size:.88rem;font-weight:800;color:var(--ink);margin-bottom:2px}
.ov-next .hint{font-size:.75rem;color:var(--ink-soft)}

.ov-grid{display:grid;grid-template-columns:1.4fr 1fr;gap:16px;margin-bottom:16px}
@media(max-width:1100px){.ov-grid{grid-template-columns:1fr}}
.ov-panel{background:#fff;border:1px solid var(--hair);border-radius:var(--r);box-shadow:var(--shadow);overflow:hidden;margin-bottom:16px}
.ov-panel-head{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;
    background:var(--parchment);border-bottom:1px solid var(--hair)}
.ov-panel-head h3{margin:0;font-size:.88rem;font-weight:700;color:var(--ink)}
.ov-panel-body{padding:16px}

.ov-kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px}
@media(max-width:700px){.ov-kpis{grid-template-columns:1fr}}
.ov-kpi{background:#fff;border:1px solid var(--hair);border-radius:var(--r);padding:14px 16px;
    box-shadow:var(--shadow);position:relative;overflow:hidden}
.ov-kpi::after{content:'';position:absolute;top:0;right:0;left:0;height:3px}
.ov-kpi.pay::after{background:var(--green)}
.ov-kpi.due::after{background:var(--red)}
.ov-kpi.profit::after{background:var(--gold)}
.ov-kpi .lbl{font-size:.68rem;color:var(--ink-soft);margin-bottom:4px}
.ov-kpi .val{font-size:1.2rem;font-weight:800;color:var(--ink)}
.ov-kpi .val.ok{color:var(--green)} .ov-kpi .val.warn{color:#e65100} .ov-kpi .val.bad{color:var(--red)}

.ov-pay-bar{margin-top:12px}
.ov-pay-bar .bar{height:10px;background:var(--parchment-2);border-radius:6px;overflow:hidden}
.ov-pay-bar .fill{height:100%;border-radius:6px;background:linear-gradient(90deg,var(--green),#2ecc71);transition:width .4s}
.ov-pay-bar .labels{display:flex;justify-content:space-between;font-size:.7rem;color:var(--ink-soft);margin-top:6px}

.ov-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:600px){.ov-info-grid{grid-template-columns:1fr}}
.ov-info-item .k{font-size:.68rem;color:var(--ink-soft);margin-bottom:2px}
.ov-info-item .v{font-size:.82rem;font-weight:600;color:var(--ink)}

.ov-table{width:100%;border-collapse:collapse;font-size:.78rem}
.ov-table th{padding:9px 14px;background:var(--ink);color:#fff;font-size:.68rem;font-weight:600;text-align:right}
.ov-table td{padding:10px 14px;border-bottom:1px solid var(--hair);vertical-align:middle}
.ov-table tr:last-child td{border-bottom:none}
.ov-table tr:hover td{background:var(--parchment)}
.ov-table .num{font-family:monospace;direction:ltr;text-align:left;font-weight:600}
.ov-table .total-row td{background:var(--parchment);font-weight:800;border-top:2px solid var(--gold)}

.ov-totals{display:flex;flex-direction:column;gap:8px}
.ov-total-line{display:flex;justify-content:space-between;font-size:.8rem;padding:4px 0;border-bottom:1px dashed var(--hair)}
.ov-total-line:last-child{border-bottom:none;padding-top:8px;margin-top:4px;border-top:2px solid var(--gold);font-size:1rem;font-weight:800}
.ov-total-line.discount{color:var(--red)}
.ov-total-line.paid{color:var(--green)}
.ov-total-line.due{color:var(--red);font-weight:700}

.ov-timeline{position:relative;padding-right:20px}
.ov-timeline::before{content:'';position:absolute;right:6px;top:4px;bottom:4px;width:2px;background:var(--parchment-2)}
.ov-tl{position:relative;padding:0 0 18px 0}
.ov-tl:last-child{padding-bottom:0}
.ov-tl-dot{position:absolute;right:-20px;top:4px;width:12px;height:12px;border-radius:50%;
    background:var(--gold);border:2px solid #fff;box-shadow:0 0 0 2px var(--gold-light)}
.ov-tl-time{font-size:.68rem;color:var(--ink-soft);margin-bottom:2px}
.ov-tl-label{font-size:.82rem;font-weight:700;color:var(--ink)}
.ov-tl-note{font-size:.72rem;color:var(--ink-soft);margin-top:2px}
.ov-tl-user{font-size:.68rem;color:var(--gold-deep);margin-top:2px}

.ov-badge{display:inline-block;padding:3px 10px;border-radius:12px;font-size:.68rem;font-weight:700}
.ov-badge-ok{background:#e8f5e9;color:var(--green)}
.ov-badge-warn{background:#fff3e0;color:#e65100}
.ov-badge-danger{background:#fdecea;color:var(--red)}
.ov-badge-info{background:#e3f2fd;color:var(--blue)}
.ov-badge-gray{background:#f0ebe3;color:var(--ink-soft)}

.ov-donut-wrap{display:flex;align-items:center;gap:20px;padding:8px 0}
.ov-donut{position:relative;width:90px;height:90px;flex-shrink:0}
.ov-donut svg{transform:rotate(-90deg)}
.ov-donut-center{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
    font-size:.85rem;font-weight:800;color:var(--ink)}
.ov-donut-legend{flex:1;font-size:.78rem}
.ov-donut-legend .row{display:flex;align-items:center;gap:8px;margin-bottom:6px}
.ov-donut-legend .dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}

.ov-empty{padding:20px;text-align:center;color:var(--ink-soft);font-size:.8rem}
.ov-notes{background:var(--parchment);border-radius:8px;padding:10px 12px;font-size:.78rem;color:var(--ink-soft);margin-top:10px}
.ov-collect-status{display:flex;align-items:center;gap:14px;padding:14px 16px;border-radius:10px;margin-bottom:14px;border:1px solid var(--hair)}
.ov-collect-status.ok{background:#e8f5e9;border-color:#a5d6a7}
.ov-collect-status.warn{background:#fff8e1;border-color:#ffe082}
.ov-collect-status.bad{background:#fdecea;border-color:#f5c6c6}
.ov-collect-status.gray{background:#f5f5f5;border-color:#e0e0e0}
.ov-collect-status .cs-icon{font-size:2rem;line-height:1}
.ov-collect-status .cs-title{font-size:.92rem;font-weight:800;color:var(--ink)}
.ov-collect-status .cs-sub{font-size:.74rem;color:var(--ink-soft);margin-top:2px}
.ov-actions{display:flex;flex-wrap:wrap;gap:8px;margin:14px 0}
.ov-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:9px;
    font-size:.76rem;font-weight:700;text-decoration:none;cursor:pointer;border:none;transition:.15s}
.ov-btn-outline{background:#fff;border:1px solid var(--hair);color:var(--ink)}
.ov-btn-outline:hover{border-color:var(--gold);color:var(--gold-deep)}
.ov-btn-gold{background:var(--gold);color:var(--ink)}
.ov-btn-gold:hover{background:var(--gold-deep);color:#fff}
.ov-btn-green{background:var(--green);color:#fff}
.ov-btn-green:hover{filter:brightness(1.05)}
.ov-notes-box{margin-top:14px;padding-top:14px;border-top:1px dashed var(--hair)}
.ov-notes-box label{display:block;font-size:.72rem;font-weight:700;color:var(--ink-soft);margin-bottom:6px}
.ov-notes-box textarea{width:100%;min-height:72px;padding:10px 12px;border:1px solid var(--hair);
    border-radius:9px;font-size:.8rem;font-family:inherit;resize:vertical}
.ov-notes-box textarea:focus{outline:none;border-color:var(--gold)}
.ov-notes-foot{display:flex;justify-content:space-between;align-items:center;margin-top:8px;gap:8px}
.ov-notes-foot .hint{font-size:.68rem;color:var(--ink-soft)}
</style>

<div class="ov">

    {{-- رأس الطلب --}}
    <div class="ov-hero">
        <div>
            <h1>{{ $order->number }}</h1>
            <div style="font-size:.8rem;opacity:.7">
                {{ $order->placed_at?->format('d/m/Y — H:i') ?? '—' }}
                @if($order->warehouse)
                    · {{ $order->warehouse->name }}
                @endif
            </div>
            <div class="meta">
                <span class="ov-pill gold">{{ $channelLabel }}</span>
                <span class="ov-pill">{{ $statusLabel }}</span>
                <span class="ov-pill">{{ $order->paymentMethodLabel() }}</span>
                <span class="ov-pill">{{ $payLabel }}</span>
            </div>
        </div>
        <div class="ov-total">
            <div class="lbl">الإجمالي النهائي</div>
            <div class="amt">{{ $order->total_minor->format() }}</div>
            @if($order->balanceDue()->isPositive())
                <div class="sub">متبقي: {{ $order->balanceDue()->format() }}</div>
            @else
                <div class="sub" style="color:#7dcea0">✓ مسدَّد بالكامل</div>
            @endif
        </div>
    </div>

    @if($nextAction)
        <div class="ov-next">
            <span class="icon">{{ $nextAction['icon'] }}</span>
            <div>
                <div class="label">{{ $nextAction['label'] }}</div>
                <div class="hint">{{ $nextAction['hint'] }}</div>
            </div>
        </div>
    @endif

    @if($isCancelled)
        <div class="ov-alert cancel">❌ تم إلغاء هذا الطلب — لا يمكن متابعة سير العمل</div>
    @elseif($isReturned)
        <div class="ov-alert return">↩ تم إرجاع هذا الطلب</div>
    @else
        {{-- مسار الحالة --}}
        <div class="ov-stepper">
            <div class="ov-stepper-title">📍 مسار تنفيذ الطلب</div>
            <div class="ov-track">
                <div class="ov-track-fill" style="width:calc({{ $progressWidth }} - 36px)"></div>
                @foreach($steps as $i => $step)
                    @php
                        $done = $i < $stepIndex;
                        $current = $i === $stepIndex;
                    @endphp
                    <div class="ov-step {{ $done ? 'done' : ($current ? 'current' : '') }}">
                        <div class="ov-dot">
                            @if($done) ✓ @else {{ $step['icon'] }} @endif
                        </div>
                        <span class="ov-step-name">{{ $step['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- بطاقات مالية سريعة --}}
    <div class="ov-kpis">
        <div class="ov-kpi pay">
            <div class="lbl">💰 المدفوع</div>
            <div class="val ok">{{ $order->paid_minor->format() }}</div>
            <div class="ov-pay-bar">
                <div class="bar"><div class="fill" style="width:{{ $paidPct }}%"></div></div>
                <div class="labels"><span>{{ $paidPct }}% محصَّل</span><span>{{ $order->total_minor->format() }}</span></div>
            </div>
        </div>
        <div class="ov-kpi due">
            <div class="lbl">⏳ المتبقي</div>
            <div class="val {{ $order->balanceDue()->isPositive() ? 'bad' : 'ok' }}">
                {{ $order->balanceDue()->format() }}
            </div>
            <div style="font-size:.7rem;color:var(--ink-soft);margin-top:6px">
                @if($order->isPaid()) لا يوجد مبلغ مستحق @else يتطلّب تحصيل @endif
            </div>
        </div>
        @if(! $isCashier)
            <div class="ov-kpi profit">
                <div class="lbl">📈 مجمل الربح</div>
                <div class="val {{ $order->grossMarginPercent() < 15 ? 'bad' : 'ok' }}">
                    {{ $order->grossProfit()->format() }}
                </div>
                <div style="font-size:.7rem;color:var(--ink-soft);margin-top:6px">
                    هامش {{ $order->grossMarginPercent() }}% · تكلفة {{ $order->cogs_minor->format() }}
                </div>
            </div>
        @else
            <div class="ov-kpi">
                <div class="lbl">🧾 عدد الأصناف</div>
                <div class="val">{{ $order->lines->count() }}</div>
                <div style="font-size:.7rem;color:var(--ink-soft);margin-top:6px">
                    {{ $order->lines->sum('qty') }} وحدة إجمالاً
                </div>
            </div>
        @endif
    </div>

    <div class="ov-grid">
        {{-- العميل والشحن --}}
        <div>
            <div class="ov-panel">
                <div class="ov-panel-head"><h3>👤 العميل والتوصيل</h3></div>
                <div class="ov-panel-body">
                    <div class="ov-info-grid">
                        <div class="ov-info-item">
                            <div class="k">العميل</div>
                            <div class="v">{{ $order->customer?->name ?? 'عميل عابر' }}</div>
                        </div>
                        <div class="ov-info-item">
                            <div class="k">الهاتف</div>
                            <div class="v" style="direction:ltr;text-align:right">
                                {{ $order->customer?->phone ?? ($addr['phone'] ?? '—') }}
                            </div>
                        </div>
                        @if(! empty($addr['city']) || ! empty($addr['address']))
                            <div class="ov-info-item" style="grid-column:1/-1">
                                <div class="k">عنوان الشحن</div>
                                <div class="v">
                                    {{ collect([$addr['address'] ?? null, $addr['city'] ?? null, $addr['governorate'] ?? null])->filter()->implode('، ') ?: '—' }}
                                </div>
                            </div>
                        @endif
                        @if($order->shipping_carrier || $order->tracking_number)
                            <div class="ov-info-item">
                                <div class="k">شركة الشحن</div>
                                <div class="v">{{ $order->shipping_carrier ?? '—' }}</div>
                            </div>
                            <div class="ov-info-item">
                                <div class="k">رقم التتبّع</div>
                                <div class="v" style="font-family:monospace">{{ $order->tracking_number ?? '—' }}</div>
                            </div>
                        @endif
                        @if(in_array($addr['payment_method'] ?? '', ['instapay', 'vodafone_cash'], true))
                            <div class="ov-info-item">
                                <div class="k">رقم التحويل</div>
                                <div class="v" style="font-family:monospace">
                                    {{ \App\Support\StorefrontCheckout::paymentNumber((string) ($addr['payment_method'] ?? '')) ?? '—' }}
                                </div>
                            </div>
                        @endif
                    </div>
                    @if($order->notes)
                        <div class="ov-notes">
                            <div>📝 ملاحظات العميل: {{ $order->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- الأصناف --}}
            <div class="ov-panel">
                <div class="ov-panel-head">
                    <h3>🛍️ الأصناف ({{ $order->lines->count() }})</h3>
                    <span style="font-size:.75rem;color:var(--ink-soft)">{{ $order->subtotal_minor->format() }} قبل الخصم</span>
                </div>
                <table class="ov-table">
                    <thead>
                        <tr>
                            <th>الصنف</th>
                            <th>الكمية</th>
                            <th>سعر الوحدة</th>
                            <th>الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->lines as $line)
                            <tr>
                                <td>
                                    <strong>{{ $line->name_snapshot }}</strong>
                                    @if($line->sku_snapshot)
                                        <div style="font-size:.65rem;color:var(--ink-soft);font-family:monospace">{{ $line->sku_snapshot }}</div>
                                    @endif
                                </td>
                                <td class="num">{{ $line->quantity()->format() }}</td>
                                <td class="num">{{ $line->unit_price_minor->format() }}</td>
                                <td class="num" style="font-weight:800">{{ $line->line_total_minor->format() }}</td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="3">إجمالي الأصناف</td>
                            <td class="num">{{ $order->subtotal_minor->format() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- الجانب الأيمن: حسابات + دفعات + سجل --}}
        <div>
            {{-- التحصيل والدفع --}}
            <div class="ov-panel">
                <div class="ov-panel-head">
                    <h3>💰 التحصيل والدفع</h3>
                    <span class="ov-badge ov-badge-{{ $payColor }}">{{ $payLabel }}</span>
                </div>
                <div class="ov-panel-body">
                    <div class="ov-collect-status {{ $collection['tone'] }}">
                        <span class="cs-icon">{{ $collection['icon'] }}</span>
                        <div>
                            <div class="cs-title">{{ $collection['title'] }}</div>
                            <div class="cs-sub">{{ $collection['subtitle'] }}</div>
                        </div>
                    </div>

                    <div class="ov-info-grid" style="margin-bottom:12px">
                        <div class="ov-info-item">
                            <div class="k">طريقة الدفع</div>
                            <div class="v">{{ $order->paymentMethodLabel() }}</div>
                        </div>
                        <div class="ov-info-item">
                            <div class="k">حالة السداد</div>
                            <div class="v">
                                <span class="ov-badge ov-badge-{{ $payColor }}">{{ $payLabel }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="ov-actions">
                        <a href="{{ $invoiceUrl }}" target="_blank" class="ov-btn ov-btn-outline">📄 عرض الفاتورة</a>
                        <a href="{{ $invoicePrintUrl }}" target="_blank" class="ov-btn ov-btn-gold">🖨️ طباعة الفاتورة</a>
                        @if($canConfirmPayment)
                            <button type="button" class="ov-btn ov-btn-green" wire:click="confirmPayment" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="confirmPayment">✅ تأكيد التحصيل</span>
                                <span wire:loading wire:target="confirmPayment">جاري التسجيل…</span>
                            </button>
                        @endif
                    </div>

                    <div class="ov-donut-wrap">
                        @php
                            $circ = 2 * 3.14159 * 38;
                            $paidArc = $circ * ($paidPct / 100);
                        @endphp
                        <div class="ov-donut">
                            <svg width="90" height="90" viewBox="0 0 90 90">
                                <circle cx="45" cy="45" r="38" fill="none" stroke="#ede4d4" stroke-width="8"/>
                                <circle cx="45" cy="45" r="38" fill="none" stroke="#128c3e" stroke-width="8"
                                    stroke-dasharray="{{ $paidArc }} {{ $circ - $paidArc }}" stroke-linecap="round"/>
                            </svg>
                            <div class="ov-donut-center">{{ $paidPct }}%</div>
                        </div>
                        <div class="ov-donut-legend">
                            <div class="row"><span class="dot" style="background:var(--green)"></span> مدفوع: {{ $order->paid_minor->format() }}</div>
                            <div class="row"><span class="dot" style="background:var(--parchment-2)"></span> متبقي: {{ $order->balanceDue()->format() }}</div>
                            <div class="row"><span class="dot" style="background:var(--gold)"></span> إجمالي: {{ $order->total_minor->format() }}</div>
                        </div>
                    </div>
                    <div class="ov-totals" style="margin-top:12px">
                        <div class="ov-total-line"><span>المجموع الفرعي</span><span>{{ $order->subtotal_minor->format() }}</span></div>
                        @if($order->discount_minor->isPositive())
                            <div class="ov-total-line discount"><span>الخصم</span><span>− {{ $order->discount_minor->format() }}</span></div>
                        @endif
                        @if($order->tax_minor->isPositive())
                            <div class="ov-total-line"><span>ضريبة القيمة المضافة</span><span>{{ $order->tax_minor->format() }}</span></div>
                        @endif
                        @if($order->shipping_minor->isPositive())
                            <div class="ov-total-line"><span>الشحن</span><span>{{ $order->shipping_minor->format() }}</span></div>
                        @endif
                        <div class="ov-total-line paid"><span>المدفوع</span><span>{{ $order->paid_minor->format() }}</span></div>
                        @if($order->balanceDue()->isPositive())
                            <div class="ov-total-line due"><span>المتبقي</span><span>{{ $order->balanceDue()->format() }}</span></div>
                        @endif
                        <div class="ov-total-line"><span>الإجمالي النهائي</span><span>{{ $order->total_minor->format() }}</span></div>
                    </div>

                    <div class="ov-notes-box">
                        <label for="internal-notes">🔒 ملاحظات ذاتية (داخلية — لا يراها العميل)</label>
                        <textarea id="internal-notes" wire:model="internalNotes" placeholder="أضف ملاحظة للفريق: موعد التسليم، تعليمات خاصة، متابعة التحويل…"></textarea>
                        <div class="ov-notes-foot">
                            <span class="hint">تُحفظ مع الطلب ولا تظهر للعميل</span>
                            <button type="button" class="ov-btn ov-btn-gold" wire:click="saveInternalNotes" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="saveInternalNotes">💾 حفظ الملاحظات</span>
                                <span wire:loading wire:target="saveInternalNotes">جاري الحفظ…</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- نقاط الدفع (POS) --}}
            @if($order->tenders->isNotEmpty())
                <div class="ov-panel">
                    <div class="ov-panel-head"><h3>💳 نقاط الدفع</h3></div>
                    <table class="ov-table">
                        <thead>
                            <tr>
                                <th>الطريقة</th>
                                <th>المبلغ</th>
                                <th>المدفوع</th>
                                <th>الباقي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->tenders as $tender)
                                <tr>
                                    <td>{{ $tender->method->getLabel() }}</td>
                                    <td class="num">{{ $tender->amount_minor->format() }}</td>
                                    <td class="num">{{ $tender->tendered_minor?->format() ?? '—' }}</td>
                                    <td class="num" style="color:var(--green)">{{ $tender->change_minor?->format() ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($order->channel === \App\Domain\Shared\Enums\SalesChannel::Pos)
                        <div style="padding:10px 16px;background:var(--parchment);font-size:.78rem;display:flex;justify-content:space-between">
                            <span>إجمالي المدفوع من العميل</span>
                            <strong>{{ $order->totalTendered()->format() }}</strong>
                        </div>
                        @if($order->totalChange()->isPositive())
                            <div style="padding:8px 16px;font-size:.78rem;display:flex;justify-content:space-between;color:var(--green)">
                                <span>الباقي للعميل</span>
                                <strong>{{ $order->totalChange()->format() }}</strong>
                            </div>
                        @endif
                    @endif
                </div>
            @endif

            {{-- الخط الزمني --}}
            <div class="ov-panel">
                <div class="ov-panel-head"><h3>🕐 سجل الحالة</h3></div>
                <div class="ov-panel-body">
                    @if($history->isNotEmpty())
                        <div class="ov-timeline">
                            @foreach($history as $event)
                                @php
                                    $evStep = $statusMap->get($event->to_status);
                                    $evLabel = $evStep['label'] ?? $event->to_status;
                                @endphp
                                <div class="ov-tl">
                                    <div class="ov-tl-dot"></div>
                                    <div class="ov-tl-time">{{ $event->created_at?->format('d/m/Y H:i') }}</div>
                                    <div class="ov-tl-label">{{ $evLabel }}</div>
                                    @if($event->note)
                                        <div class="ov-tl-note">{{ $event->note }}</div>
                                    @endif
                                    <div class="ov-tl-user">{{ $event->user?->name ?? 'النظام' }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="ov-empty">لا توجد سجلات حالة بعد</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
</x-filament-panels::page>
