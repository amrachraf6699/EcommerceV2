<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicMailConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_config_uses_admin_settings_values_when_present(): void
    {
        Setting::query()->insert([
            [
                'group' => 'mail',
                'key' => 'mail_host',
                'label' => 'Mail host',
                'value' => 'smtp.admin.test',
                'input_type' => 'text',
                'sort_order' => 1,
            ],
            [
                'group' => 'mail',
                'key' => 'mail_port',
                'label' => 'Mail port',
                'value' => '2525',
                'input_type' => 'number',
                'sort_order' => 2,
            ],
            [
                'group' => 'mail',
                'key' => 'mail_username',
                'label' => 'Mail username',
                'value' => 'admin-user',
                'input_type' => 'text',
                'sort_order' => 3,
            ],
            [
                'group' => 'mail',
                'key' => 'mail_password',
                'label' => 'Mail password',
                'value' => 'secret-pass',
                'input_type' => 'password',
                'sort_order' => 4,
            ],
            [
                'group' => 'mail',
                'key' => 'mail_encryption',
                'label' => 'Mail encryption',
                'value' => 'ssl',
                'input_type' => 'select',
                'sort_order' => 5,
            ],
            [
                'group' => 'mail',
                'key' => 'mail_from_name',
                'label' => 'Mail from name',
                'value' => 'SunFlower Admin',
                'input_type' => 'text',
                'sort_order' => 6,
            ],
            [
                'group' => 'mail',
                'key' => 'mail_from_address',
                'label' => 'Mail from address',
                'value' => 'admin@example.com',
                'input_type' => 'email',
                'sort_order' => 7,
            ],
        ]);

        app()->forgetInstance(\App\Support\SettingsManager::class);
        (new AppServiceProvider(app()))->boot();

        $this->assertSame('smtp.admin.test', config('mail.mailers.smtp.host'));
        $this->assertSame(2525, config('mail.mailers.smtp.port'));
        $this->assertSame('admin-user', config('mail.mailers.smtp.username'));
        $this->assertSame('secret-pass', config('mail.mailers.smtp.password'));
        $this->assertSame('ssl', config('mail.mailers.smtp.encryption'));
        $this->assertSame('admin@example.com', config('mail.from.address'));
        $this->assertSame('SunFlower Admin', config('mail.from.name'));
    }
}
