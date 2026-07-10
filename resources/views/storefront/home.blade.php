@extends('layouts.storefront')

@section('title', $shop['name'] . ' — ' . $shop['tagline'])

@push('head-styles')
<style>
/* ═══════════════════════════════════════════════════
   ATTAR — CINEMATIC HOME  ·  "Midnight Souk"
   Dark luxury · Arabic editorial · Molten gold light
═══════════════════════════════════════════════════ */

/* ── Cinematic token extensions ── */
:root{
  --void:#060402;
  --ember-1:#0e0804;
  --ember-2:#1a1109;
  --ember-3:#2e1e10;
  --g1:#c49020;
  --g2:#e8b840;
  --g3:#f5d060;
  --copper:#a04520;
  --cream:#f0e4cc;
  --cream-dim:#a08860;
  --cream-ghost:#604830;
  --ease:cubic-bezier(.2,.8,.2,1);
  --bounce:cubic-bezier(.34,1.56,.64,1);
}

/* ── Scroll reveal ── */
[data-r]{
  opacity:0;
  transform:translateY(38px);
  transition:opacity .85s var(--ease),transform .85s var(--ease);
}
[data-r].in{opacity:1;transform:none}
[data-r][data-d="1"]{transition-delay:.08s}
[data-r][data-d="2"]{transition-delay:.16s}
[data-r][data-d="3"]{transition-delay:.24s}
[data-r][data-d="4"]{transition-delay:.32s}
[data-r][data-d="5"]{transition-delay:.40s}

/* ══ HERO ══════════════════════════════════════════ */
.c-hero{
  min-height:100dvh;display:flex;flex-direction:column;
  background:var(--void);position:relative;overflow:hidden;
}
/* Atmospheric glow layers */
.c-hero-bg{
  position:absolute;inset:0;pointer-events:none;
  background:
    radial-gradient(ellipse 75% 55% at 78% 15%,rgba(196,144,32,.13),transparent 55%),
    radial-gradient(ellipse 55% 45% at 12% 80%,rgba(160,69,32,.09),transparent 48%),
    radial-gradient(circle 500px at 50% 50%,rgba(196,144,32,.03),transparent 70%);
}
/* Film grain */
.c-hero::before{
  content:'';position:absolute;inset:0;z-index:0;pointer-events:none;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='250' height='250'%3E%3Cfilter id='g'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.8' numOctaves='4' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/filter%3E%3Crect width='250' height='250' filter='url(%23g)' opacity='.045'/%3E%3C/svg%3E");
}
/* Large Arabic watermark */
.c-wm{
  position:absolute;font-family:'Amiri';
  font-size:clamp(10rem,25vw,20rem);
  color:rgba(196,144,32,.028);line-height:1;
  top:50%;left:50%;transform:translate(-50%,-50%);
  white-space:nowrap;pointer-events:none;z-index:0;
  letter-spacing:16px;
  animation:wmBreathe 9s ease-in-out infinite;
}
@keyframes wmBreathe{
  0%,100%{opacity:.028;transform:translate(-50%,-50%) scale(1)}
  50%{opacity:.055;transform:translate(-50%,-50%) scale(1.025)}
}
/* Hero inner grid */
.c-hero-inner{
  flex:1;display:grid;grid-template-columns:1.2fr .8fr;
  align-items:center;gap:48px;
  padding:120px 0 90px;position:relative;z-index:2;
}
/* ── Eyebrow tag ── */
.c-eyebrow{
  display:inline-flex;align-items:center;gap:10px;
  font-family:'Reem Kufi';font-size:.75rem;letter-spacing:2px;
  color:var(--g2);padding:8px 20px;border-radius:40px;
  border:1px solid rgba(196,144,32,.28);
  background:rgba(196,144,32,.07);
  margin-bottom:28px;
  animation:fadeUp .65s var(--ease) .05s both;
}
.c-eyebrow::before{
  content:'';width:6px;height:6px;border-radius:50%;
  background:var(--g2);box-shadow:0 0 10px var(--g2);
  animation:dot 2.2s ease-in-out infinite;
}
@keyframes dot{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.3;transform:scale(.7)}}
/* ── H1 ── */
.c-h1{
  font-family:'Amiri';font-weight:700;
  font-size:clamp(2.8rem,5.8vw,5.2rem);
  color:var(--cream);line-height:1.1;margin-bottom:20px;
  animation:fadeUp .7s var(--ease) .15s both;
}
.c-h1 em{font-style:normal;color:var(--g2);text-shadow:0 0 60px rgba(196,144,32,.35)}
/* ── Lead ── */
.c-lead{
  font-size:1.06rem;line-height:1.88;color:var(--cream-dim);
  max-width:480px;margin-bottom:36px;
  animation:fadeUp .7s var(--ease) .25s both;
}
/* ── CTA row ── */
.c-cta{display:flex;flex-wrap:wrap;gap:14px;margin-bottom:48px;animation:fadeUp .7s var(--ease) .35s both}
/* ── Gold fill button ── */
.c-btn-gold{
  display:inline-flex;align-items:center;gap:10px;
  padding:14px 32px;border-radius:12px;
  background:linear-gradient(135deg,var(--g1),var(--g2));
  color:var(--void);font-family:'Reem Kufi';font-weight:700;font-size:.98rem;
  border:none;cursor:pointer;text-decoration:none;
  transition:all .3s var(--ease);position:relative;overflow:hidden;
  box-shadow:0 8px 28px -6px rgba(196,144,32,.55);
}
.c-btn-gold::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,.18),transparent);opacity:0;transition:.3s}
.c-btn-gold:hover{transform:translateY(-2px);box-shadow:0 16px 40px -6px rgba(196,144,32,.65)}
.c-btn-gold:hover::after{opacity:1}
/* ── Ghost button ── */
.c-btn-ghost{
  display:inline-flex;align-items:center;gap:10px;
  padding:13px 28px;border-radius:12px;
  background:transparent;color:var(--cream);
  border:1.5px solid rgba(240,228,204,.18);
  font-family:'Reem Kufi';font-weight:600;font-size:.92rem;
  cursor:pointer;text-decoration:none;transition:.28s;
}
.c-btn-ghost:hover{border-color:var(--g1);color:var(--g2)}
/* ── Stats ── */
.c-stats-row{
  display:flex;gap:32px;flex-wrap:wrap;
  padding-top:32px;border-top:1px solid rgba(196,144,32,.12);
  animation:fadeUp .7s var(--ease) .45s both;
}
.c-stat .num{font-family:'Reem Kufi';font-size:1.9rem;font-weight:700;color:var(--g2);line-height:1;text-shadow:0 0 30px rgba(196,144,32,.3)}
.c-stat .lbl{font-size:.7rem;color:var(--cream-ghost);margin-top:5px;font-family:'Reem Kufi';letter-spacing:1px}
/* ── Orb scene ── */
.c-orb-scene{
  position:relative;width:440px;height:440px;
  display:flex;align-items:center;justify-content:center;
  animation:fadeUp .85s var(--ease) .1s both;
}
/* Rotating rings */
.c-ring{position:absolute;border-radius:50%;border:1px solid rgba(196,144,32,.14);animation:rotateCW linear infinite}
.c-ring:nth-child(1){inset:0;animation-duration:80s}
.c-ring:nth-child(2){inset:8%;animation-duration:55s;animation-direction:reverse;border-style:dashed;border-color:rgba(196,144,32,.1)}
.c-ring:nth-child(3){inset:17%;animation-duration:110s;border-color:rgba(196,144,32,.08)}
@keyframes rotateCW{to{transform:rotate(360deg)}}
/* Ring accent dots */
.c-ring:nth-child(1)::before,.c-ring:nth-child(1)::after{
  content:'';position:absolute;width:7px;height:7px;border-radius:50%;
  background:var(--g1);box-shadow:0 0 14px var(--g1);
  top:-3.5px;left:50%;transform:translateX(-50%);
}
.c-ring:nth-child(1)::after{top:auto;bottom:-3.5px}
.c-ring:nth-child(2)::before{
  content:'';position:absolute;width:5px;height:5px;border-radius:50%;
  background:var(--copper);box-shadow:0 0 10px var(--copper);
  right:-2.5px;top:50%;transform:translateY(-50%);
}
/* Core orb */
.c-orb-core{
  position:relative;z-index:2;width:210px;height:210px;border-radius:50%;
  background:radial-gradient(circle at 35% 35%,#281806,#070401);
  border:2px solid rgba(196,144,32,.42);
  display:flex;align-items:center;justify-content:center;overflow:hidden;
  box-shadow:
    0 0 0 10px rgba(196,144,32,.04),
    0 0 0 22px rgba(196,144,32,.02),
    0 40px 100px -15px rgba(0,0,0,.98),
    inset 0 1px 0 rgba(196,144,32,.22);
}
.c-orb-core img{width:100%;height:100%;object-fit:cover}
.c-orb-seal{font-family:'Amiri';font-size:5.5rem;color:var(--g1);text-shadow:0 0 50px rgba(196,144,32,.45);line-height:1}
/* Floating spice cards */
.c-spice{
  position:absolute;display:flex;align-items:center;gap:10px;
  padding:12px 16px;border-radius:16px;min-width:120px;
  background:rgba(14,8,4,.9);backdrop-filter:blur(18px);
  border:1px solid rgba(196,144,32,.2);
  box-shadow:0 20px 50px -10px rgba(0,0,0,.75),inset 0 1px 0 rgba(255,255,255,.035);
  animation:spiceFloat ease-in-out infinite;
}
.c-spice .ic{font-size:1.45rem;flex-shrink:0}
.c-spice-name{font-family:'Reem Kufi';font-size:.76rem;color:var(--cream);font-weight:600;line-height:1.3}
.c-spice-sub{font-size:.61rem;color:rgba(196,144,32,.72);margin-top:2px}
.c-spice:nth-child(5){top:4%;right:-9%;animation-duration:5.2s;animation-delay:0s}
.c-spice:nth-child(6){top:40%;left:-18%;animation-duration:6.5s;animation-delay:1.4s}
.c-spice:nth-child(7){bottom:9%;right:-11%;animation-duration:4.8s;animation-delay:2.8s}
.c-spice:nth-child(8){bottom:26%;left:-13%;animation-duration:7.2s;animation-delay:4.2s}
@keyframes spiceFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-9px)}}
/* Scroll caret */
.c-scroll-cue{
  position:absolute;bottom:30px;left:50%;transform:translateX(-50%);
  z-index:2;display:flex;flex-direction:column;align-items:center;gap:8px;
  animation:fadeUp .8s var(--ease) .9s both;
}
.c-scroll-line{width:1px;height:44px;background:linear-gradient(to bottom,rgba(196,144,32,.5),transparent)}
.c-scroll-txt{font-family:'Reem Kufi';font-size:.6rem;color:rgba(196,144,32,.45);letter-spacing:3px}

@keyframes fadeUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:none}}

/* ══ TICKER ════════════════════════════════════════ */
.c-ticker{
  background:var(--ember-1);
  border-block:1px solid rgba(196,144,32,.16);
  padding:13px 0;overflow:hidden;position:relative;
}
.c-ticker::before,.c-ticker::after{
  content:'';position:absolute;top:0;bottom:0;width:70px;z-index:2;pointer-events:none;
}
.c-ticker::before{right:0;background:linear-gradient(to left,var(--ember-1),transparent)}
.c-ticker::after{left:0;background:linear-gradient(to right,var(--ember-1),transparent)}
.c-ticker-track{display:flex;white-space:nowrap;width:max-content;animation:ticker 32s linear infinite}
.c-tick-item{
  display:inline-flex;align-items:center;gap:10px;
  padding:0 28px;font-family:'Reem Kufi';font-size:.78rem;
  color:rgba(196,144,32,.88);
}
.c-tick-sep{font-size:.5rem;color:rgba(196,144,32,.3)}
@keyframes ticker{to{transform:translateX(50%)}}

/* ══ SECTION COMMONS ═══════════════════════════════ */
.c-sec{padding:88px 0;position:relative}
.c-sec-dark{background:var(--void)}
.c-sec-ember{background:var(--ember-1)}
.c-sec-warm{background:var(--ember-2)}

.c-over{
  display:flex;align-items:center;gap:14px;
  font-family:'Reem Kufi';font-size:.7rem;letter-spacing:3.5px;
  color:var(--g1);margin-bottom:12px;
}
.c-over::after{content:'';width:44px;height:1px;background:linear-gradient(to left,transparent,var(--g1))}
.c-sec-h2{font-family:'Amiri';font-size:clamp(2.1rem,4.2vw,3rem);color:var(--cream);line-height:1.2;margin-bottom:10px}
.c-sec-sub{color:var(--cream-dim);font-size:.93rem;line-height:1.75}

/* ══ QUICK NAV ══════════════════════════════════════ */
.c-portals{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.c-portal{
  display:flex;flex-direction:column;align-items:center;gap:14px;
  padding:34px 20px 28px;border-radius:22px;
  background:rgba(255,255,255,.022);
  border:1px solid rgba(196,144,32,.1);
  text-decoration:none;position:relative;overflow:hidden;
  transition:all .35s var(--ease);
}
.c-portal::before{
  content:'';position:absolute;top:0;left:0;right:0;height:1px;
  background:linear-gradient(to right,transparent,rgba(196,144,32,.5),transparent);
  opacity:0;transition:.35s;
}
.c-portal:hover{
  transform:translateY(-8px);
  background:rgba(196,144,32,.058);
  border-color:rgba(196,144,32,.28);
  box-shadow:0 32px 60px -16px rgba(0,0,0,.65),0 0 0 1px rgba(196,144,32,.1);
}
.c-portal:hover::before{opacity:1}
.c-portal-ico{
  width:66px;height:66px;border-radius:18px;
  background:rgba(196,144,32,.08);border:1px solid rgba(196,144,32,.18);
  display:flex;align-items:center;justify-content:center;
  font-size:1.65rem;transition:.35s;
}
.c-portal:hover .c-portal-ico{background:rgba(196,144,32,.18);border-color:rgba(196,144,32,.48);transform:scale(1.06)}
.c-portal b{font-family:'Reem Kufi';font-size:.94rem;color:var(--cream);text-align:center}
.c-portal small{font-size:.72rem;color:var(--cream-ghost);text-align:center}

/* ══ CATEGORIES ════════════════════════════════════ */
.c-bento{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.c-tile{
  border-radius:20px;padding:26px 22px;text-decoration:none;
  border:1px solid rgba(196,144,32,.09);
  background:rgba(255,255,255,.018);
  transition:all .3s var(--ease);position:relative;overflow:hidden;
  display:flex;flex-direction:column;gap:8px;min-height:118px;
}
.c-tile::after{
  content:'';position:absolute;inset:0;
  background:radial-gradient(circle at 0% 100%,rgba(196,144,32,.07),transparent 55%);
  opacity:0;transition:.3s;
}
.c-tile:hover{transform:scale(1.025);border-color:rgba(196,144,32,.32)}
.c-tile:hover::after{opacity:1}
.c-tile .ico{font-size:1.55rem}
.c-tile b{font-family:'Reem Kufi';font-size:.97rem;color:var(--cream);position:relative;z-index:1}
.c-tile small{font-size:.72rem;color:var(--cream-ghost);position:relative;z-index:1}
/* Featured "all" tile */
.c-tile.star{
  grid-column:span 2;
  background:linear-gradient(135deg,rgba(196,144,32,.13),rgba(160,69,32,.09));
  border-color:rgba(196,144,32,.2);min-height:156px;justify-content:flex-end;
}
.c-tile.star::before{
  content:'✦';position:absolute;top:-18px;left:20px;
  font-size:7.5rem;color:rgba(196,144,32,.055);
  font-family:serif;pointer-events:none;line-height:1;z-index:0;
}
.c-tile.star b{font-size:1.18rem}

/* ══ STORY BAND ════════════════════════════════════ */
.c-story{
  padding:110px 0;position:relative;overflow:hidden;
  background:linear-gradient(155deg,#050301 0%,#120803 45%,#080502 100%);
}
.c-story-wm{
  position:absolute;font-family:'Amiri';
  font-size:clamp(7rem,18vw,15rem);
  color:rgba(196,144,32,.025);pointer-events:none;
  top:50%;left:50%;transform:translate(-50%,-50%);
  white-space:nowrap;z-index:0;letter-spacing:22px;
  animation:wmBreathe 9s ease-in-out infinite;
}
.c-story-inner{
  display:grid;grid-template-columns:1fr auto 1fr;
  gap:56px;align-items:center;position:relative;z-index:1;
}
.c-story-divider{width:1px;height:220px;background:linear-gradient(to bottom,transparent,rgba(196,144,32,.24),transparent);flex-shrink:0}
.c-story-h{font-family:'Amiri';font-size:clamp(1.85rem,3.5vw,2.8rem);color:var(--cream);line-height:1.25;margin-bottom:18px}
.c-story-p{color:var(--cream-dim);font-size:1rem;line-height:1.9}
.c-chips{display:flex;flex-wrap:wrap;gap:10px;margin-top:24px}
.c-chip{
  padding:7px 17px;border-radius:30px;
  border:1px solid rgba(196,144,32,.2);
  background:rgba(196,144,32,.05);
  font-family:'Reem Kufi';font-size:.77rem;
  color:rgba(196,144,32,.84);transition:.22s;cursor:default;
}
.c-chip:hover{background:rgba(196,144,32,.12);border-color:rgba(196,144,32,.44)}
.c-brand-focal{text-align:center}
.c-brand-focal .name{
  font-family:'Amiri';
  font-size:clamp(2.6rem,5.5vw,4.4rem);
  color:var(--g1);text-shadow:0 0 80px rgba(196,144,32,.3);
  line-height:1.1;margin-bottom:14px;
}
.c-brand-focal .addr{color:var(--cream-ghost);font-size:.87rem;margin-bottom:18px}
.c-brand-focal .phone{
  display:inline-flex;align-items:center;gap:8px;
  color:var(--g2);font-weight:700;font-size:1.05rem;text-decoration:none;
  border-bottom:1px solid rgba(196,144,32,.3);padding-bottom:3px;transition:.24s;
}
.c-brand-focal .phone:hover{color:#fff;border-color:rgba(255,255,255,.45)}

/* ══ OFFERS ════════════════════════════════════════ */
.c-offer-card{
  border-radius:24px;overflow:hidden;margin-bottom:32px;
  border:1px solid rgba(196,144,32,.13);
  background:rgba(255,255,255,.018);
  box-shadow:0 40px 80px -20px rgba(0,0,0,.65);
}
.c-offer-banner{height:156px;background-size:cover;background-position:center;position:relative}
.c-offer-banner::after{
  content:'';position:absolute;inset:0;
  background:linear-gradient(to bottom,transparent 30%,rgba(6,4,2,.93));
}
.c-offer-body{padding:28px 30px 32px}
.c-offer-head{display:flex;justify-content:space-between;align-items:start;gap:20px;margin-bottom:28px;flex-wrap:wrap}
.c-offer-head h3{font-family:'Amiri';font-size:1.6rem;color:var(--cream)}
.c-offer-head p{color:var(--cream-dim);font-size:.9rem;margin-top:6px;max-width:520px}
.c-offer-meta{display:flex;flex-direction:column;gap:8px;align-items:flex-end}
.c-disc{
  background:linear-gradient(135deg,var(--copper),#be4818);
  color:#fff;padding:8px 18px;border-radius:20px;
  font-weight:700;font-family:'Reem Kufi';font-size:.82rem;
  box-shadow:0 6px 20px -4px rgba(160,69,32,.5);white-space:nowrap;
}
.c-timer{
  background:rgba(196,144,32,.1);border:1px solid rgba(196,144,32,.22);
  color:var(--g2);padding:7px 14px;border-radius:20px;
  font-size:.76rem;font-family:'Reem Kufi';white-space:nowrap;
}

/* ══ PRODUCT CARDS — dark zone overrides ═══════════ */
.dark-z .card{
  background:rgba(255,255,255,.025)!important;
  border-color:rgba(196,144,32,.11)!important;
  box-shadow:0 20px 50px -14px rgba(0,0,0,.55)!important;
}
.dark-z .card:hover{
  border-color:rgba(196,144,32,.28)!important;
  box-shadow:0 30px 60px -14px rgba(0,0,0,.65)!important;
}
.dark-z .card h3{color:var(--cream)!important}
.dark-z .card .desc{color:var(--cream-dim)!important}
.dark-z .card .price{color:var(--g2)!important}
.dark-z .price-compare{color:var(--cream-ghost)!important}
.dark-z .card .price small{color:var(--cream-ghost)!important}
.dark-z .badge-cat{background:rgba(14,8,4,.92)!important;color:var(--cream)!important}
.dark-z .unit-opt{background:rgba(255,255,255,.04)!important;border-color:rgba(196,144,32,.18)!important;color:var(--cream-dim)!important}
.dark-z .unit-opt.sel{background:var(--g1)!important;border-color:var(--g1)!important;color:var(--void)!important}
.dark-z .unit-opt.sel small{color:rgba(6,4,2,.65)!important}
.dark-z .stepper button{background:rgba(255,255,255,.04)!important;border-color:rgba(196,144,32,.18)!important;color:var(--cream)!important}
.dark-z .stepper span{color:var(--cream)!important}
.dark-z .unit-chip{color:var(--cream-dim)!important}
.dark-z .add-btn{background:rgba(196,144,32,.11)!important;color:var(--cream)!important;border:1px solid rgba(196,144,32,.24)!important}
.dark-z .add-btn:hover:not(:disabled){background:var(--g1)!important;color:var(--void)!important}
.dark-z .add-btn.added{background:rgba(58,96,48,.7)!important;color:#a0e0a0!important}
.dark-z .card .thumb{background:rgba(0,0,0,.25)!important}
.dark-z .card .thumb .no-img{color:rgba(196,144,32,.18)!important}

/* view-all link */
.c-view-all{text-align:center;padding:14px 0 8px}

/* ══ RESPONSIVE ════════════════════════════════════ */
@media(max-width:900px){
  .c-hero-inner{grid-template-columns:1fr;padding:88px 0 64px}
  .c-orb-scene{order:-1;width:290px;height:290px;margin:0 auto}
  .c-orb-core{width:136px;height:136px}
  .c-orb-seal{font-size:3.8rem}
  .c-spice:nth-child(6),.c-spice:nth-child(8){display:none}
  .c-portals{grid-template-columns:repeat(2,1fr)}
  .c-bento{grid-template-columns:repeat(2,1fr)}
  .c-story-inner{grid-template-columns:1fr;gap:40px}
  .c-story-divider{display:none}
  .c-brand-focal{text-align:start}
}
@media(max-width:540px){
  .c-h1{font-size:2.6rem}
  .c-portals{grid-template-columns:1fr 1fr}
  .c-bento{grid-template-columns:1fr 1fr}
  .c-orb-scene{width:240px;height:240px}
  .c-stats-row{gap:20px}
  .c-sec{padding:64px 0}
  .c-spice{min-width:100px;padding:10px 12px}
  .c-orb-scene .c-spice:nth-child(7){display:none}
}
</style>
@endpush

@section('content')

{{-- ══ HERO ══ --}}
<section class="c-hero">
  <div class="c-hero-bg"></div>
  <div class="c-wm" aria-hidden="true">عطارة</div>

  <div class="wrap c-hero-inner">

    {{-- Text column --}}
    <div>
      <div class="c-eyebrow">{{ $shop['tagline'] }}</div>
      <h1 class="c-h1">{{ $shop['hero_title'] }}</h1>
      <p class="c-lead">{{ $shop['hero_subtitle'] }}</p>
      <div class="c-cta">
        <a href="{{ route('storefront.catalog') }}" class="c-btn-gold">
          تسوّق الآن
          <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
          </svg>
        </a>
        <a href="{{ route('storefront.offers') }}" class="c-btn-ghost">العروض الحصرية</a>
      </div>
      <div class="c-stats-row">
        <div class="c-stat">
          <div class="num">{{ $categories->count() ?: '٨' }}+</div>
          <div class="lbl">تصنيف</div>
        </div>
        <div class="c-stat">
          <div class="num">{{ count($featured) ?: '٥٠' }}+</div>
          <div class="lbl">منتج</div>
        </div>
        <div class="c-stat">
          <div class="num">{{ count($shop['delivery_cities']) }}</div>
          <div class="lbl">مدن توصيل</div>
        </div>
      </div>
    </div>

    {{-- Orb visual --}}
    <div style="display:flex;align-items:center;justify-content:center">
      <div class="c-orb-scene">
        <div class="c-ring"></div>
        <div class="c-ring"></div>
        <div class="c-ring"></div>

        <div class="c-orb-core">
          @if($shop['logo_url'])
            <img src="{{ $shop['logo_url'] }}" alt="{{ $shop['name'] }}">
          @else
            <span class="c-orb-seal">ع</span>
          @endif
        </div>

        <div class="c-spice">
          <span class="ic">🌶</span>
          <div><div class="c-spice-name">الفلفل الحار</div><div class="c-spice-sub">طازج يومياً</div></div>
        </div>
        <div class="c-spice">
          <span class="ic">🫚</span>
          <div><div class="c-spice-name">زيت البذرة</div><div class="c-spice-sub">معصور بارد</div></div>
        </div>
        <div class="c-spice">
          <span class="ic">🌿</span>
          <div><div class="c-spice-name">الزعتر</div><div class="c-spice-sub">جبلي أصيل</div></div>
        </div>
        <div class="c-spice">
          <span class="ic">☕</span>
          <div><div class="c-spice-name">القهوة</div><div class="c-spice-sub">تحميص خاص</div></div>
        </div>
      </div>
    </div>
  </div>

  <div class="c-scroll-cue" aria-hidden="true">
    <div class="c-scroll-line"></div>
    <div class="c-scroll-txt">تمرير</div>
  </div>
</section>

{{-- ══ TICKER ══ --}}
<div class="c-ticker" aria-hidden="true">
  <div class="c-ticker-track">
    @for($i = 0; $i < 2; $i++)
      <span class="c-tick-item">بهارات طازجة يوميًا <span class="c-tick-sep">◆</span></span>
      <span class="c-tick-item">توصيل {{ implode(' و', $shop['delivery_cities']) }} <span class="c-tick-sep">◆</span></span>
      <span class="c-tick-item">دفع كاش أو إنستاباي أو فودافون كاش <span class="c-tick-sep">◆</span></span>
      <span class="c-tick-item">جودة مضمونة من {{ $shop['name'] }} <span class="c-tick-sep">◆</span></span>
      <span class="c-tick-item">دعم واتساب على مدار اليوم <span class="c-tick-sep">◆</span></span>
    @endfor
  </div>
</div>

{{-- ══ QUICK NAV ══ --}}
<section class="c-sec c-sec-ember">
  <div class="wrap">
    <div class="c-portals">
      <a href="{{ route('storefront.catalog') }}" class="c-portal" data-r data-d="1">
        <div class="c-portal-ico">🛒</div>
        <b>المنتجات</b>
        <small>تصفّح الكتالوج كاملاً</small>
      </a>
      <a href="{{ route('storefront.offers') }}" class="c-portal" data-r data-d="2">
        <div class="c-portal-ico">🏷</div>
        <b>العروض</b>
        <small>خصومات حصرية</small>
      </a>
      <a href="{{ route('storefront.track.lookup') }}" class="c-portal" data-r data-d="3">
        <div class="c-portal-ico">📦</div>
        <b>تتبّع طلبك</b>
        <small>رقم الطلب + الهاتف</small>
      </a>
      <a href="{{ \App\Support\ShopSettings::whatsappUrl('مرحبًا، أريد الاستفسار') }}" class="c-portal" data-r data-d="4" target="_blank" rel="noopener">
        <div class="c-portal-ico">💬</div>
        <b>واتساب</b>
        <small>رد في دقائق</small>
      </a>
    </div>
  </div>
</section>

{{-- ══ CATEGORIES ══ --}}
@if($categories->count())
<section class="c-sec c-sec-dark">
  <div class="wrap">
    <div data-r style="margin-bottom:44px">
      <div class="c-over">تسوّق حسب التصنيف</div>
      <h2 class="c-sec-h2">ماذا تبحث اليوم؟</h2>
    </div>
    <div class="c-bento">
      <a href="{{ route('storefront.catalog') }}" class="c-tile star" data-r data-d="1">
        <span class="ico">✦</span>
        <b>كل المنتجات</b>
        <small>بهارات • أعشاب • بقالة • حبوب</small>
      </a>
      @foreach($categories->take(7) as $cat)
      <a href="{{ route('storefront.catalog', ['category' => $cat->slug]) }}" class="c-tile" data-r data-d="{{ ($loop->index % 4) + 1 }}">
        <span class="ico">{{ $cat->icon && !str_starts_with($cat->icon, 'heroicon') ? $cat->icon : '🏷' }}</span>
        <b>{{ $cat->name }}</b>
        <small>عرض المنتجات</small>
      </a>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ══ STORY BAND ══ --}}
<section class="c-story">
  <div class="c-story-wm" aria-hidden="true">{{ $shop['name'] }}</div>
  <div class="wrap c-story-inner">

    <div data-r>
      <h2 class="c-story-h">تراث عطارة<br>منذ أجيال</h2>
      <p class="c-story-p">{{ $shop['description'] }} نختار بعناية كل حبة وكل توابلة، ونوصّل طلبك طازجًا إلى {{ implode(' و', $shop['delivery_cities']) }} في {{ $shop['governorate'] }}.</p>
      <div class="c-chips">
        <span class="c-chip">🌿 بهارات أصلية</span>
        <span class="c-chip">⚖️ بيع بالوزن</span>
        <span class="c-chip">🚚 توصيل سريع</span>
        <span class="c-chip">💳 دفع مرن</span>
      </div>
    </div>

    <div class="c-story-divider"></div>

    <div class="c-brand-focal" data-r data-d="2">
      <div class="name">{{ $shop['name'] }}</div>
      <div class="addr">{{ $shop['address'] }}</div>
      @if($shop['phone'])
        <a href="tel:{{ $shop['phone'] }}" class="phone">
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
          </svg>
          {{ $shop['phone'] }}
        </a>
      @endif
    </div>
  </div>
</section>

{{-- ══ OFFERS ══ --}}
@if(!empty($homePromotions) && count($homePromotions))
<section class="c-sec c-sec-ember">
  <div class="wrap">
    <div data-r style="margin-bottom:44px">
      <div class="c-over">عروض {{ $shop['name'] }}</div>
      <h2 class="c-sec-h2">خصومات حصرية</h2>
      <p class="c-sec-sub">عروض محدودة الوقت على أفضل المنتجات</p>
    </div>

    @foreach($homePromotions as $promo)
    <div class="c-offer-card" data-r>
      @if($promo['banner'])
        <div class="c-offer-banner" style="background-image:url('{{ $promo['banner'] }}')"></div>
      @endif
      <div class="c-offer-body">
        <div class="c-offer-head">
          <div>
            <h3>{{ $promo['name'] }}</h3>
            @if($promo['description'])<p>{{ $promo['description'] }}</p>@endif
          </div>
          <div class="c-offer-meta">
            <span class="c-disc">{{ $promo['discount_label'] }}</span>
            @if($promo['show_countdown'] && $promo['days_remaining'] !== null)
              <span class="c-timer">⏳ {{ $promo['days_remaining'] }} أيام متبقية</span>
            @endif
          </div>
        </div>
        @if(count($promo['products']))
        <div class="grid dark-z">
          @foreach($promo['products'] as $p)
            @include('storefront.partials.product-card', ['p' => $p])
          @endforeach
        </div>
        @endif
      </div>
    </div>
    @endforeach

    <div class="c-view-all">
      <a href="{{ route('storefront.offers') }}" class="c-btn-ghost">كل العروض</a>
    </div>
  </div>
</section>
@endif

{{-- ══ FEATURED ══ --}}
@if(count($featured))
<section class="c-sec c-sec-dark">
  <div class="wrap">
    <div data-r style="margin-bottom:44px">
      <div class="c-over">اختياراتنا</div>
      <h2 class="c-sec-h2">منتجات مميّزة</h2>
      <p class="c-sec-sub">أكثر ما يطلبه زبائننا</p>
    </div>
    <div class="grid dark-z">
      @foreach($featured as $p)
        @include('storefront.partials.product-card', ['p' => $p])
      @endforeach
    </div>
    <div class="c-view-all">
      <a href="{{ route('storefront.catalog') }}" class="c-btn-ghost">
        عرض جميع المنتجات
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7M18 19l-7-7 7-7"/>
        </svg>
      </a>
    </div>
  </div>
</section>
@endif

@endsection

@push('scripts')
<script>
(function(){
  const io = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      if(e.isIntersecting){
        const d = parseInt(e.target.dataset.d || '0') * 80;
        setTimeout(function(){ e.target.classList.add('in'); }, d);
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.1 });
  document.querySelectorAll('[data-r]').forEach(function(el){ io.observe(el); });
})();
</script>
@endpush
