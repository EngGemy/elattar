<?php

declare(strict_types=1);

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class InventoryCluster extends Cluster
{
    protected static ?string $navigationIcon  = "heroicon-o-cube";
    protected static ?string $navigationLabel = "المخزون";
    protected static ?string $clusterBreadcrumb = "المخزون";
    protected static ?int    $navigationSort  = 2;
}
