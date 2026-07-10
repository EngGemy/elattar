<?php

declare(strict_types=1);

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class CatalogCluster extends Cluster
{
    protected static ?string $navigationIcon  = "heroicon-o-squares-2x2";
    protected static ?string $navigationLabel = "الكتالوج";
    protected static ?string $clusterBreadcrumb = "الكتالوج";
    protected static ?int    $navigationSort  = 1;
}
