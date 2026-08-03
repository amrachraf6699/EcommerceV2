<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $settings = [
            ['afs_environment', 'AFS Environment', 'sandbox', 'select', 'Choose which AFS environment accepts payments.', json_encode(['sandbox', 'live'])],
            ['afs_sandbox_entity_id', 'AFS Sandbox Entity ID', '', 'text', 'AFS Sandbox entity ID.', null],
            ['afs_sandbox_access_token', 'AFS Sandbox Access Token', '', 'password', 'AFS Sandbox access token.', null],
            ['afs_sandbox_base_url', 'AFS Sandbox Base URL', 'https://eu-test.oppwa.com', 'text', 'AFS Sandbox base URL.', null],
            ['afs_live_entity_id', 'AFS Live Entity ID', '', 'text', 'AFS Live entity ID.', null],
            ['afs_live_access_token', 'AFS Live Access Token', '', 'password', 'AFS Live access token.', null],
            ['afs_live_base_url', 'AFS Live Base URL', 'https://eu-prod.oppwa.com', 'text', 'AFS Live base URL.', null],
            ['afs_brands', 'AFS Payment Brands', '', 'text', 'Whitespace-separated AFS brand codes enabled for this merchant.', null],
        ];

        foreach ($settings as $index => [$key, $label, $value, $inputType, $description, $options]) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'group' => 'payment',
                    'label' => $label,
                    'value' => $value,
                    'input_type' => $inputType,
                    'description' => $description,
                    'options' => $options,
                    'is_public' => false,
                    'sort_order' => 300 + $index,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }

        DB::table('settings')->where('group', 'payment')->whereIn('key', [
            'tap_secret_key', 'tap_public_key', 'tap_webhook_secret',
        ])->delete();
    }

    public function down(): void
    {
        DB::table('settings')->where('group', 'payment')->whereIn('key', [
            'afs_environment',
            'afs_sandbox_entity_id',
            'afs_sandbox_access_token',
            'afs_sandbox_base_url',
            'afs_live_entity_id',
            'afs_live_access_token',
            'afs_live_base_url',
            'afs_brands',
        ])->delete();
    }
};
