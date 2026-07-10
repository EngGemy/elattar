<?php

declare(strict_types=1);

namespace App\Domain\Sales\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * آلة حالات الطلب.
 *
 *  pending → confirmed → processing → shipped → delivered
 *     │          │            │           │
 *     └──────────┴────────────┴───────────┴──→ cancelled
 *                                    delivered ──→ returned
 *
 * الانتقالات غير المصرّح بها ترمي TransitionNotFound — لا if/else.
 */
abstract class OrderState extends State
{
    abstract public function label(): string;
    abstract public function color(): string;
    abstract public function icon(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)

            ->allowTransition(Pending::class,    Confirmed::class)
            ->allowTransition(Pending::class,    Cancelled::class)

            ->allowTransition(Confirmed::class,  Processing::class)
            ->allowTransition(Confirmed::class,  Cancelled::class)

            ->allowTransition(Processing::class, Shipped::class)
            ->allowTransition(Processing::class, Cancelled::class)

            ->allowTransition(Shipped::class,    Delivered::class)
            ->allowTransition(Shipped::class,    Cancelled::class)

            ->allowTransition(Delivered::class,  Returned::class);
    }

    /** هل تُخصم من المخزون في هذه الحالة؟ */
    public function isFulfilled(): bool
    {
        return $this instanceof Shipped || $this instanceof Delivered;
    }

    public function isFinal(): bool
    {
        return $this instanceof Delivered
            || $this instanceof Cancelled
            || $this instanceof Returned;
    }
}
