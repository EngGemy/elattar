<?php

declare(strict_types=1);

namespace App\Exports\Products;

use App\Domain\Catalog\Models\Category;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** قالب فارغ لإضافة/تحديث المنتجات عبر الإكسل */
final class ProductsImportTemplateExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'قالب المنتجات';
    }

    public function headings(): array
    {
        return ProductsExport::headingsAr();
    }

    public function array(): array
    {
        $category = Category::query()->where('is_active', true)->orderBy('name')->value('name') ?? 'بهارات';

        // صف توضيحي — احذف أو عدّل قبل الرفع
        return [[
            'فلفل أسود',
            'PEPPER-BLK',
            'PEPPER-BLK-1',
            '',
            $category,
            'بالوزن',
            'نشط',
            'جم',
            '',
            180,
            100,
            1,
            5000,
            'فلفل أسود مطحون',
            'لا',
        ]];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B5E20']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
