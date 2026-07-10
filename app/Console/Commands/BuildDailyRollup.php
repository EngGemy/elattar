<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Reports\Actions\BuildDailyRollupAction;
use Carbon\Carbon;
use Illuminate\Console\Command;

/** يعمل الساعة 00:05 لليوم السابق */
class BuildDailyRollup extends Command
{
    protected $signature   = 'reports:rollup {date? : بصيغة Y-m-d، الافتراضي أمس}';
    protected $description = 'بناء جداول التقارير المُجمَّعة';

    public function handle(BuildDailyRollupAction $action): int
    {
        $date = $this->argument('date')
            ? Carbon::parse($this->argument('date'))
            : now()->subDay();

        $action->execute($date);

        $this->info("تم بناء ملخص يوم {$date->toDateString()}.");

        return self::SUCCESS;
    }
}
