<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Inventory\Models\StockLevel;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Inventory\Models\StockReservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/** تصفير أرصدة المخزون — يُبقي المنتجات والأسماء كما هي */
class ZeroStockLevels extends Command
{
    protected $signature = 'inventory:zero-stock
                            {--clear-movements : حذف سجل حركات المخزون أيضًا}
                            {--force : تنفيذ بدون تأكيد}';

    protected $description = 'تصفير كميات المخزون (on_hand و reserved) مع الإبقاء على المنتجات';

    public function handle(): int
    {
        $levelsCount = StockLevel::where(function ($q) {
            $q->where('on_hand', '>', 0)->orWhere('reserved', '>', 0);
        })->count();

        $reservationsCount = StockReservation::where('status', 'active')->count();
        $movementsCount    = StockMovement::count();

        $this->info('ملخص قبل التصفير:');
        $this->line("  • أرصدة بكمية غير صفرية: {$levelsCount}");
        $this->line("  • حجوزات نشطة: {$reservationsCount}");
        $this->line('  • حركات مخزون: ' . ($this->option('clear-movements') ? "سيتم حذف {$movementsCount}" : 'لن تُمس'));

        if (! $this->option('force') && ! $this->confirm('هل تريد تصفير كل الكميات؟', false)) {
            $this->warn('تم الإلغاء.');

            return self::SUCCESS;
        }

        DB::transaction(function () {
            StockReservation::where('status', 'active')->update(['status' => 'released']);

            StockLevel::query()->update([
                'on_hand'           => 0,
                'reserved'          => 0,
                'last_movement_at'  => null,
            ]);

            if ($this->option('clear-movements')) {
                StockMovement::query()->delete();
            }
        });

        $this->newLine();
        $this->info('تم تصفير المخزون بنجاح.');
        $this->line('  • المنتجات والأسماء والفئات لم تُمس.');
        $this->line('  • يمكنك إدخال الكميات الحقيقية من: المخزون ← تسوية جرد');

        return self::SUCCESS;
    }
}
