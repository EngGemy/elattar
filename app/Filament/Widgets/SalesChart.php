<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

/** منحنى المبيعات والربح — ٣٠ يومًا. رمادي/أسود فقط. */
class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'المبيعات والربح — آخر ٣٠ يومًا';
    protected static ?int    $sort    = 2;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return ! auth()->user()?->isCashier();
    }

    protected function getData(): array
    {
        $rows = DB::table('daily_sales_summary')
            ->where('date', '>=', now()->subDays(30)->toDateString())
            ->groupBy('date')
            ->selectRaw('date, SUM(net_minor) AS net, SUM(profit_minor) AS profit')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label'           => 'صافي المبيعات (ج.م)',
                    'data'            => $rows->pluck('net')->map(fn ($v) => round($v / 100, 2)),
                    'borderColor'     => '#111827',
                    'backgroundColor' => 'rgba(17,24,39,0.06)',
                    'fill'            => true,
                    'tension'         => 0.3,
                ],
                [
                    'label'           => 'مجمل الربح (ج.م)',
                    'data'            => $rows->pluck('profit')->map(fn ($v) => round($v / 100, 2)),
                    'borderColor'     => '#9CA3AF',
                    'borderDash'      => [6, 4],
                    'fill'            => false,
                    'tension'         => 0.3,
                ],
            ],
            'labels' => $rows->pluck('date'),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
