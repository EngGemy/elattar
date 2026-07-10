<?php

declare(strict_types=1);

namespace App\Domain\Pos\Actions;

use App\Domain\Pos\Models\Register;
use App\Domain\Pos\Models\RegisterSession;
use App\Domain\Shared\ValueObjects\Money;
use RuntimeException;

/** فتح شيفت — كاشير واحد لكل صندوق في اللحظة الواحدة */
final class OpenRegisterSessionAction
{
    public function execute(Register $register, Money $openingFloat): RegisterSession
    {
        if ($register->hasOpenSession()) {
            throw new RuntimeException("الصندوق «{$register->name}» به شيفت مفتوح بالفعل. أغلقه أولًا.");
        }

        return RegisterSession::create([
            'register_id'         => $register->id,
            'user_id'             => auth()->id(),
            'opened_at'           => now(),
            'opening_float_minor' => $openingFloat->minor,
            'status'              => 'open',
        ]);
    }
}
