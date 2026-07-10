<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domain\Reports\Actions\SalesReportAction;
use App\Filament\Clusters\ReportsCluster;
use App\Support\ReportExporter;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Url;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * مركز التقارير — ٦ أنواع تقارير احترافية مع تصدير Excel.
 */
class SalesReport extends Page
{
    protected static ?string $cluster         = ReportsCluster::class;
    protected static ?string $navigationIcon  = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'مركز التقارير';
    protected static ?string $title           = 'مركز التقارير';
    protected static ?int    $navigationSort  = 1;

    protected static string $view = 'filament.pages.sales-report';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    #[Url]
    public string $tab = 'overview';

    public static function canAccess(): bool
    {
        return ! auth()->user()?->isCashier();
    }

    public function getHeading(): string
    {
        return '';
    }

    public function mount(): void
    {
        if ($this->from === '') {
            $this->from = now()->startOfMonth()->toDateString();
        }

        if ($this->to === '') {
            $this->to = now()->toDateString();
        }
    }

    public function setPeriod(string $period): void
    {
        $this->to = now()->toDateString();

        $this->from = match ($period) {
            'today'   => now()->toDateString(),
            'week'    => now()->startOfWeek()->toDateString(),
            'month'   => now()->startOfMonth()->toDateString(),
            'quarter' => now()->startOfQuarter()->toDateString(),
            'year'    => now()->startOfYear()->toDateString(),
            default   => now()->startOfMonth()->toDateString(),
        };
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function export(string $type): BinaryFileResponse
    {
        $data = $this->reportData();
        $from = $this->dateFrom();
        $to   = $this->dateTo();

        if ($type === 'all') {
            Notification::make()->title('جاري تحميل التقرير الشامل…')->success()->send();

            return Excel::download(
                ReportExporter::fullWorkbook($data, $from, $to),
                ReportExporter::filename('تقرير_شامل', $from, $to),
            );
        }

        $labels = [
            'summary'    => 'ملخص_الأداء',
            'daily'      => 'المبيعات_اليومية',
            'products'   => 'أفضل_المنتجات',
            'categories' => 'الربحية_بالتصنيف',
            'inventory'  => 'تقييم_المخزون',
            'dead_stock' => 'مخزون_راكد',
            'cashiers'   => 'أداء_الكاشيرين',
            'channels'   => 'القنوات',
        ];

        Notification::make()->title('جاري تحميل ملف Excel…')->success()->send();

        return Excel::download(
            ReportExporter::sheet($type, $data, $from, $to),
            ReportExporter::filename($labels[$type] ?? 'تقرير', $from, $to),
        );
    }

    /** @return array<string, mixed> */
    public function getViewData(): array
    {
        return $this->reportData() + [
            'tab'      => $this->tab,
            'fromDate' => $this->dateFrom(),
            'toDate'   => $this->dateTo(),
        ];
    }

    private function dateFrom(): Carbon
    {
        return Carbon::parse($this->from ?: now()->startOfMonth());
    }

    private function dateTo(): Carbon
    {
        return Carbon::parse($this->to ?: now());
    }

    /** @return array<string, mixed> */
    private function reportData(): array
    {
        $action = app(SalesReportAction::class);
        $from   = $this->dateFrom();
        $to     = $this->dateTo();

        $valuation = $action->inventoryValuation();

        return [
            'summary'     => $action->summary($from, $to),
            'dailyTrend'  => $action->dailyTrend($from, $to),
            'channels'    => $action->channelBreakdown($from, $to),
            'bestSellers' => $action->bestSellers($from, $to, 50),
            'byCategory'  => $action->marginByCategory($from, $to),
            'valuation'   => $valuation,
            'deadStock'   => array_values(array_filter($valuation, fn ($r) => $r->is_dead_stock)),
            'totalValue'  => array_sum(array_column($valuation, 'value_minor')),
            'cashiers'    => $action->cashierPerformance($from, $to),
        ];
    }
}
