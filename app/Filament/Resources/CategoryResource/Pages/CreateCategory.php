<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Domain\Catalog\Models\Category;
use App\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['slug'] ?? null) && filled($data['name'] ?? null)) {
            $data['slug'] = Category::makeUniqueSlug((string) $data['name']);
        }

        return $data;
    }
}
