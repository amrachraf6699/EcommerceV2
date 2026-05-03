<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Slider;
use App\Models\User;
use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminAuthorizationSeeder::class);

        $this->superAdmin = User::factory()->create(['is_active' => true]);
        $this->superAdmin->assignRole('super-admin');
    }

    public function test_admin_can_create_new_admin_user(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.admins.store'), [
            'name' => 'Catalog Manager',
            'email' => 'catalog@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'admin',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.admins.index'));
        $this->assertDatabaseHas('users', ['email' => 'catalog@example.com', 'is_active' => true]);
        $this->assertTrue(User::where('email', 'catalog@example.com')->firstOrFail()->hasRole('admin'));
    }

    public function test_admin_can_update_existing_admin_role(): void
    {
        $manager = User::factory()->create(['is_active' => true]);
        $manager->assignRole('admin');

        $this->actingAs($this->superAdmin)->put(route('admin.admins.update', $manager), [
            'name' => 'Updated Manager',
            'email' => 'manager@example.com',
            'role' => 'super-admin',
            'is_active' => '1',
        ])->assertRedirect(route('admin.admins.index'));

        $manager->refresh();
        $this->assertSame('Updated Manager', $manager->name);
        $this->assertTrue($manager->hasRole('super-admin'));
    }

    public function test_last_super_admin_cannot_be_deactivated(): void
    {
        $this->actingAs($this->superAdmin)->delete(route('admin.admins.destroy', $this->superAdmin))
            ->assertSessionHasErrors('role');

        $this->superAdmin->refresh();
        $this->assertTrue($this->superAdmin->is_active);
    }

    public function test_role_permissions_can_be_updated(): void
    {
        $role = Role::findByName('admin', 'web');

        $this->actingAs($this->superAdmin)->put(route('admin.roles.update', $role), [
            'name' => 'admin',
            'permissions' => ['dashboard.view', 'settings.view', 'settings.update'],
        ])->assertRedirect(route('admin.roles.index'));

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('settings.update'));
    }

    public function test_user_without_admin_create_permission_cannot_create_admins(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo('dashboard.view', 'admins.view');

        $this->actingAs($user)->post(route('admin.admins.store'), [
            'name' => 'No Access',
            'email' => 'no-access@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'admin',
        ])->assertForbidden();
    }

    public function test_admin_can_manage_customers(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.customers.store'), [
            'name' => 'Sara Ali',
            'email' => 'sara@example.com',
            'phone' => '01000000000',
            'country' => 'Egypt',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.customers.index'));
        $this->assertDatabaseHas('customers', ['email' => 'sara@example.com', 'country' => 'Egypt']);

        $customer = Customer::where('email', 'sara@example.com')->firstOrFail();

        $this->actingAs($this->superAdmin)->put(route('admin.customers.update', $customer), [
            'name' => 'Sara Hassan',
            'email' => 'sara@example.com',
            'phone' => '01111111111',
            'country' => 'Saudi Arabia',
            'is_active' => '1',
        ])->assertRedirect(route('admin.customers.index'));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Sara Hassan',
            'country' => 'Saudi Arabia',
        ]);

        $this->actingAs($this->superAdmin)->delete(route('admin.customers.destroy', $customer))
            ->assertRedirect(route('admin.customers.index'));

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_admin_can_manage_sliders(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->superAdmin)->post(route('admin.sliders.store'), [
            'title' => 'Hero Slide',
            'subtitle' => 'Main message',
            'image' => UploadedFile::fake()->image('hero.png'),
            'link' => null,
            'text_color' => '#111111',
            'button_background_color' => '#000000',
            'button_text_color' => '#ffffff',
            'horizontal_align' => 'center',
            'vertical_align' => 'bottom',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.sliders.index'));
        $slider = Slider::firstOrFail();
        $this->assertNull($slider->link);
        Storage::disk('public')->assertExists($slider->image);

        $oldImage = $slider->image;

        $this->actingAs($this->superAdmin)->put(route('admin.sliders.update', $slider), [
            'title' => 'Updated Slide',
            'subtitle' => 'Updated message',
            'image' => UploadedFile::fake()->image('hero-2.png'),
            'link' => null,
            'text_color' => '#222222',
            'button_background_color' => '#333333',
            'button_text_color' => '#eeeeee',
            'horizontal_align' => 'right',
            'vertical_align' => 'top',
            'is_active' => '0',
        ])->assertRedirect(route('admin.sliders.index'));

        $slider->refresh();
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($slider->image);
        $this->assertSame('Updated Slide', $slider->title);
        $this->assertNull($slider->link);
        $this->assertSame('right', $slider->horizontal_align);
        $this->assertFalse($slider->is_active);

        $imagePath = $slider->image;

        $this->actingAs($this->superAdmin)->delete(route('admin.sliders.destroy', $slider))
            ->assertRedirect(route('admin.sliders.index'));

        Storage::disk('public')->assertMissing($imagePath);
        $this->assertDatabaseMissing('sliders', ['id' => $slider->id]);
    }
}
