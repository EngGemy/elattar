<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Inventory\Events\LowStockDetected;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLowStockAlert implements ShouldQueue
{
    public function handle(LowStockDetected $event): void
    {
        $level   = $event->level;
        $variant = $level->variant;

        $recipients = User::role(['admin', 'warehouse_manager'])->get();

        Notification::make()
            ->title('تنبيه: مخزون منخفض')
            ->warning()
            ->body("الصنف «{$variant->full_name}» وصل إلى {$level->availableQty()} {$variant->unit->labelAr()} في {$level->warehouse->name}.")
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('عرض المخزون')
                    ->url(route('filament.admin.resources.stock-levels.index')),
            ])
            ->sendToDatabase($recipients);
    }
}
