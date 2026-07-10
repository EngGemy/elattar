<?php

declare(strict_types=1);

namespace App\Support;

use App\Exports\Reports\FullReportExport;
use App\Exports\Reports\ReportSheetExport;
use Carbon\CarbonInterface;

/** يحوّل بيانات التقارير إلى ملفات Excel جاهزة للتصدير */
final class ReportExporter
{
    public static function money(?float $minor): string
    {
        return number_format(($minor ?? 0) / 100, 2);
    }

    public static function filename(string $prefix, CarbonInterface $from, CarbonInterface $to): string
    {
        return sprintf(
            '%s_%s_%s.xlsx',
            $prefix,
            $from->format('Y-m-d'),
            $to->format('Y-m-d'),
        );
    }

    /** @param array<string, mixed> $data */
    public static function sheet(string $type, array $data, CarbonInterface $from, CarbonInterface $to): ReportSheetExport
    {
        return match ($type) {
            'summary'    => self::summarySheet($data, $from, $to),
            'daily'      => self::dailySheet($data['dailyTrend'] ?? []),
            'products'   => self::productsSheet($data['bestSellers'] ?? []),
            'categories' => self::categoriesSheet($data['byCategory'] ?? []),
            'inventory'  => self::inventorySheet($data['valuation'] ?? []),
            'dead_stock' => self::deadStockSheet($data['deadStock'] ?? []),
            'cashiers'   => self::cashiersSheet($data['cashiers'] ?? []),
            'channels'   => self::channelsSheet($data['channels'] ?? []),
            default      => new ReportSheetExport('تقرير', [''], []),
        };
    }

    /** @param array<string, mixed> $data */
    public static function fullWorkbook(array $data, CarbonInterface $from, CarbonInterface $to): FullReportExport
    {
        return new FullReportExport([
            self::summarySheet($data, $from, $to),
            self::dailySheet($data['dailyTrend'] ?? []),
            self::productsSheet($data['bestSellers'] ?? []),
            self::categoriesSheet($data['byCategory'] ?? []),
            self::channelsSheet($data['channels'] ?? []),
            self::inventorySheet($data['valuation'] ?? []),
            self::deadStockSheet($data['deadStock'] ?? []),
            self::cashiersSheet($data['cashiers'] ?? []),
        ]);
    }

    /** @param array<string, mixed> $data */
    private static function summarySheet(array $data, CarbonInterface $from, CarbonInterface $to): ReportSheetExport
    {
        $c = $data['summary']['current'] ?? [];
        $g = $data['summary']['growth'] ?? [];

        return new ReportSheetExport(
            'ملخص الأداء',
            ['المؤشر', 'القيمة', 'التغيّر %', 'الفترة'],
            [
                ['صافي المبيعات', self::money($c['net_minor'] ?? 0), ($g['net'] ?? 0) . '%', "{$from->format('Y-m-d')} → {$to->format('Y-m-d')}"],
                ['مجمل الربح', self::money($c['profit_minor'] ?? 0), ($g['profit'] ?? 0) . '%', ''],
                ['تكلفة البضاعة', self::money($c['cogs_minor'] ?? 0), '', ''],
                ['هامش الربح', ($c['gp_percent'] ?? 0) . '%', '', ''],
                ['عدد الطلبات', (int) ($c['orders_count'] ?? 0), ($g['orders'] ?? 0) . '%', ''],
                ['متوسط الطلب', self::money($c['aov_minor'] ?? 0), ($g['aov'] ?? 0) . '%', ''],
                ['إجمالي الخصومات', self::money($c['discount_minor'] ?? 0), '', ''],
                ['الضريبة', self::money($c['tax_minor'] ?? 0), '', ''],
            ],
        );
    }

    /** @param array<int, object> $rows */
    private static function dailySheet(array $rows): ReportSheetExport
    {
        return new ReportSheetExport(
            'المبيعات اليومية',
            ['التاريخ', 'الطلبات', 'صافي المبيعات', 'الربح', 'ت. البضاعة', 'الخصومات'],
            array_map(fn ($r) => [
                $r->date,
                (int) $r->orders_count,
                self::money($r->net_minor),
                self::money($r->profit_minor),
                self::money($r->cogs_minor),
                self::money($r->discount_minor),
            ], $rows),
        );
    }

    /** @param array<int, object> $rows */
    private static function productsSheet(array $rows): ReportSheetExport
    {
        return new ReportSheetExport(
            'أفضل المنتجات',
            ['#', 'الصنف', 'SKU', 'التصنيف', 'الكمية', 'الإيراد', 'الربح', 'GP%'],
            array_map(fn ($r, $i) => [
                $i + 1,
                $r->product_name,
                $r->sku,
                $r->category_name,
                number_format((float) $r->qty_sold, 2),
                self::money($r->revenue_minor),
                self::money($r->profit_minor),
                $r->gp_percent . '%',
            ], $rows, array_keys($rows)),
        );
    }

    /** @param array<int, object> $rows */
    private static function categoriesSheet(array $rows): ReportSheetExport
    {
        return new ReportSheetExport(
            'الربحية بالتصنيف',
            ['التصنيف', 'الإيراد', 'ت. البضاعة', 'الربح', 'GP%'],
            array_map(fn ($r) => [
                $r->category_name,
                self::money($r->revenue_minor),
                self::money($r->cogs_minor),
                self::money($r->profit_minor),
                $r->gp_percent . '%',
            ], $rows),
        );
    }

    /** @param array<int, object> $rows */
    private static function channelsSheet(array $rows): ReportSheetExport
    {
        $labels = ['pos' => 'نقطة البيع', 'online' => 'المتجر الإلكتروني'];

        return new ReportSheetExport(
            'القنوات',
            ['القناة', 'الطلبات', 'صافي المبيعات', 'الربح', 'GP%'],
            array_map(fn ($r) => [
                $labels[$r->channel] ?? $r->channel,
                (int) $r->orders_count,
                self::money($r->net_minor),
                self::money($r->profit_minor),
                $r->gp_percent . '%',
            ], $rows),
        );
    }

    /** @param array<int, object> $rows */
    private static function inventorySheet(array $rows): ReportSheetExport
    {
        return new ReportSheetExport(
            'تقييم المخزون',
            ['المخزن', 'الصنف', 'SKU', 'الموجود', 'المحجوز', 'المتاح', 'القيمة', 'آخر حركة', 'الحالة'],
            array_map(fn ($r) => [
                $r->warehouse_name,
                $r->product_name,
                $r->sku,
                number_format((float) $r->on_hand, 2),
                number_format((float) $r->reserved, 2),
                number_format((float) $r->available, 2),
                self::money($r->value_minor),
                $r->last_movement_at ?? '—',
                $r->is_dead_stock ? 'راكد' : 'نشط',
            ], $rows),
        );
    }

    /** @param array<int, object> $rows */
    private static function deadStockSheet(array $rows): ReportSheetExport
    {
        return new ReportSheetExport(
            'مخزون راكد',
            ['المخزن', 'الصنف', 'SKU', 'الموجود', 'القيمة', 'آخر حركة'],
            array_map(fn ($r) => [
                $r->warehouse_name,
                $r->product_name,
                $r->sku,
                number_format((float) $r->on_hand, 2),
                self::money($r->value_minor),
                $r->last_movement_at ?? '—',
            ], $rows),
        );
    }

    /** @param array<int, object> $rows */
    private static function cashiersSheet(array $rows): ReportSheetExport
    {
        return new ReportSheetExport(
            'أداء الكاشيرين',
            ['الكاشير', 'الشيفتات', 'الطلبات', 'مبيعات نقدية', 'مبيعات بطاقة', 'صافي الفرق', 'مرات العجز'],
            array_map(fn ($r) => [
                $r->cashier_name,
                (int) $r->sessions_count,
                (int) $r->orders_count,
                self::money($r->cash_sales_minor),
                self::money($r->card_sales_minor),
                self::money($r->total_variance_minor),
                (int) $r->shortage_count,
            ], $rows),
        );
    }
}
