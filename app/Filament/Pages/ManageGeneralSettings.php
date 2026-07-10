<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Clusters\SettingsCluster;
use App\Settings\GeneralSettings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageGeneralSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster         = SettingsCluster::class;
    protected static ?string $navigationIcon  = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'الإعدادات العامة';
    protected static ?string $title           = 'إعدادات المتجر';
    protected static ?int    $navigationSort  = 1;

    protected static string $view = 'filament.pages.manage-general-settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return ! auth()->user()?->isCashier();
    }

    public function mount(): void
    {
        $s = app(GeneralSettings::class);

        $this->form->fill([
            'shop_name'        => $s->shop_name,
            'shop_tagline'       => $s->shop_tagline,
            'shop_description'   => $s->shop_description,
            'logo_path'          => $s->logo_path ? [$s->logo_path] : [],
            'phone'              => $s->phone,
            'whatsapp'           => $s->whatsapp,
            'instapay'           => $s->instapay,
            'vodafone_cash'      => $s->vodafone_cash,
            'address'            => $s->address,
            'governorate'        => $s->governorate,
            'delivery_cities'    => $s->delivery_cities,
            'hero_title'         => $s->hero_title,
            'hero_subtitle'      => $s->hero_subtitle,
            'footer_note'        => $s->footer_note,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('الهوية والشعار')->schema([
                    TextInput::make('shop_name')
                        ->label('اسم المتجر')
                        ->required()
                        ->maxLength(120),
                    TextInput::make('shop_tagline')
                        ->label('الشعار الفرعي')
                        ->maxLength(160),
                    Textarea::make('shop_description')
                        ->label('وصف المتجر (SEO)')
                        ->rows(2)
                        ->maxLength(300),
                    FileUpload::make('logo_path')
                        ->label('الشعار')
                        ->image()
                        ->disk('public')
                        ->directory('shop')
                        ->imageEditor()
                        ->maxFiles(1)
                        ->maxSize(2048)
                        ->helperText('يظهر في المتجر ولوحة التحكم. الحجم المثالي: مربع 512×512'),
                ])->columns(2),

                Section::make('التواصل والدفع')->schema([
                    TextInput::make('phone')
                        ->label('هاتف المتجر')
                        ->tel()
                        ->maxLength(20),
                    TextInput::make('whatsapp')
                        ->label('واتساب (بدون +)')
                        ->required()
                        ->maxLength(20)
                        ->helperText('مثال: 201012345678'),
                    TextInput::make('instapay')
                        ->label('رقم إنستاباي')
                        ->maxLength(20),
                    TextInput::make('vodafone_cash')
                        ->label('رقم فودافون كاش')
                        ->maxLength(20),
                    TextInput::make('address')
                        ->label('العنوان')
                        ->maxLength(200)
                        ->columnSpanFull(),
                ])->columns(2),

                Section::make('التوصيل')->schema([
                    TextInput::make('governorate')
                        ->label('المحافظة')
                        ->required()
                        ->maxLength(60),
                    TagsInput::make('delivery_cities')
                        ->label('مدن التوصيل')
                        ->placeholder('أضف مدينة')
                        ->required(),
                ])->columns(2),

                Section::make('الصفحة الرئيسية')->schema([
                    TextInput::make('hero_title')
                        ->label('عنوان البانر')
                        ->maxLength(160)
                        ->columnSpanFull(),
                    Textarea::make('hero_subtitle')
                        ->label('نص البانر')
                        ->rows(2)
                        ->maxLength(300)
                        ->columnSpanFull(),
                    TextInput::make('footer_note')
                        ->label('ملاحظة التذييل')
                        ->maxLength(120)
                        ->columnSpanFull(),
                ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = app(GeneralSettings::class);

        if (isset($data['logo_path']) && is_array($data['logo_path'])) {
            $data['logo_path'] = $data['logo_path'][0] ?? null;
        }

        foreach ($data as $key => $value) {
            if (! property_exists($settings, $key)) {
                continue;
            }

            $settings->{$key} = match ($key) {
                'delivery_cities' => is_array($value) ? array_values($value) : [],
                'logo_path'       => is_array($value) ? ($value[0] ?? null) : $value,
                'phone', 'instapay', 'vodafone_cash' => $value !== null && $value !== '' ? (string) $value : null,
                default           => $value ?? '',
            };
        }

        $settings->save();

        Notification::make()
            ->title('تم حفظ الإعدادات')
            ->success()
            ->send();
    }
}
