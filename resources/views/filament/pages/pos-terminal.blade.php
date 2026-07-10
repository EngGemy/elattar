<x-filament-panels::page>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=El+Messiri:wght@400;500;600;700&family=Reem+Kufi:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    .fi-header,.fi-topbar,.fi-sidebar,.fi-sidebar-close-overlay,.fi-breadcrumbs,.fi-page>header,.fi-page-header{display:none!important}
    .fi-main{padding:0!important;max-width:100%!important}
    .fi-page{padding:0!important;gap:0!important}
    .fi-main-ctn{margin-inline-start:0!important}
    .fi-page>section{padding:0!important}
    [x-cloak]{display:none!important}

    .pos{
        --ink:#241a11;--ink-soft:#5a4630;--parchment:#f1e9d8;--parchment-2:#e7dabf;
        --gold:#b8892b;--gold-deep:#8a6115;--clay:#9c4a2a;--olive:#697038;--green:#128c3e;
        --card:#fbf7ec;--hair:#cbb890;--shadow:0 12px 30px -20px rgba(36,26,17,.45);
        direction:rtl;font-family:'El Messiri',sans-serif;color:var(--ink);
        background:radial-gradient(900px 450px at 90% -5%,rgba(184,137,43,.07),transparent 55%),var(--parchment);
        height:100vh;display:flex;flex-direction:column;overflow:hidden;
    }
    .pos *{box-sizing:border-box}

    .pos-top{background:linear-gradient(180deg,rgba(251,247,236,.98),rgba(241,233,216,.92));border-bottom:1px solid var(--hair);flex-shrink:0}
    .pos-top-inner{display:flex;align-items:center;gap:14px;padding:10px 16px;flex-wrap:wrap}
    .pos-brand{display:flex;align-items:center;gap:10px;font-family:'Reem Kufi';font-weight:700;min-width:180px}
    .pos-logo-wide{height:44px;width:auto;max-width:200px;object-fit:contain;filter:drop-shadow(0 2px 8px rgba(212,168,90,.35))}
    .pos-seal{width:42px;height:42px;border-radius:50%;background:var(--gold-deep);border:2px solid var(--gold);
        display:grid;place-items:center;color:var(--parchment);font-family:'Amiri';font-size:1.25rem;flex-shrink:0}
    .pos-brand b{font-size:1rem;display:block;line-height:1.2}
    .pos-brand small{font-size:.65rem;color:var(--gold-deep);font-family:'El Messiri';font-weight:500}

    .pos-search{flex:1;min-width:220px;max-width:520px;display:flex;gap:8px;align-items:stretch}
    .pos-search-field{position:relative;flex:1;min-width:0}
    .pos-search input{width:100%;background:var(--card);border:1.5px solid var(--hair);border-radius:40px;
        padding:10px 44px 10px 16px;font-family:'El Messiri';font-size:.95rem;color:var(--ink)}
    .pos-search input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(184,137,43,.12)}
    .pos-search-field svg{position:absolute;right:14px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:var(--ink-soft);pointer-events:none;z-index:1}
    .pos-search-btn{
        flex-shrink:0;background:linear-gradient(135deg,var(--gold),#d4a85a);color:var(--ink);border:none;
        padding:0 18px;border-radius:40px;font-family:'Reem Kufi';font-weight:700;font-size:.82rem;cursor:pointer;
        box-shadow:0 4px 14px -4px rgba(184,137,43,.45);white-space:nowrap;
    }

    .pos-kpis{display:flex;gap:8px;flex-wrap:wrap}
    .pos-kpi{background:var(--card);border:1px solid var(--hair);border-radius:12px;padding:6px 12px;text-align:center;min-width:72px}
    .pos-kpi .v{font-family:'Reem Kufi';font-weight:700;font-size:.9rem;color:var(--gold-deep)}
    .pos-kpi .l{font-size:.65rem;color:var(--ink-soft)}

    .pos-pending{display:flex;align-items:center;gap:6px;background:#fef3c7;border:1px solid #fbbf24;
        color:#92400e;padding:7px 14px;border-radius:30px;font-size:.82rem;font-weight:700;text-decoration:none;animation:pulse 2s infinite}
  @keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(251,191,36,.4)}50%{box-shadow:0 0 0 6px rgba(251,191,36,0)}}

    .pos-user{display:flex;align-items:center;gap:8px;padding-right:10px;border-right:1px solid var(--hair)}
    .pos-user-av{width:36px;height:36px;border-radius:50%;background:var(--ink);color:var(--parchment);
        display:grid;place-items:center;font-weight:700;font-size:.85rem}
    .pos-user span{font-size:.78rem;color:var(--ink-soft)}
    .pos-user b{font-size:.88rem;display:block}

    .pos-body{flex:1;display:flex;overflow:hidden;min-height:0;width:100%}

    .pos-cats{width:168px;flex-shrink:0;background:rgba(251,247,236,.6);border-left:1px solid var(--hair);
        overflow-y:auto;padding:10px 8px;display:flex;flex-direction:column;gap:4px}
    .pos-cat{width:100%;text-align:right;padding:9px 12px;border-radius:11px;border:1px solid transparent;
        background:transparent;cursor:pointer;font-family:'El Messiri';font-weight:600;font-size:.85rem;
        color:var(--ink-soft);display:flex;justify-content:space-between;align-items:center;transition:.12s}
    .pos-cat:hover{background:var(--parchment-2)}
    .pos-cat.on{background:var(--ink);color:var(--parchment);border-color:var(--ink)}
    .pos-cat .n{font-size:.7rem;opacity:.7;background:rgba(0,0,0,.08);padding:1px 7px;border-radius:20px}
    .pos-cat.on .n{background:rgba(255,255,255,.15)}

    .pos-main{flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0;padding:10px 12px;gap:10px}
    .pos-toolbar{display:flex;gap:8px;align-items:center;flex-shrink:0}
    .pos-chip{background:var(--parchment-2);border:1px solid var(--hair);color:var(--ink-soft);padding:6px 14px;
        border-radius:30px;cursor:pointer;font-weight:600;font-size:.82rem;white-space:nowrap;transition:.12s}
    .pos-chip.on{background:var(--olive);color:#fff;border-color:var(--olive)}

    .pos-grid-wrap{flex:1;overflow-y:auto;padding-left:4px}
    .pos-grid{display:grid;gap:14px;grid-template-columns:repeat(auto-fill,minmax(200px,1fr))}
    .pos-grid.dense{grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px}

    .p-card{background:var(--card);border:1px solid var(--hair);border-radius:16px;overflow:hidden;
        box-shadow:var(--shadow);display:flex;flex-direction:column;cursor:pointer;transition:transform .18s,box-shadow .18s;position:relative}
    .p-card:not(.out):hover{transform:translateY(-3px);box-shadow:0 18px 36px -18px rgba(36,26,17,.55)}
    .p-card.out{opacity:.5;cursor:not-allowed}
    .p-card .thumb{height:130px;background:var(--parchment-2);position:relative;overflow:hidden}
    .p-card.dense .thumb{height:100px}
    .p-card .thumb img{width:100%;height:100%;object-fit:cover}
    .p-card .thumb .ph{display:grid;place-items:center;height:100%;font-size:2.5rem;opacity:.35}
    .p-card .badge-cat{position:absolute;top:8px;right:8px;background:rgba(36,26,17,.8);color:var(--parchment);
        font-family:'Reem Kufi';font-size:.65rem;padding:3px 9px;border-radius:20px}
    .p-card .badge-stock{position:absolute;top:8px;left:8px;font-size:.65rem;padding:3px 8px;border-radius:20px;font-weight:700}
    .p-card .badge-stock.ok{background:#d1fae5;color:#065f46}
    .p-card .badge-stock.low{background:#fef3c7;color:#92400e}
    .p-card .badge-stock.no{background:#fee2e2;color:#7f1d1d}
    .p-card .badge-var{position:absolute;bottom:8px;left:8px;background:var(--gold);color:var(--ink);
        font-size:.65rem;padding:2px 8px;border-radius:20px;font-weight:700}
    .p-card .body{padding:11px 13px 13px;flex:1;display:flex;flex-direction:column;gap:4px}
    .p-card h3{font-family:'El Messiri';font-size:1rem;font-weight:700;line-height:1.3}
    .p-card .desc{font-size:.75rem;color:var(--ink-soft);line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:2.2em}
    .p-card .price{font-family:'Reem Kufi';color:var(--gold-deep);font-weight:700;font-size:.95rem}
    .p-card .price s{font-size:.75rem;color:var(--ink-soft);font-weight:400;margin-left:4px}
    .p-card .price small{font-size:.7rem;color:var(--ink-soft);font-weight:400}
    .p-card .sku{font-size:.68rem;color:var(--ink-soft);font-family:monospace}
    .p-card .foot{display:flex;justify-content:space-between;align-items:center;margin-top:auto;padding-top:6px}
    .p-card .add{background:var(--ink);color:var(--parchment);border:none;width:32px;height:32px;border-radius:9px;
        font-size:1.2rem;font-weight:700;cursor:pointer;display:grid;place-items:center;transition:.12s}
    .p-card .add:hover{background:var(--gold-deep)}
    .p-card .in-cart{background:var(--olive)}

    .pos-order{width:min(380px,32vw);flex-shrink:0;background:linear-gradient(180deg,#f7f0df,#ede1c6);
        border-left:1px solid var(--hair);display:flex;flex-direction:column;overflow:hidden;z-index:2}
    .pos-order-head{padding:14px 16px 10px;border-bottom:1px solid var(--hair)}
    .pos-order-head h2{font-family:'Amiri';font-size:1.35rem}
    .pos-order-head .sub{font-size:.78rem;color:var(--ink-soft);display:flex;justify-content:space-between}

    .pos-customer{padding:10px 14px;border-bottom:1px solid var(--hair);position:relative}
    .pos-customer input{width:100%;background:var(--card);border:1px solid var(--hair);border-radius:10px;
        padding:8px 12px;font-size:.88rem}
    .pos-customer input:focus{outline:none;border-color:var(--gold)}
    .pos-customer-drop{position:absolute;top:100%;left:14px;right:14px;background:var(--card);border:1px solid var(--hair);
        border-radius:10px;box-shadow:var(--shadow);z-index:50;max-height:200px;overflow-y:auto}
    .pos-customer-drop button{width:100%;text-align:right;padding:9px 12px;border:none;background:none;cursor:pointer;font-size:.85rem}
    .pos-customer-drop button:hover{background:var(--parchment-2)}
    .pos-customer-tag{display:flex;align-items:center;justify-content:space-between;background:var(--parchment-2);
        border-radius:9px;padding:6px 10px;font-size:.82rem;margin-top:6px}
    .pos-customer-tag button{background:none;border:none;color:var(--clay);cursor:pointer;font-size:.75rem}
    .pos-customer-default{width:100%;display:flex;align-items:center;justify-content:space-between;
        background:var(--card);border:1px solid var(--hair);border-radius:10px;padding:8px 12px;cursor:pointer;
        font-size:.85rem;color:var(--ink)}
    .pos-customer-default:hover{border-color:var(--gold)}
    .pos-customer-default .lbl{display:flex;align-items:center;gap:6px}
    .pos-customer-default .lbl small{color:var(--ink-soft);font-size:.72rem}
    .pos-customer-default .chg{color:var(--gold);font-size:.75rem;font-weight:600}
    .pos-customer-drop .muted{padding:6px 12px 3px;font-size:.7rem;color:var(--ink-soft)}
    .pos-customer-drop .cust-ph{color:var(--ink-soft);font-size:.75rem}

    .pos-lines{flex:1;overflow-y:auto;padding:8px 12px;display:flex;flex-direction:column;gap:8px}
    .pos-line{background:var(--card);border:1px solid var(--hair);border-radius:13px;padding:10px;display:flex;gap:10px}
    .pos-line img{width:52px;height:52px;border-radius:9px;object-fit:cover;flex-shrink:0;background:var(--parchment-2)}
    .pos-line .info{flex:1;min-width:0}
    .pos-line .info b{font-size:.9rem;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .pos-line .info small{color:var(--ink-soft);font-size:.75rem}
    .pos-line .info .lp{color:var(--gold-deep);font-family:'Reem Kufi';font-weight:700;font-size:.88rem;margin-top:2px}
    .pos-line .side{text-align:left;display:flex;flex-direction:column;align-items:flex-end;gap:4px}
    .pos-line .side .tot{font-family:'Reem Kufi';font-weight:700;font-size:.95rem}
    .pos-line .rm{background:none;border:none;color:var(--clay);cursor:pointer;font-size:.75rem;text-decoration:underline}
    .stepper{display:flex;align-items:center;gap:5px}
    .stepper button{width:26px;height:26px;border-radius:7px;border:1px solid var(--hair);background:var(--parchment);cursor:pointer;font-weight:700}
    .stepper span{min-width:28px;text-align:center;font-weight:600;font-size:.85rem}

    .pos-totals{border-top:1px solid var(--hair);padding:12px 14px;background:rgba(231,218,191,.45)}
    .pos-totals .row{display:flex;justify-content:space-between;font-size:.88rem;padding:3px 0;color:var(--ink-soft)}
    .pos-totals .row.big{border-top:1px dashed var(--hair);margin-top:6px;padding-top:10px;font-size:1.15rem;font-weight:700;color:var(--ink)}
    .pos-totals .row.big span:last-child{font-family:'Reem Kufi';color:var(--gold-deep);font-size:1.4rem}
    .pos-pay{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:10px}
    .pos-pay button{padding:12px;border-radius:12px;font-family:'El Messiri';font-weight:700;font-size:.92rem;
        cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;border:none;transition:.15s}
    .pos-pay .cash{background:var(--green);color:#fff}
    .pos-pay .cash:hover{filter:brightness(1.08)}
    .pos-pay .card{background:var(--card);border:2px solid var(--hair);color:var(--ink)}
    .pos-pay .card:hover{border-color:var(--gold)}
    .pos-pay button:disabled{opacity:.4;cursor:not-allowed}

    .pos-empty{text-align:center;color:var(--ink-soft);padding:40px 20px}
    .pos-empty svg{width:56px;height:56px;opacity:.35;margin:0 auto 10px}

    .pos-modal{position:fixed;inset:0;background:rgba(36,26,17,.55);backdrop-filter:blur(3px);z-index:200;
        display:grid;place-items:center;padding:16px}
    .pos-modal .box{background:linear-gradient(180deg,#f8f2e2,#ebdfc5);border:1px solid var(--gold);border-radius:18px;
        width:min(480px,100%);max-height:90vh;overflow-y:auto;box-shadow:var(--shadow)}
    .pos-modal .mh{padding:18px 20px 8px;text-align:center;position:relative}
    .pos-modal .mh h3{font-family:'Amiri';font-size:1.5rem}
    .pos-modal .mh .x{position:absolute;top:12px;left:12px;background:none;border:none;font-size:1.4rem;cursor:pointer;color:var(--ink-soft)}
    .pos-modal .mb{padding:8px 20px 20px}

    .var-grid{display:grid;gap:8px}
    .var-opt{border:2px solid var(--hair);background:var(--parchment);border-radius:11px;padding:10px 12px;
        cursor:pointer;text-align:right;transition:.12s}
    .var-opt:hover,.var-opt.sel{border-color:var(--gold-deep);background:var(--card)}
    .var-opt b{display:block;font-size:.92rem}
    .var-opt small{color:var(--ink-soft);font-size:.78rem}
    .var-opt .vp{color:var(--gold-deep);font-family:'Reem Kufi';font-weight:700;margin-top:3px}

    .unit-row{display:flex;gap:6px;flex-wrap:wrap;margin:10px 0}
    .unit-opt{flex:1;min-width:60px;text-align:center;border:1px solid var(--hair);background:var(--parchment);
        border-radius:9px;padding:7px 4px;cursor:pointer;font-weight:600;font-size:.8rem;color:var(--ink-soft)}
    .unit-opt.sel{background:var(--olive);color:#fff;border-color:var(--olive)}

    .numpad{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin:10px 0}
    .numpad button{padding:14px;border-radius:10px;border:none;background:var(--parchment-2);font-size:1.1rem;font-weight:700;cursor:pointer}
    .numpad button:active{transform:scale(.94)}

    .pos-toast{position:fixed;bottom:20px;left:50%;transform:translateX(-50%) translateY(120px);z-index:300;
        background:var(--ink);color:var(--parchment);padding:12px 24px;border-radius:40px;font-weight:600;
        box-shadow:var(--shadow);transition:transform .35s cubic-bezier(.2,1.3,.4,1);display:flex;gap:8px;align-items:center}
    .pos-toast.show{transform:translateX(-50%) translateY(0)}

    .pos-orders-btn{display:flex;align-items:center;gap:6px;background:var(--card);border:1.5px solid var(--hair);
        color:var(--ink);padding:7px 14px;border-radius:30px;font-size:.82rem;font-weight:700;cursor:pointer;position:relative}
    .pos-orders-btn.has-new{border-color:#f59e0b;background:#fef3c7;color:#92400e}
    .pos-orders-btn .dot{position:absolute;top:-4px;left:-4px;min-width:18px;height:18px;border-radius:50%;
        background:var(--clay);color:#fff;font-size:.65rem;display:grid;place-items:center;padding:0 4px}

    .pos-orders-drawer{position:fixed;top:0;bottom:0;right:0;width:min(380px,92vw);background:var(--card);
        border-left:1px solid var(--hair);box-shadow:-12px 0 40px rgba(36,26,17,.15);z-index:250;
        display:flex;flex-direction:column;transform:translateX(100%);transition:transform .25s ease}
    .pos-orders-drawer.open{transform:translateX(0)}
    .pos-orders-head{padding:16px;border-bottom:1px solid var(--hair);display:flex;justify-content:space-between;align-items:center}
    .pos-orders-head h3{font-family:'Amiri';font-size:1.3rem}
    .pos-orders-list{flex:1;overflow-y:auto;padding:12px;display:flex;flex-direction:column;gap:10px}
    .pos-order-card{border:1px solid var(--hair);border-radius:14px;padding:12px;background:var(--parchment);text-decoration:none;color:inherit;display:block;transition:.15s}
    .pos-order-card:hover{border-color:var(--gold);background:#fff}
    .pos-order-card .top{display:flex;justify-content:space-between;gap:8px;margin-bottom:6px}
    .pos-order-card .num{font-family:'Reem Kufi';font-weight:700;color:var(--gold-deep)}
    .pos-order-card .when{font-size:.72rem;color:var(--ink-soft)}
    .pos-order-card .cust{font-weight:700;font-size:.9rem}
    .pos-order-card .meta{font-size:.78rem;color:var(--ink-soft);line-height:1.6;margin-top:4px}
    .pos-order-card .foot{display:flex;justify-content:space-between;align-items:center;margin-top:8px;padding-top:8px;border-top:1px dashed var(--hair)}
    .pos-order-card .total{font-family:'Reem Kufi';font-weight:700;color:var(--gold-deep)}
    .pos-order-card .pay{font-size:.72rem;background:var(--parchment-2);padding:3px 8px;border-radius:20px}

    .pay-summary{display:grid;gap:8px;margin-bottom:12px}
    .pay-row{display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border-radius:11px;background:var(--parchment-2);font-size:.9rem}
    .pay-row.due{background:rgba(184,137,43,.12);border:1px solid rgba(184,137,43,.25)}
    .pay-row.paid{background:var(--card);border:1.5px solid var(--hair)}
    .pay-row.change{background:#d1fae5;border:1px solid #6ee7b7;font-weight:700}
    .pay-row.short{background:#fee2e2;border:1px solid #fca5a5}
    .pay-input{width:100%;text-align:center;font-size:2rem;font-weight:700;font-family:'Reem Kufi';
        border:2px solid var(--hair);border-radius:12px;padding:10px;background:#fff;margin-top:4px}
    .pay-input:focus{outline:none;border-color:var(--gold)}

    .pos-open{display:grid;place-items:center;min-height:100vh;padding:24px}
    .pos-open-card{background:var(--card);border:1px solid var(--hair);border-radius:20px;padding:36px;width:min(420px,100%);box-shadow:var(--shadow);text-align:center}
    .pos-open-card .seal-lg{width:80px;height:80px;border-radius:50%;background:var(--gold-deep);border:3px solid var(--gold);
        display:grid;place-items:center;color:var(--parchment);font-family:'Amiri';font-size:2.2rem;margin:0 auto 16px}
    .pos-open-card h1{font-family:'Amiri';font-size:1.8rem}
    .pos-open-card input{width:100%;text-align:center;font-size:2rem;font-weight:700;padding:14px;border:1.5px solid var(--hair);
        border-radius:14px;background:var(--parchment);margin:12px 0}
    .pos-open-card .quick{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:16px}
    .pos-open-card .quick button{padding:10px;border:1px solid var(--hair);border-radius:10px;background:var(--parchment-2);cursor:pointer;font-weight:600}
    .pos-open-card .go{width:100%;padding:14px;background:var(--green);color:#fff;border:none;border-radius:13px;
        font-weight:700;font-size:1.05rem;cursor:pointer}

    .scrollbar-thin::-webkit-scrollbar{width:5px}
    .scrollbar-thin::-webkit-scrollbar-thumb{background:var(--hair);border-radius:99px}
</style>

@php
    $register   = \App\Domain\Pos\Models\Register::where('is_active', true)->first();
    $registerId = $register?->id ?? 1;
    $brandName  = filament()->getBrandName();
@endphp

@if (! $registerSession)
<div class="pos pos-open">
    <div class="pos-open-card" x-data="{ float: 0, loading: false }">
        <div class="seal-lg">ع</div>
        <h1>{{ $brandName }}</h1>
        <p style="color:var(--ink-soft);margin:6px 0 20px">فتح شيفت — {{ $register?->name ?? 'كاشير 1' }}</p>
        <label style="font-weight:600;font-size:.9rem">الرصيد الافتتاحي (ج.م)</label>
        <input type="number" x-model.number="float" step="0.01" min="0" placeholder="0.00">
        <div class="quick">
            @foreach ([0, 100, 500, 1000] as $amt)
            <button type="button" @click="float = {{ $amt }}">{{ $amt == 0 ? 'صفر' : number_format($amt) }}</button>
            @endforeach
        </div>
        <button type="button" class="go" :disabled="loading"
                @click="loading=true; $wire.openSession({{ $registerId }}, float)">
            <span x-show="!loading">فتح الشيفت</span>
            <span x-show="loading">جارٍ الفتح…</span>
        </button>
    </div>
</div>

@else
<div class="pos" x-data="posExpert(@js($catalog), @js($categories), @js($sessionMeta), {{ $pendingOnline }}, @js($pendingOrders))"
     x-init="init()" @keydown.window="handleKey($event)">

    {{-- ═══ TOP BAR ═══ --}}
    <header class="pos-top">
        <div class="pos-top-inner">
            <div class="pos-brand">
                @if($posLogo = \App\Support\ShopSettings::logoUrl())
                    <img src="{{ $posLogo }}" alt="{{ $brandName }}" class="pos-logo-wide">
                @else
                    <div class="pos-seal">ع</div>
                @endif
                <div>
                    <b>{{ $brandName }}</b>
                    <small>{{ $registerSession->register->name ?? 'كاشير' }} — شيفت #{{ $registerSession->id }}</small>
                </div>
            </div>

            <div class="pos-search">
                <div class="pos-search-field">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input x-ref="search" x-model="query" @keydown.enter="handleBarcode()"
                           placeholder="ابحث بالاسم أو SKU أو امسح الباركود… (F2)">
                </div>
                <button type="button" class="pos-search-btn" @click="$refs.search?.focus()">بحث</button>
            </div>

            <div class="pos-kpis">
                <div class="pos-kpi"><div class="v" x-text="session.orders_count">0</div><div class="l">مبيعات</div></div>
                <div class="pos-kpi"><div class="v" x-text="fmt(session.cash_sales)">0</div><div class="l">نقدي</div></div>
                <div class="pos-kpi"><div class="v" x-text="fmt(session.card_sales)">0</div><div class="l">بطاقة</div></div>
                <div class="pos-kpi"><div class="v" x-text="cart.length">0</div><div class="l">أصناف</div></div>
            </div>

            <button type="button" class="pos-orders-btn" :class="pendingOnline > 0 ? 'has-new' : ''" @click="ordersOpen = true">
                📋 طلبات المتجر
                <span x-show="pendingOnline > 0" class="dot" x-text="pendingOnline"></span>
            </button>

            <div class="pos-user" x-data="{ closeModal: false, counted: 0, note: '' }">
                <div>
                    <b>{{ auth()->user()->name }}</b>
                    <span x-text="clock"></span>
                </div>
                <div class="pos-user-av">{{ mb_substr(auth()->user()->name ?? 'ك', 0, 1) }}</div>
                <button type="button" @click="closeModal=true" style="background:none;border:1px solid var(--clay);color:var(--clay);
                    padding:6px 12px;border-radius:20px;font-size:.78rem;font-weight:600;cursor:pointer">إقفال</button>

                <div x-show="closeModal" x-cloak class="pos-modal" @click.self="closeModal=false">
                    <div class="box" @click.stop>
                        <div class="mh"><h3>إقفال الشيفت</h3><button class="x" @click="closeModal=false">×</button></div>
                        <div class="mb">
                            <label style="font-weight:600;font-size:.88rem">الرصيد المعدود (ج.م)</label>
                            <input type="number" x-model.number="counted" step="0.01" class="pos-open-card" style="width:100%;margin:8px 0">
                            <textarea x-model="note" rows="2" placeholder="ملاحظات" style="width:100%;border:1px solid var(--hair);border-radius:10px;padding:10px;margin-bottom:12px"></textarea>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                                <button @click="closeModal=false" style="padding:12px;border:1px solid var(--hair);border-radius:11px;background:var(--card);cursor:pointer">إلغاء</button>
                                <button @click="$wire.closeSession(counted, note||null); closeModal=false" style="padding:12px;background:var(--clay);color:#fff;border:none;border-radius:11px;font-weight:700;cursor:pointer">تأكيد</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="pos-body">
        {{-- ═══ CATEGORIES SIDEBAR ═══ --}}
        <aside class="pos-cats scrollbar-thin">
            <template x-for="cat in categories" :key="cat.id">
                <button type="button" class="pos-cat" :class="activeCatId === cat.id ? 'on' : ''"
                        @click="activeCatId = cat.id">
                    <span x-text="cat.name"></span>
                    <span class="n" x-text="cat.count"></span>
                </button>
            </template>
        </aside>

        {{-- ═══ PRODUCT AREA ═══ --}}
        <main class="pos-main">
            <div class="pos-toolbar">
                <button type="button" class="pos-chip" :class="viewMode==='all'?'on':''" @click="viewMode='all'">كل المنتجات</button>
                <button type="button" class="pos-chip" :class="viewMode==='featured'?'on':''" @click="viewMode='featured'">⭐ مميزة</button>
                <button type="button" class="pos-chip" :class="viewMode==='instock'?'on':''" @click="viewMode='instock'">متوفر فقط</button>
                <button type="button" class="pos-chip" :class="dense?'on':''" @click="dense=!dense" style="margin-right:auto">⊞ كثيف</button>
                <button type="button" class="pos-chip" @click="$wire.refreshCatalog().then(() => { toast('تم تحديث الكتالوج') })">↻ تحديث</button>
            </div>

            <div class="pos-grid-wrap scrollbar-thin">
                <div class="pos-grid" :class="dense ? 'dense' : ''">
                    <template x-for="p in filtered" :key="p.product_id">
                        <div class="p-card" :class="[p.total_stock <= 0 ? 'out' : '', cartQtyProduct(p.product_id) > 0 ? 'has-cart' : '']"
                             @click="selectProduct(p)">
                            <div class="thumb">
                                <template x-if="p.image"><img :src="p.image" :alt="p.name" loading="lazy"></template>
                                <template x-if="!p.image"><div class="ph">🫙</div></template>
                                <span class="badge-cat" x-text="p.category"></span>
                                <span class="badge-stock" :class="stockClass(p)"
                                      x-text="stockLabel(p)"></span>
                                <span x-show="p.variant_count > 1" class="badge-var"
                                      x-text="p.variant_count + ' خيارات'"></span>
                            </div>
                            <div class="body">
                                <h3 x-text="p.name"></h3>
                                <p class="desc" x-text="p.description || ''"></p>
                                <div class="price">
                                    <template x-if="p.min_price === p.max_price">
                                        <span>
                                            <s x-show="p.variants[0]?.is_on_sale && p.variants[0]?.compare_at > p.variants[0]?.price"
                                               x-text="fmt(p.variants[0]?.compare_at)" style="font-size:.75rem;color:var(--ink-soft);margin-left:4px"></s>
                                            <span x-text="fmt(p.min_price)"></span>
                                        </span>
                                    </template>
                                    <template x-if="p.min_price !== p.max_price">
                                        <span x-text="fmt(p.min_price) + ' – ' + fmt(p.max_price)"></span>
                                    </template>
                                    <small x-show="p.variants[0]?.is_weighted"> / كجم</small>
                                </div>
                                <div class="sku" x-text="p.variants[0]?.sku"></div>
                                <div class="foot">
                                    <span style="font-size:.72rem;color:var(--ink-soft)" x-text="cartQtyProduct(p.product_id) > 0 ? 'في السلة: '+cartQtyProduct(p.product_id) : ''"></span>
                                    <button type="button" class="add" :class="cartQtyProduct(p.product_id)>0?'in-cart':''"
                                            @click.stop="selectProduct(p)">+</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <template x-if="filtered.length === 0">
                    <div class="pos-empty">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p>لا توجد نتائج — جرّب بحثاً أو تصنيفاً آخر</p>
                    </div>
                </template>
            </div>
        </main>

        {{-- ═══ ORDER PANEL ═══ --}}
        <aside class="pos-order">
            <div class="pos-order-head">
                <h2>ملخص الطلب</h2>
                <div class="sub">
                    <span>#<span x-text="String(session.id).padStart(4,'0')"></span></span>
                    <button type="button" @click="cart=[]" x-show="cart.length" style="background:none;border:none;color:var(--clay);cursor:pointer;font-size:.78rem">تفريغ السلة</button>
                </div>
            </div>

            <div class="pos-customer" @click.outside="customerPickerOpen=false">
                {{-- لا يوجد عميل مختار: العميل الافتراضي (نقدي) مع إمكانية الاختيار --}}
                <template x-if="!customer">
                    <div>
                        <button type="button" x-show="!customerPickerOpen"
                                class="pos-customer-default" @click="openCustomerPicker()">
                            <span class="lbl">👤 عميل نقدي <small>(افتراضي)</small></span>
                            <span class="chg">اختيار عميل ▾</span>
                        </button>

                        <div x-show="customerPickerOpen" style="position:relative">
                            <input type="text" x-ref="custInput" x-model="customerQuery"
                                   @input.debounce.300ms="searchCustomer()"
                                   @focus="searchCustomer()"
                                   placeholder="بحث بالاسم أو الهاتف…">
                            <div x-show="customerResults.length" class="pos-customer-drop">
                                <div class="muted" x-show="!customerQuery">أحدث العملاء</div>
                                <template x-for="c in customerResults" :key="c.id">
                                    <button type="button" @click="pickCustomer(c)">
                                        <span x-text="c.name"></span>
                                        <span class="cust-ph" x-text="c.phone ? ' — '+c.phone : ''"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- عميل مختار --}}
                <template x-if="customer">
                    <div class="pos-customer-tag">
                        <span x-text="customer.name + (customer.phone ? ' — '+customer.phone : '')"></span>
                        <button type="button" @click="clearCustomer()">إزالة</button>
                    </div>
                </template>
            </div>

            <div class="pos-lines scrollbar-thin">
                <template x-if="!cart.length">
                    <div class="pos-empty">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <p>السلة فارغة</p>
                    </div>
                </template>
                <template x-for="(line, i) in cart" :key="line.id">
                    <div class="pos-line">
                        <template x-if="line.image"><img :src="line.thumb || line.image" :alt="line.name"></template>
                        <template x-if="!line.image"><div style="width:52px;height:52px;border-radius:9px;background:var(--parchment-2);display:grid;place-items:center">🫙</div></template>
                        <div class="info">
                            <b x-text="line.full_name || line.name"></b>
                            <small x-text="line.label + ' — ' + fmt(line.price)"></small>
                            <div class="stepper">
                                <button type="button" @click="changeQty(i, -line.step)">−</button>
                                <span x-text="fmtQty(line.qty, line.unit)"></span>
                                <button type="button" @click="changeQty(i, line.step)">+</button>
                            </div>
                        </div>
                        <div class="side">
                            <span class="tot" x-text="fmt(lineTotal(line))"></span>
                            <button type="button" class="rm" @click="cart.splice(i,1)">حذف</button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="pos-totals">
                <div class="row"><span>المجموع الفرعي</span><span x-text="fmt(subtotal)"></span></div>
                <div class="row"><span>ض.ق.م 14% (شامل)</span><span x-text="fmt(taxAmount)"></span></div>
                <div class="row big"><span>الإجمالي</span><span x-text="fmt(subtotal)"></span></div>
                <div class="pos-pay">
                    <button type="button" class="cash" :disabled="!cart.length" @click="openPayModal('cash')">💵 نقدًا (F4)</button>
                    <button type="button" class="card" :disabled="!cart.length" @click="pay('card')">💳 بطاقة (F8)</button>
                </div>
            </div>
        </aside>
    </div>

    {{-- ═══ VARIANT PICKER ═══ --}}
    <div x-show="variantModal" x-cloak class="pos-modal" @click.self="variantModal=false">
        <div class="box" @click.stop>
            <div class="mh">
                <h3 x-text="pendingProduct?.name"></h3>
                <button class="x" @click="variantModal=false">×</button>
                <p style="color:var(--ink-soft);font-size:.85rem">اختر المتغيّر</p>
            </div>
            <div class="mb var-grid">
                <template x-for="v in pendingProduct?.variants || []" :key="v.id">
                    <button type="button" class="var-opt" :class="selectedVariant?.id===v.id?'sel':''"
                            :disabled="v.available<=0" @click="selectedVariant=v">
                        <b x-text="v.label"></b>
                        <small x-text="'SKU: '+v.sku + (v.barcode ? ' | '+v.barcode : '')"></small>
                        <div class="vp" x-text="fmt(v.price) + (v.is_weighted?' / كجم':'')"></div>
                        <small x-text="v.available<=0?'غير متوفر':('متوفر: '+fmtQty(v.available,v.unit))"></small>
                    </button>
                </template>
                <button type="button" class="go pos-open-card" style="margin-top:10px;width:100%;padding:12px;background:var(--ink);color:var(--parchment);border:none;border-radius:12px;font-weight:700;cursor:pointer"
                        :disabled="!selectedVariant || selectedVariant.available<=0"
                        @click="confirmVariant()">إضافة للسلة</button>
            </div>
        </div>
    </div>

    {{-- ═══ WEIGHT MODAL ═══ --}}
    <div x-show="weightModal" x-cloak class="pos-modal" @click.self="weightModal=false">
        <div class="box" @click.stop>
            <div class="mh">
                <h3 x-text="pendingVariant?.full_name || pendingVariant?.label"></h3>
                <button class="x" @click="weightModal=false">×</button>
            </div>
            <div class="mb">
                <input type="number" x-ref="weightInput" x-model.number="pendingQty" :step="pendingVariant?.step"
                       @keydown.enter="confirmWeight()" style="width:100%;text-align:center;font-size:2rem;font-weight:700;padding:12px;border:1.5px solid var(--hair);border-radius:12px;margin-bottom:10px">
                <div class="unit-row">
                    <template x-for="g in [100,250,500,1000]" :key="g">
                        <button type="button" class="unit-opt" :class="pendingQty===g?'sel':''" @click="pendingQty=g"
                                x-text="g>=1000?(g/1000)+' كجم':g+' جم'"></button>
                    </template>
                </div>
                <div style="display:flex;justify-content:space-between;padding:12px;background:var(--parchment-2);border-radius:11px;margin-bottom:12px">
                    <span>الإجمالي</span>
                    <b style="color:var(--gold-deep);font-family:'Reem Kufi'" x-text="fmt(Math.round((pendingVariant?.price||0)*pendingQty/1000))"></b>
                </div>
                <button type="button" @click="confirmWeight()" style="width:100%;padding:13px;background:var(--green);color:#fff;border:none;border-radius:12px;font-weight:700;cursor:pointer">إضافة</button>
            </div>
        </div>
    </div>

    {{-- ═══ CASH PAY MODAL ═══ --}}
    <div x-show="payModal" x-cloak class="pos-modal" @click.self="payModal=false">
        <div class="box" @click.stop style="width:min(420px,100%)">
            <div class="mh" style="background:var(--green);color:#fff;border-radius:18px 18px 0 0;margin:-1px">
                <button class="x" style="color:#fff" @click="payModal=false">×</button>
                <p style="font-size:.85rem;opacity:.9">المبلغ المستحق</p>
                <h3 style="font-size:2rem;color:#fff" x-text="fmt(subtotal)"></h3>
            </div>
            <div class="mb">
                <div class="pay-summary">
                    <div class="pay-row due">
                        <span>المطلوب من العميل</span>
                        <b x-text="fmt(subtotal)"></b>
                    </div>
                    <div class="pay-row paid">
                        <div style="width:100%">
                            <small style="color:var(--ink-soft)">المدفوع (ما سلّمه العميل)</small>
                            <input type="text" inputmode="decimal" class="pay-input" x-model="cashInputMajor"
                                   @keydown.enter="confirmCashPay()" placeholder="0.00">
                        </div>
                    </div>
                    <div class="pay-row change" x-show="cashTenderedMinor >= subtotal && cashChangeMinor > 0">
                        <span>الباقي للعميل</span>
                        <b style="font-family:'Reem Kufi';font-size:1.2rem" x-text="fmt(cashChangeMinor)"></b>
                    </div>
                    <div class="pay-row short" x-show="cashTenderedMinor > 0 && cashTenderedMinor < subtotal">
                        <span>متبقي على العميل</span>
                        <b x-text="fmt(subtotal - cashTenderedMinor)"></b>
                    </div>
                </div>
                <div class="unit-row">
                    <template x-for="amt in [50, 100, 200, 500]" :key="amt">
                        <button type="button" class="unit-opt" @click="cashInputMajor = String(amt)" x-text="amt + ' ج.م'"></button>
                    </template>
                </div>
                <div class="numpad">
                    <template x-for="n in ['7','8','9','4','5','6','1','2','3','C','0','.']" :key="n">
                        <button type="button" @click="numpadPress(n)" x-text="n"></button>
                    </template>
                    <button type="button" @click="numpadPress('⌫')">⌫</button>
                </div>
                <button type="button" :disabled="cashTenderedMinor < subtotal" @click="confirmCashPay()"
                        style="width:100%;padding:14px;background:var(--green);color:#fff;border:none;border-radius:12px;font-weight:700;cursor:pointer">
                    تأكيد الدفع
                </button>
            </div>
        </div>
    </div>

    {{-- ═══ ONLINE ORDERS DRAWER ═══ --}}
    <div class="pos-orders-drawer scrollbar-thin" :class="ordersOpen ? 'open' : ''">
        <div class="pos-orders-head">
            <h3>طلبات المتجر الجديدة</h3>
            <button type="button" @click="ordersOpen=false" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:var(--ink-soft)">×</button>
        </div>
        <div class="pos-orders-list">
            <template x-if="!pendingOrders.length">
                <div class="pos-empty">
                    <p>لا توجد طلبات جديدة حاليًا</p>
                </div>
            </template>
            <template x-for="o in pendingOrders" :key="o.id">
                <a :href="o.url" target="_blank" class="pos-order-card">
                    <div class="top">
                        <span class="num" x-text="o.number"></span>
                        <span class="when" x-text="o.placed_ago"></span>
                    </div>
                    <div class="cust" x-text="o.customer"></div>
                    <div class="meta">
                        <div x-show="o.phone" x-text="'📞 ' + o.phone"></div>
                        <div x-show="o.city" x-text="'📍 ' + o.city"></div>
                        <div x-text="o.items_count + ' صنف — ' + o.placed_at"></div>
                    </div>
                    <div class="foot">
                        <span class="total" x-text="fmt(o.total)"></span>
                        <span class="pay" x-text="o.payment"></span>
                    </div>
                </a>
            </template>
        </div>
    </div>
    <div x-show="ordersOpen" x-cloak @click="ordersOpen=false"
         style="position:fixed;inset:0;background:rgba(36,26,17,.35);z-index:240"></div>

    <div class="pos-toast" :class="toastMsg ? 'show' : ''" x-text="toastMsg"></div>
</div>
@endif

@push('scripts')
<script>
function posExpert(catalog, categories, sessionMeta, pendingOnline, pendingOrders) {
    return {
        catalog, categories, session: sessionMeta, pendingOnline, pendingOrders,
        cart: [], query: '', activeCatId: 0, viewMode: 'all', dense: false,
        customer: null, customerQuery: '', customerResults: [], customerPickerOpen: false,
        variantModal: false, pendingProduct: null, selectedVariant: null,
        weightModal: false, pendingVariant: null, pendingQty: 0,
        payModal: false, cashInputMajor: '0', ordersOpen: false,
        toastMsg: '', clock: '', barcodeIndex: {},

        get cashTenderedMinor() {
            const v = parseFloat(String(this.cashInputMajor).replace(/,/g, '').trim());
            return isNaN(v) ? 0 : Math.round(v * 100);
        },
        get cashChangeMinor() {
            return Math.max(0, this.cashTenderedMinor - this.subtotal);
        },

        init() {
            this.buildBarcodeIndex();
            this.$refs.search?.focus();
            setInterval(() => {
                this.clock = new Date().toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
            }, 1000);
            setInterval(() => this.poll(), 15000);
            this.$wire.on('order-completed', (e) => {
                this.playChime();
                let msg = '✓ تم البيع — ' + (e.number || '');
                if (e.change > 0) msg += ' | الباقي: ' + this.fmt(e.change);
                this.toast(msg);
            });
            Livewire.hook('message.processed', () => {
                if (this.$wire.catalog) this.catalog = this.$wire.catalog;
                if (this.$wire.categories) this.categories = this.$wire.categories;
                if (this.$wire.sessionMeta) this.session = this.$wire.sessionMeta;
                if (this.$wire.pendingOrders) this.pendingOrders = this.$wire.pendingOrders;
                if (this.$wire.pendingOnline !== undefined) this.pendingOnline = this.$wire.pendingOnline;
                this.buildBarcodeIndex();
            });
        },

        buildBarcodeIndex() {
            this.barcodeIndex = {};
            this.catalog.forEach(p => {
                p.variants.forEach(v => {
                    if (v.barcode) this.barcodeIndex[v.barcode] = { product: p, variant: v };
                    if (v.sku) this.barcodeIndex[v.sku] = { product: p, variant: v };
                });
            });
        },

        get filtered() {
            let list = this.catalog;
            if (this.activeCatId !== 0) list = list.filter(p => p.category_id === this.activeCatId);
            if (this.viewMode === 'featured') list = list.filter(p => p.is_featured);
            if (this.viewMode === 'instock') list = list.filter(p => p.total_stock > 0);
            if (this.query.trim()) {
                const q = this.query.trim().toLowerCase();
                list = list.filter(p =>
                    p.name.toLowerCase().includes(q) ||
                    p.category.toLowerCase().includes(q) ||
                    p.variants.some(v =>
                        (v.sku||'').toLowerCase().includes(q) ||
                        (v.barcode||'').toLowerCase().includes(q) ||
                        (v.full_name||'').toLowerCase().includes(q) ||
                        (v.label||'').toLowerCase().includes(q)
                    )
                );
            }
            return list;
        },

        stockClass(p) {
            if (p.total_stock <= 0) return 'no';
            if (p.total_stock < 10) return 'low';
            return 'ok';
        },
        stockLabel(p) {
            if (p.total_stock <= 0) return 'نفد';
            const v = p.variants[0];
            return v?.is_weighted ? this.fmtQty(p.total_stock, v.unit) : Math.floor(p.total_stock) + ' ق';
        },

        selectProduct(p) {
            if (p.total_stock <= 0) return;
            if (p.variant_count === 1) {
                this.addVariant(p, p.variants[0]);
                return;
            }
            this.pendingProduct = p;
            this.selectedVariant = p.variants.find(v => v.is_default && v.available > 0) || p.variants.find(v => v.available > 0) || p.variants[0];
            this.variantModal = true;
        },

        confirmVariant() {
            if (!this.selectedVariant || this.selectedVariant.available <= 0) return;
            this.addVariant(this.pendingProduct, this.selectedVariant);
            this.variantModal = false;
        },

        addVariant(product, variant) {
            if (variant.available <= 0) return;
            if (variant.is_weighted) {
                this.pendingVariant = {
                    ...variant,
                    product_id: product.product_id,
                    full_name: variant.full_name || product.name + ' — ' + variant.label,
                    image: product.image,
                    thumb: product.thumb,
                };
                this.pendingQty = variant.step;
                this.weightModal = true;
                this.variantModal = false;
                this.$nextTick(() => this.$refs.weightInput?.select());
                return;
            }
            this.pushCart(product, variant, 1);
            this.query = '';
            this.toast('أُضيف ' + (variant.full_name || product.name));
        },

        confirmWeight() {
            const v = this.pendingVariant;
            const qty = Math.max(v.step, Math.round(this.pendingQty / v.step) * v.step);
            if (qty > v.available) { alert('الكمية المتاحة: ' + this.fmtQty(v.available, v.unit)); return; }
            this.pushCart({ product_id: v.product_id, image: v.image, thumb: v.thumb, name: v.full_name?.split(' — ')[0] || v.label }, v, qty);
            this.weightModal = false;
            this.query = '';
        },

        pushCart(product, variant, qty) {
            const existing = this.cart.find(l => l.id === variant.id);
            const newQty = (existing?.qty ?? 0) + qty;
            if (newQty > variant.available) return;
            const row = {
                id: variant.id, product_id: product.product_id,
                name: product.name, full_name: variant.full_name || product.name,
                label: variant.label, sku: variant.sku, price: variant.price,
                unit: variant.unit, unit_label: variant.unit_label, step: variant.step,
                is_weighted: variant.is_weighted, available: variant.available,
                image: product.image, thumb: product.thumb, qty: newQty,
            };
            if (existing) Object.assign(existing, row);
            else this.cart.push(row);
        },

        cartQtyProduct(pid) {
            return this.cart.filter(l => l.product_id === pid).reduce((s, l) => s + (l.is_weighted ? 1 : l.qty), 0);
        },

        lineTotal(line) {
            return line.is_weighted ? Math.round(line.price * line.qty / 1000) : Math.round(line.price * line.qty);
        },
        get subtotal() { return this.cart.reduce((s, l) => s + this.lineTotal(l), 0); },
        get taxAmount() { return Math.round(this.subtotal - this.subtotal / 1.14); },

        changeQty(i, delta) {
            const line = this.cart[i];
            const next = +(line.qty + delta).toFixed(3);
            if (next <= 0) { this.cart.splice(i, 1); return; }
            if (next > line.available) return;
            line.qty = next;
        },

        handleBarcode() {
            const code = this.query.trim();
            const hit = this.barcodeIndex[code];
            if (hit) {
                this.addVariant(hit.product, hit.variant);
                this.query = '';
                return;
            }
        },

        openCustomerPicker() {
            this.customerPickerOpen = true;
            this.$nextTick(() => { this.$refs.custInput?.focus(); this.searchCustomer(); });
        },
        async searchCustomer() {
            this.customerResults = await this.$wire.searchCustomers(this.customerQuery);
        },
        pickCustomer(c) {
            this.customer = c;
            this.customerResults = [];
            this.customerQuery = '';
            this.customerPickerOpen = false;
        },
        clearCustomer() {
            this.customer = null;
            this.customerQuery = '';
            this.customerResults = [];
            this.customerPickerOpen = false;
        },

        openPayModal(m) {
            if (!this.cart.length) return;
            if (m === 'cash') {
                this.cashInputMajor = (this.subtotal / 100).toFixed(2);
                this.payModal = true;
            } else this.pay('card');
        },

        numpadPress(n) {
            if (n === 'C') {
                this.cashInputMajor = '0';
                return;
            }
            if (n === '⌫') {
                let s = String(this.cashInputMajor);
                s = s.length <= 1 ? '0' : s.slice(0, -1);
                this.cashInputMajor = s;
                return;
            }
            if (n === '.') {
                if (String(this.cashInputMajor).includes('.')) return;
                this.cashInputMajor = String(this.cashInputMajor) + '.';
                return;
            }
            const digit = String(n);
            this.cashInputMajor = (this.cashInputMajor === '0')
                ? digit
                : String(this.cashInputMajor) + digit;
        },

        confirmCashPay() {
            if (this.cashTenderedMinor < this.subtotal) return;
            this.pay('cash', this.cashTenderedMinor);
            this.payModal = false;
        },

        pay(method, tendered = null) {
            if (!this.cart.length) return;
            const items = this.cart.map(l => ({ variant_id: l.id, qty: l.qty }));
            const payments = [{
                method,
                amount_minor: this.subtotal,
                tendered_minor: method === 'cash' ? (tendered ?? this.subtotal) : this.subtotal,
            }];
            this.$wire.checkout(items, payments, this.customer?.id ?? null).then(() => {
                this.cart = [];
                this.clearCustomer();
                this.$refs.search?.focus();
            });
        },

        async poll() {
            const r = await this.$wire.pollUpdates();
            if (r.new_online) {
                this.playChime();
                this.toast('🔔 طلب جديد من المتجر!');
                this.ordersOpen = true;
            }
            this.pendingOnline = r.pending_online;
            if (r.pending_orders) this.pendingOrders = r.pending_orders;
            if (r.session) this.session = r.session;
        },

        handleKey(e) {
            if (e.key === 'F2') { e.preventDefault(); this.$refs.search?.focus(); }
            if (e.key === 'F4' && this.cart.length) { e.preventDefault(); this.openPayModal('cash'); }
            if (e.key === 'F8' && this.cart.length) { e.preventDefault(); this.pay('card'); }
            if (e.key === 'F9') { e.preventDefault(); this.ordersOpen = !this.ordersOpen; }
            if (e.key === 'Escape') { this.variantModal = false; this.weightModal = false; this.payModal = false; this.ordersOpen = false; }
        },

        fmt(minor) {
            return ((minor || 0) / 100).toLocaleString('ar-EG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ج.م';
        },
        fmtQty(qty, unit) {
            if (unit === 'gram' || unit === 'ml') return qty >= 1000 ? (qty/1000).toFixed(1)+' كجم' : qty+' جم';
            return qty + ' ' + (unit === 'piece' ? 'ق' : '');
        },

        toast(msg) {
            this.toastMsg = msg;
            setTimeout(() => this.toastMsg = '', 2800);
        },

        playChime() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                [0, 0.12].forEach((d, i) => {
                    const o = ctx.createOscillator(), g = ctx.createGain();
                    o.connect(g); g.connect(ctx.destination);
                    o.frequency.value = i ? 1100 : 880;
                    g.gain.value = 0.07;
                    o.start(ctx.currentTime + d); o.stop(ctx.currentTime + d + 0.1);
                });
            } catch(e) {}
        },
    };
}
</script>
@endpush
</x-filament-panels::page>
