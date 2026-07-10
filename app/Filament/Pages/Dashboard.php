<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domain\Reports\Actions\DashboardAction;
use App\Filament\Pages\SalesReport;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\PurchaseOrderResource;
use App\Filament\Resources\StockLevelResource;
use App\Support\ReportExporter;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Livewire\Attributes\Url;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** لوحة التحكم الشاملة — Odoo/SAP style */
class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $navigationLabel = 'لوحة التحكم';
    protected static ?string $title           = 'لوحة التحكم';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function getHeading(): string
    {
        return '';
    }

    public function getWidgets(): array
    {
        return [];
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

    public function exportAll(): BinaryFileResponse
    {
        $from = Carbon::parse($this->from);
        $to   = Carbon::parse($this->to);
        $data = $this->dashboardData();

        Notification::make()->title('جاري تحميل التقرير الشامل…')->success()->send();

        return Excel::download(
            ReportExporter::fullWorkbook($data, $from, $to),
            ReportExporter::filename('لوحة_التحكم', $from, $to),
        );
    }

    /** @return array<string, mixed> */
    public function getViewData(): array
    {
        return $this->dashboardData() + [
            'fromDate'  => Carbon::parse($this->from),
            'toDate'    => Carbon::parse($this->to),
            'isCashier' => auth()->user()?->isCashier() ?? false,
            'links'     => [
                'reports'         => SalesReport::getUrl(isAbsolute: false),
                'orders'          => OrderResource::getUrl('index', isAbsolute: false),
                'orders_pending'  => OrderResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'pending']]], isAbsolute: false),
                'inventory'       => StockLevelResource::getUrl('index', isAbsolute: false),
                'purchasing'      => PurchaseOrderResource::getUrl('index', isAbsolute: false),
                'pos'             => \App\Filament\Pages\PosTerminal::getUrl(isAbsolute: false),
                'register_sessions' => \App\Filament\Resources\RegisterSessionResource::getUrl('index', isAbsolute: false),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function dashboardData(): array
    {
        return app(DashboardAction::class)->build(
            Carbon::parse($this->from),
            Carbon::parse($this->to),
        );
    }
}
