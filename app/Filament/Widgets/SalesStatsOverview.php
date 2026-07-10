<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Inventory\Models\StockLevel;
use App\Domain\Reports\Actions\SalesReportAction;
use App\Domain\Shared\ValueObjects\Money;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** مؤشرات الأداء الرئيسية — أبيض/أسود، الأرقام والنسب فقط */
class SalesStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return ! auth()->user()?->isCashier();
    }

    protected function getStats(): array
    {
        $report = app(SalesReportAction::class)
            ->summary(now()->startOfMonth(), now()->endOfDay());

        $c = $report['current'];
        $g = $report['growth'];

        $lowStock = StockLevel::lowStock()->count();

        return [
            Stat::make('صافي المبيعات', Money::ofMinor((int) $c['net_minor'])->format())
                ->description($this->trend($g['net']) . ' عن الفترة السابقة')
                ->descriptionIcon($g['net'] >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($g['net'] >= 0 ? 'success' : 'danger'),

            Stat::make('مجمل الربح', Money::ofMinor((int) $c['profit_minor'])->format())
                ->description("هامش {$c['gp_percent']}%")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($c['gp_percent'] >= 25 ? 'success' : ($c['gp_percent'] >= 15 ? 'warning' : 'danger')),

            Stat::make('عدد الطلبات', number_format((int) $c['orders_count']))
                ->description($this->trend($g['orders']))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color($g['orders'] >= 0 ? 'success' : 'danger'),

            Stat::make('متوسط قيمة الطلب', Money::ofMinor((int) $c['aov_minor'])->format())
                ->description($this->trend($g['aov']))
                ->descriptionIcon('heroicon-m-calculator')
                ->color($g['aov'] >= 0 ? 'success' : 'danger'),

            Stat::make('تنبيهات المخزون', number_format($lowStock))
                ->description($lowStock > 0 ? 'صنف تحت حد إعادة الطلب' : 'لا تنبيهات')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStock > 0 ? 'danger' : 'success'),
        ];
    }

    private function trend(float $pct): string
    {
        $sign = $pct >= 0 ? '+' : '';

        return "{$sign}{$pct}%";
    }
}
