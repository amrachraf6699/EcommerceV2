<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StorefrontContactMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::query()->create([
            'group' => 'brand',
            'key' => 'name',
            'label' => 'Brand name',
            'value' => 'SunFlower',
            'input_type' => 'text',
            'sort_order' => 1,
        ]);
    }

    public function test_contact_page_renders_and_stores_message_without_recaptcha_when_keys_are_missing(): void
    {
        $this->get(route('storefront.contact.show'))
            ->assertOk();

        $this->post(route('storefront.contact.store'), [
            'name' => 'Amr Ashraf',
            'phone' => '201000000000',
            'email' => 'amr@example.com',
            'subject' => 'Need help',
            'message' => 'Please contact me back.',
        ])->assertRedirect(route('storefront.contact.show'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Amr Ashraf',
            'phone' => '201000000000',
            'email' => 'amr@example.com',
            'subject' => 'Need help',
        ]);
    }

    public function test_contact_submission_requires_valid_recaptcha_when_keys_exist(): void
    {
        Setting::query()->insert([
            [
                'group' => 'security',
                'key' => 'recaptcha_site_key',
                'label' => 'Site key',
                'value' => 'site-key',
                'input_type' => 'text',
                'sort_order' => 10,
            ],
            [
                'group' => 'security',
                'key' => 'recaptcha_secret_key',
                'label' => 'Secret key',
                'value' => 'secret-key',
                'input_type' => 'password',
                'sort_order' => 11,
            ],
        ]);

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => false], 200),
        ]);

        $this->from(route('storefront.contact.show'))
            ->post(route('storefront.contact.store'), [
                'name' => 'Amr Ashraf',
                'email' => 'amr@example.com',
                'subject' => 'Need help',
                'message' => 'Please contact me back.',
                'g-recaptcha-response' => 'bad-token',
            ])->assertRedirect(route('storefront.contact.show'))
            ->assertSessionHasErrors('g-recaptcha-response');

        $this->assertDatabaseCount('contact_messages', 0);
    }
}
