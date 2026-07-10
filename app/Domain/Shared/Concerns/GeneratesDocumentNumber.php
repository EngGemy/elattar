<?php

declare(strict_types=1);

namespace App\Domain\Shared\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * توليد رقم مستند تسلسلي آمن ضد التزامن: ORD-2025-000123
 * يستخدم قفلًا على مستوى الصف لضمان عدم تكرار الرقم.
 */
trait GeneratesDocumentNumber
{
    abstract public static function documentPrefix(): string;

    public static function nextNumber(): string
    {
        $prefix = static::documentPrefix();
        $year   = now()->year;

        return DB::transaction(function () use ($prefix, $year) {
            $last = static::query()
                ->where('number', 'like', "{$prefix}-{$year}-%")
                ->lockForUpdate()
                ->orderByDesc('id')
                ->value('number');

            $seq = $last ? ((int) substr($last, strrpos($last, '-') + 1)) + 1 : 1;

            return sprintf('%s-%d-%06d', $prefix, $year, $seq);
        });
    }
}
