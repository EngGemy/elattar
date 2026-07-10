<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Sales\Events\OrderPlaced;
use App\Domain\Shared\Enums\SalesChannel;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewOrderAlert implements ShouldQueue
{
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;

        if ($order->channel !== SalesChannel::Online) {
            return;
        }

        $recipients = User::role(['admin', 'cashier'])->get();

        Notification::make()
            ->title('طلب جديد من المتجر')
            ->success()
            ->body("طلب {$order->number} — {$order->total_minor->format()}")
            ->actions([
                Action::make('view')
                    ->label('عرض الطلب')
                    ->url(route('filament.admin.sales.resources.orders.view', ['record' => $order->id])),
            ])
            ->sendToDatabase($recipients);
    }
}
