<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Exports\Products\ProductsExport;
use App\Exports\Products\ProductsImportTemplateExport;
use App\Filament\Resources\ProductResource;
use App\Imports\Products\ProductsImport;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('تصدير إكسل')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => Excel::download(
                    new ProductsExport,
                    'products-'.now()->format('Y-m-d-His').'.xlsx'
                )),

            Actions\Action::make('template')
                ->label('قالب الاستيراد')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(fn () => Excel::download(
                    new ProductsImportTemplateExport,
                    'products-import-template.xlsx'
                )),

            Actions\Action::make('import')
                ->label('استيراد إكسل')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('ملف الإكسل')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        ->disk('local')
                        ->directory('imports/products')
                        ->required()
                        ->helperText('المنتج الجديد يُنشأ — وإن وُجد كود المتغير تُحدَّث بياناته وتُضاف الكمية على الرصيد الحالي.'),
                ])
                ->action(function (array $data): void {
                    $relative = $data['file'] ?? null;
                    if (is_array($relative)) {
                        $relative = $relative[0] ?? null;
                    }

                    if (! is_string($relative) || $relative === '') {
                        Notification::make()->title('اختر ملف إكسل')->danger()->send();

                        return;
                    }

                    $path = \Illuminate\Support\Facades\Storage::disk('local')->path($relative);

                    if (! is_file($path)) {
                        Notification::make()->title('الملف غير موجود')->danger()->send();

                        return;
                    }

                    HeadingRowFormatter::default('none');

                    $import = app(ProductsImport::class);
                    Excel::import($import, $path);

                    \Illuminate\Support\Facades\Storage::disk('local')->delete($relative);

                    $body = "جديد: {$import->created} — محدّث: {$import->updated} — متخطّى: {$import->skipped}";
                    if ($import->messages !== []) {
                        $body .= "\n".implode("\n", array_slice($import->messages, 0, 8));
                    }

                    $notification = Notification::make()
                        ->title('اكتمل استيراد المنتجات')
                        ->body($body);

                    if ($import->skipped > 0) {
                        $notification->warning();
                    } else {
                        $notification->success();
                    }

                    $notification->send();
                }),

            Actions\CreateAction::make()->label('منتج جديد'),
        ];
    }
}
