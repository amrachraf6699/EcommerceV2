<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_topbar_renders_database_notifications(): void
    {
        $this->seed(AdminAuthorizationSeeder::class);

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('super-admin');

        DatabaseNotification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => 'admin.test',
            'notifiable_type' => $admin->getMorphClass(),
            'notifiable_id' => $admin->getKey(),
            'data' => [
                'title' => 'طلب جديد',
                'body' => 'تم إنشاء طلب جديد ويحتاج إلى مراجعة.',
                'url' => route('admin.orders.index'),
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('طلب جديد');
        $response->assertSee('تم إنشاء طلب جديد ويحتاج إلى مراجعة.');
    }

    public function test_admin_can_mark_notifications_as_read(): void
    {
        $this->seed(AdminAuthorizationSeeder::class);

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('super-admin');

        $notification = DatabaseNotification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => 'admin.test',
            'notifiable_type' => $admin->getMorphClass(),
            'notifiable_id' => $admin->getKey(),
            'data' => [
                'title' => 'تنبيه',
                'body' => 'اختبار الإشعارات.',
                'url' => route('admin.dashboard'),
            ],
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.notifications.read', $notification))
            ->assertRedirect(route('admin.dashboard'));

        $notification->refresh();
        $this->assertNotNull($notification->read_at);

        DatabaseNotification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => 'admin.test',
            'notifiable_type' => $admin->getMorphClass(),
            'notifiable_id' => $admin->getKey(),
            'data' => ['title' => 'تنبيه 2'],
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
    }
}
