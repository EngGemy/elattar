<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.shop_name', 'عبد القادر العطّار');
        $this->migrator->add('general.shop_tagline', 'عطارة وبقالة أصيلة');
        $this->migrator->add('general.shop_description', 'أجود البهارات والمنتجات الغذائية — توصيل لكل الدقهلية');
        $this->migrator->add('general.logo_path', null);
        $this->migrator->add('general.phone', '');
        $this->migrator->add('general.whatsapp', env('STOREFRONT_WHATSAPP', '201000000000'));
        $this->migrator->add('general.instapay', env('STOREFRONT_INSTAPAY', '01234567890'));
        $this->migrator->add('general.vodafone_cash', env('STOREFRONT_VODAFONE_CASH', '01012345678'));
        $this->migrator->add('general.address', 'المنصورة — الدقهلية');
        $this->migrator->add('general.governorate', 'الدقهلية');
        $this->migrator->add('general.delivery_cities', ['المنصورة', 'طلخا']);
        $this->migrator->add('general.hero_title', 'بهارات أصيلة من قلب الدقهلية');
        $this->migrator->add('general.hero_subtitle', 'نختار أجود الحبوب والتوابل ونوصّل لباب بيتك في المنصورة وطلخا');
        $this->migrator->add('general.footer_note', 'جميع الحقوق محفوظة');
    }
};
