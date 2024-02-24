<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {

        $this->migrator->add('platform.site_name', 'ForexFreeFunds');
        $this->migrator->add('platform.site_description', 'ForexFreeFunds');
        $this->migrator->add('platform.lock_purchases', false);
        $this->migrator->add('platform.lock_withdrawals', false);
        $this->migrator->add('platform.lock_referrals', false);
        $this->migrator->add('platform.challenge_purchase_description', "Purchase a product to get started");
        $this->migrator->add('platform.affiliate_percentage', 10);
        $this->migrator->add('platform.affiliate_minimum_withdrawal', 1500);
        $this->migrator->add('platform.minimum_withdrawal', 1500);

        // product prices

        $this->migrator->add('platform.product_one_price', 10000);
        $this->migrator->add('platform.product_one_title', "$1000 Challenge");
        $this->migrator->add('platform.product_one_description', "$1000 Challenge");

        $this->migrator->add('platform.product_two_price', 20000);
        $this->migrator->add('platform.product_two_title', "$20,000 Challenge");
        $this->migrator->add('platform.product_two_description', "$20,000 Challenge");

        $this->migrator->add('platform.product_three_price', 30000);
        $this->migrator->add('platform.product_three_title', "$30,000 Challenge");
        $this->migrator->add('platform.product_three_description', "$30,000 Challenge");
    }
};
