<?php

declare(strict_types=1);

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class PurchasingCluster extends Cluster
{
    protected static ?string $navigationIcon  = "heroicon-o-truck";
    protected static ?string $navigationLabel = "المشتريات";
    protected static ?string $clusterBreadcrumb = "المشتريات";
    protected static ?int    $navigationSort  = 4;
}
