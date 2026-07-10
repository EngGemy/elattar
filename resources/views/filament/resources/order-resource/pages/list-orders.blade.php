<x-filament-panels::page
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
    x-data="orderAlertSound({{ \App\Domain\Sales\Models\Order::where('status', 'pending')->count() }})"
    x-init="start()"
>
    <div class="flex flex-col gap-y-6">
        <x-filament-panels::resources.tabs />

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE, scopes: $this->getRenderHookScopes()) }}

        {{ $this->table }}

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
    </div>
</x-filament-panels::page>

@push('scripts')
<script>
function orderAlertSound(initial) {
    return {
        lastPending: initial,
        start() {
            setInterval(() => this.check(), 8000);
        },
        check() {
            const rows = document.querySelectorAll('[wire\\:key*="table.records"] tbody tr');
            let pending = 0;
            rows.forEach(row => {
                if (row.textContent.includes('قيد الانتظار') || row.textContent.includes('pending')) pending++;
            });
            if (pending > this.lastPending) this.playChime();
            this.lastPending = pending;
        },
        playChime() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                [0, 0.15].forEach((delay, i) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.frequency.value = i === 0 ? 880 : 1100;
                    gain.gain.value = 0.08;
                    osc.start(ctx.currentTime + delay);
                    osc.stop(ctx.currentTime + delay + 0.12);
                });
            } catch (e) {}
        },
    };
}
</script>
@endpush
