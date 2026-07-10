<?php

declare(strict_types=1);

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class SalesCluster extends Cluster
{
    protected static ?string $navigationIcon  = "heroicon-o-shopping-cart";
    protected static ?string $navigationLabel = "المبيعات";
    protected static ?string $clusterBreadcrumb = "المبيعات";
    protected static ?int    $navigationSort  = 3;
}
