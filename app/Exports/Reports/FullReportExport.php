<?php

declare(strict_types=1);

namespace App\Exports\Reports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/** تصدير شامل — كل أقسام التقرير في ملف Excel واحد */
final class FullReportExport implements WithMultipleSheets
{
    /** @param array<int, ReportSheetExport> $sheets */
    public function __construct(private array $sheets) {}

    public function sheets(): array
    {
        return $this->sheets;
    }
}
