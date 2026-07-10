<?php

declare(strict_types=1);

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class SettingsCluster extends Cluster
{
    protected static ?string $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'الإعدادات';
    protected static ?string $clusterBreadcrumb = 'الإعدادات';
    protected static ?int    $navigationSort  = 6;
}
