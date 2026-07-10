<?php

declare(strict_types=1);

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class ReportsCluster extends Cluster
{
    protected static ?string $navigationIcon  = "heroicon-o-chart-bar";
    protected static ?string $navigationLabel = "التقارير";
    protected static ?string $clusterBreadcrumb = "التقارير";
    protected static ?int    $navigationSort  = 5;
}
