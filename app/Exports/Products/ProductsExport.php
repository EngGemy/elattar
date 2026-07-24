<?php

declare(strict_types=1);

namespace App\Exports\Products;

use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Shared\ValueObjects\Money;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** تصدير كل المنتجات والمتغيّرات مع الكميات والتصنيفات */
final class ProductsExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public static function headingsAr(): array
    {
        return [
            'اسم المنتج',
            'كود المنتج',
            'كود المتغير',
            'الباركود',
            'التصنيف',
            'نوع البيع',
            'الحالة',
            'وحدة القياس',
            'وصف العبوة',
            'السعر (ج.م)',
            'التكلفة (ج.م)',
            'خطوة الكمية',
            'الكمية بالمخزن',
            'وصف مختصر',
            'مميز',
        ];
    }

    public function title(): string
    {
        return 'المنتجات';
    }

    public function headings(): array
    {
        return self::headingsAr();
    }

    public function collection(): Collection
    {
        $warehouseId = (int) (Warehouse::query()->where('is_default', true)->value('id') ?? 0);

        $products = Product::query()
            ->with([
                'category',
                'variants' => fn ($q) => $q->orderByDesc('is_default')->orderBy('id'),
                'variants.stockLevels' => fn ($q) => $warehouseId > 0
                    ? $q->where('warehouse_id', $warehouseId)
                    : $q,
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $rows = collect();

        foreach ($products as $product) {
            $variants = $product->variants;

            if ($variants->isEmpty()) {
                $rows->push($this->mapRow($product, null, 0.0));
                continue;
            }

            foreach ($variants as $variant) {
                $qty = (float) ($variant->stockLevels->first()?->on_hand ?? 0);
                $rows->push($this->mapRow($product, $variant, $qty));
            }
        }

        return $rows;
    }

    private function mapRow(Product $product, mixed $variant, float $qty): array
    {
        $price = $variant?->price_minor instanceof Money
            ? round($variant->price_minor->minor / 100, 2)
            : null;
        $cost = $variant?->cost_minor instanceof Money
            ? round($variant->cost_minor->minor / 100, 2)
            : null;

        return [
            $product->name,
            $product->sku_root,
            $variant?->sku ?? '',
            $variant?->barcode ?? '',
            $product->category?->name ?? '',
            $product->type?->getLabel() ?? '',
            $product->status?->getLabel() ?? '',
            $variant?->unit?->labelAr() ?? '',
            $variant?->unit_label ?? '',
            $price,
            $cost,
            $variant?->step !== null ? (float) $variant->step : '',
            $qty,
            $product->short_description ?? '',
            $product->is_featured ? 'نعم' : 'لا',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '241A11']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            'A2:O5000' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ],
        ];
    }
}
