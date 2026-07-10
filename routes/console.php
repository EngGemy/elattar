<?php

use Illuminate\Support\Facades\Schedule;

// تحرير حجوزات السلال المتروكة
Schedule::command('inventory:release-expired')->everyFiveMinutes();

// بناء جداول التقارير المُجمَّعة لليوم السابق
Schedule::command('reports:rollup')->dailyAt('00:05');