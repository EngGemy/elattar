<?php

declare(strict_types=1);

namespace App\Imports\Products;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Models\TaxClass;
use App\Domain\Inventory\Actions\SetVariantStockAction;
use App\Domain\Shared\Enums\ProductStatus;
use App\Domain\Shared\Enums\ProductType;
use App\Domain\Shared\Enums\UnitOfMeasure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Validators\Failure;

/**
 * استيراد منتجات من إكسل (عربي).
 * - كود المتغير موجود ⟵ تحديث البيانات + إضافة الكمية على الرصيد الحالي
 * - كود جديد ⟵ إنشاء منتج/متغيّر وتعيين الكمية
 */
final class ProductsImport implements OnEachRow, WithHeadingRow, SkipsEmptyRows, SkipsOnFailure
{
    use SkipsFailures;

    public int $created = 0;
    public int $updated = 0;
    public int $skipped = 0;

    /** @var list<string> */
    public array $messages = [];

    public function __construct(private readonly SetVariantStockAction $setStock) {}

    public function onRow(Row $row): void
    {
        $r = $row->toArray();
        $line = $row->getIndex();

        $name = $this->str($this->val($r, ['اسم_المنتج', 'اسم المنتج', 'name']));
        $skuRoot = $this->str($this->val($r, ['كود_المنتج', 'كود المنتج', 'sku_root', 'sku الجذر']));
        $variantSku = $this->str($this->val($r, ['كود_المتغير', 'كود المتغير', 'sku', 'variant_sku']));

        if ($name === '' && $skuRoot === '' && $variantSku === '') {
            return;
        }

        if ($name === '' || $skuRoot === '') {
            $this->skipped++;
            $this->messages[] = "صف {$line}: اسم المنتج وكود المنتج مطلوبان.";

            return;
        }

        if ($variantSku === '') {
            $variantSku = $skuRoot;
        }

        try {
            DB::transaction(function () use ($r, $name, $skuRoot, $variantSku): void {
                $categoryName = $this->str($this->val($r, ['التصنيف', 'category', 'category_name']));
                $category = $this->resolveCategory($categoryName);

                $type = $this->resolveType($this->val($r, ['نوع_البيع', 'نوع البيع', 'type']));
                $status = $this->resolveStatus($this->val($r, ['الحالة', 'status']));
                $unit = $this->resolveUnit($this->val($r, ['وحدة_القياس', 'وحدة القياس', 'unit']));
                $barcode = $this->str($this->val($r, ['الباركود', 'barcode']));
                $unitLabel = $this->str($this->val($r, ['وصف_العبوة', 'وصف العبوة', 'unit_label']));
                $shortDesc = $this->str($this->val($r, ['وصف_مختصر', 'وصف مختصر', 'short_description']));
                $featured = $this->boolish($this->val($r, ['مميز', 'is_featured']));

                $priceMajor = $this->float($this->val($r, ['السعر_ج_م', 'السعر (ج.م)', 'السعر', 'price']));
                $costMajor = $this->float($this->val($r, ['التكلفة_ج_م', 'التكلفة (ج.م)', 'التكلفة', 'cost']));
                $step = $this->float($this->val($r, ['خطوة_الكمية', 'خطوة الكمية', 'step']));
                $qty = $this->float($this->val($r, ['الكمية_بالمخزن', 'الكمية بالمخزن', 'الكمية', 'stock_qty', 'qty']));

                if ($step <= 0) {
                    $step = (float) $unit->defaultStep();
                }

                $priceMinor = (int) round(max(0, $priceMajor) * 100);
                $costMinor = (int) round(max(0, $costMajor) * 100);

                $existingVariant = ProductVariant::query()
                    ->with('product')
                    ->where('sku', $variantSku)
                    ->first();

                if ($existingVariant) {
                    $product = $existingVariant->product;
                    $product->fill([
                        'name'              => $name,
                        'sku_root'          => $skuRoot,
                        'category_id'       => $category->id,
                        'type'              => $type,
                        'status'            => $status,
                        'short_description' => $shortDesc !== '' ? $shortDesc : $product->short_description,
                        'is_featured'       => $featured,
                    ])->save();

                    $existingVariant->fill([
                        'barcode'     => $barcode !== '' ? $barcode : $existingVariant->barcode,
                        'price_minor' => $priceMinor,
                        'cost_minor'  => $costMinor,
                        'unit'        => $unit,
                        'step'        => $step,
                        'unit_label'  => $unitLabel !== '' ? $unitLabel : $existingVariant->unit_label,
                        'is_active'   => true,
                    ])->save();

                    if ($qty > 0) {
                        $current = $existingVariant->totalOnHand();
                        $this->setStock->execute(
                            variant: $existingVariant,
                            targetQty: $current + $qty,
                            note: 'إضافة كمية من استيراد إكسل',
                        );
                    }

                    $this->updated++;

                    return;
                }

                $product = Product::query()->where('sku_root', $skuRoot)->first();

                if (! $product) {
                    $product = Product::query()->create([
                        'category_id'       => $category->id,
                        'tax_class_id'      => TaxClass::default()?->id,
                        'sku_root'          => $skuRoot,
                        'name'              => $name,
                        'slug'              => $this->uniqueSlug($name, $skuRoot),
                        'type'              => $type,
                        'status'            => $status,
                        'short_description' => $shortDesc !== '' ? $shortDesc : null,
                        'is_featured'       => $featured,
                        'has_variants'      => false,
                        'sort_order'        => 0,
                    ]);
                    $this->created++;
                } else {
                    $product->fill([
                        'name'              => $name,
                        'category_id'       => $category->id,
                        'type'              => $type,
                        'status'            => $status,
                        'short_description' => $shortDesc !== '' ? $shortDesc : $product->short_description,
                        'is_featured'       => $featured,
                    ])->save();
                    $this->updated++;
                }

                $hasDefault = $product->variants()->where('is_default', true)->exists();

                $variant = ProductVariant::query()->create([
                    'product_id'  => $product->id,
                    'sku'         => $variantSku,
                    'barcode'     => $barcode !== '' ? $barcode : null,
                    'price_minor' => $priceMinor,
                    'cost_minor'  => $costMinor,
                    'unit'        => $unit,
                    'step'        => $step,
                    'unit_label'  => $unitLabel !== '' ? $unitLabel : null,
                    'is_default'  => ! $hasDefault,
                    'is_active'   => true,
                ]);

                if ($qty > 0) {
                    $this->setStock->execute(
                        variant: $variant,
                        targetQty: $qty,
                        note: 'رصيد افتتاحي من استيراد إكسل',
                    );
                }
            });
        } catch (\Throwable $e) {
            $this->skipped++;
            $this->messages[] = "صف {$line}: ".$e->getMessage();
        }
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->skipped++;
            $this->messages[] = 'صف '.$failure->row().': '.implode(' — ', $failure->errors());
        }
    }

    /** @param  array<string, mixed>  $row */
    private function val(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        // Maatwebsite يحوّل العناوين أحيانًا لـ snake بدون مسافات
        foreach ($row as $k => $v) {
            $norm = $this->normalizeKey((string) $k);
            foreach ($keys as $key) {
                if ($norm === $this->normalizeKey($key) && $v !== null && $v !== '') {
                    return $v;
                }
            }
        }

        return null;
    }

    private function normalizeKey(string $key): string
    {
        $key = trim(mb_strtolower($key));
        $key = str_replace([' ', '-', '(', ')', '.', 'ـ'], ['_', '_', '', '', '', ''], $key);

        return $key;
    }

    private function str(mixed $v): string
    {
        return trim((string) ($v ?? ''));
    }

    private function float(mixed $v): float
    {
        if ($v === null || $v === '') {
            return 0.0;
        }

        if (is_string($v)) {
            $v = str_replace([',', '٫'], ['.', '.'], $v);
        }

        return (float) $v;
    }

    private function boolish(mixed $v): bool
    {
        if (is_bool($v)) {
            return $v;
        }

        $s = mb_strtolower(trim((string) ($v ?? '')));

        return in_array($s, ['1', 'true', 'yes', 'y', 'نعم', 'مميز'], true);
    }

    private function resolveCategory(string $name): Category
    {
        if ($name === '') {
            $existing = Category::query()->where('is_active', true)->orderBy('id')->first();
            if ($existing) {
                return $existing;
            }

            return Category::query()->create([
                'name'      => 'عام',
                'slug'      => Category::makeUniqueSlug('عام'),
                'is_active' => true,
            ]);
        }

        $found = Category::query()->where('name', $name)->first();
        if ($found) {
            return $found;
        }

        return Category::query()->create([
            'name'      => $name,
            'slug'      => Category::makeUniqueSlug($name),
            'is_active' => true,
        ]);
    }

    private function resolveType(mixed $raw): ProductType
    {
        $s = mb_strtolower(trim((string) ($raw ?? '')));

        return match (true) {
            $s === '' => ProductType::Simple,
            str_contains($s, 'وزن') || $s === ProductType::Weighted->value => ProductType::Weighted,
            str_contains($s, 'متعدد') || $s === ProductType::Variable->value => ProductType::Variable,
            default => ProductType::tryFrom($s) ?? ProductType::Simple,
        };
    }

    private function resolveStatus(mixed $raw): ProductStatus
    {
        $s = mb_strtolower(trim((string) ($raw ?? '')));

        return match (true) {
            $s === '' || str_contains($s, 'نشط') || $s === 'active' => ProductStatus::Active,
            str_contains($s, 'مسود') || $s === 'draft' => ProductStatus::Draft,
            str_contains($s, 'أرش') || str_contains($s, 'ارش') || $s === 'archived' => ProductStatus::Archived,
            default => ProductStatus::tryFrom($s) ?? ProductStatus::Active,
        };
    }

    private function resolveUnit(mixed $raw): UnitOfMeasure
    {
        $s = mb_strtolower(trim((string) ($raw ?? '')));

        return match (true) {
            $s === '' => UnitOfMeasure::Piece,
            $s === 'جم' || $s === 'جرام' || $s === 'gram' || $s === 'g' => UnitOfMeasure::Gram,
            $s === 'كجم' || $s === 'كيلو' || $s === 'kg' || $s === 'كيلوجرام' => UnitOfMeasure::Kg,
            $s === 'لتر' || $s === 'liter' || $s === 'l' => UnitOfMeasure::Liter,
            $s === 'مل' || $s === 'ml' => UnitOfMeasure::Ml,
            $s === 'قطعة' || $s === 'piece' || $s === 'pcs' => UnitOfMeasure::Piece,
            default => UnitOfMeasure::tryFrom($s) ?? UnitOfMeasure::Piece,
        };
    }

    private function uniqueSlug(string $name, string $skuRoot): string
    {
        $base = Str::slug($name, '-', 'ar');
        if ($base === '' || $base === '-') {
            $base = Str::slug($skuRoot, '-') ?: preg_replace('/\s+/u', '-', $skuRoot) ?: 'product';
        }

        $base = mb_strtolower((string) $base);
        $slug = $base;
        $i = 1;

        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
