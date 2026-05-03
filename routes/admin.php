<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CartController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\WelcomeCouponController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin.access'])->group(function (): void {
    Route::controller(DashboardController::class)->group(function (): void {
        Route::get('/', 'index')->name('dashboard');
        Route::get('/dashboard/export/pdf', 'exportPdf')->middleware('permission:dashboard.view')->name('dashboard.export.pdf');
        Route::get('/dashboard/export/excel', 'exportExcel')->middleware('permission:dashboard.view')->name('dashboard.export.excel');
    });

    Route::controller(ProfileController::class)->group(function (): void {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::put('/profile/password', 'updatePassword')->name('profile.password.update');
    });

    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::prefix('notifications')->as('notifications.')->controller(NotificationController::class)->group(function (): void {
        Route::patch('/read-all', 'markAllRead')->name('read-all');
        Route::patch('/{notification}/read', 'markRead')->name('read');
    });

    Route::prefix('admins')->as('admins.')->controller(AdminUserController::class)->group(function (): void {
        Route::get('/', 'index')->middleware('permission:admins.view')->name('index');
        Route::get('/create', 'create')->middleware('permission:admins.create')->name('create');
        Route::post('/', 'store')->middleware('permission:admins.create')->name('store');
        Route::get('/{admin}/edit', 'edit')->middleware('permission:admins.update')->name('edit');
        Route::put('/{admin}', 'update')->middleware('permission:admins.update')->name('update');
        Route::delete('/{admin}', 'destroy')->middleware('permission:admins.update')->name('destroy');
    });

    Route::prefix('roles')->as('roles.')->controller(RoleController::class)->group(function (): void {
        Route::get('/', 'index')->middleware('permission:roles.view')->name('index');
        Route::get('/create', 'create')->middleware('permission:roles.create')->name('create');
        Route::post('/', 'store')->middleware('permission:roles.create')->name('store');
        Route::get('/{role}/edit', 'edit')->middleware('permission:roles.update')->name('edit');
        Route::put('/{role}', 'update')->middleware('permission:roles.update')->name('update');
        Route::delete('/{role}', 'destroy')->middleware('permission:roles.delete')->name('destroy');
    });

    Route::controller(SettingsController::class)->group(function (): void {
        Route::get('/settings', 'index')->middleware('permission:settings.view')->name('settings.index');
        Route::put('/settings', 'update')->middleware('permission:settings.update')->name('settings.update');
    });

    Route::prefix('pages')->as('pages.')->controller(PageController::class)->group(function (): void {
        Route::get('/', 'index')->middleware('permission:pages.view')->name('index');
        Route::get('/create', 'create')->middleware('permission:pages.create')->name('create');
        Route::post('/', 'store')->middleware('permission:pages.create')->name('store');
        Route::get('/{page}/edit', 'edit')->middleware('permission:pages.update')->name('edit');
        Route::put('/{page}', 'update')->middleware('permission:pages.update')->name('update');
        Route::delete('/{page}', 'destroy')->middleware('permission:pages.delete')->name('destroy');
    });

    Route::prefix('categories')->as('categories.')->controller(CategoryController::class)->group(function (): void {
        Route::get('/', 'index')->middleware('permission:categories.view')->name('index');
        Route::get('/create', 'create')->middleware('permission:categories.create')->name('create');
        Route::post('/', 'store')->middleware('permission:categories.create')->name('store');
        Route::get('/{category}/edit', 'edit')->middleware('permission:categories.update')->name('edit');
        Route::put('/{category}', 'update')->middleware('permission:categories.update')->name('update');
        Route::delete('/{category}', 'destroy')->middleware('permission:categories.delete')->name('destroy');
    });

    Route::prefix('customers')->as('customers.')->controller(CustomerController::class)->group(function (): void {
        Route::get('/', 'index')->middleware('permission:customers.view')->name('index');
        Route::get('/create', 'create')->middleware('permission:customers.create')->name('create');
        Route::post('/', 'store')->middleware('permission:customers.create')->name('store');
        Route::get('/{customer}/edit', 'edit')->middleware('permission:customers.update')->name('edit');
        Route::put('/{customer}', 'update')->middleware('permission:customers.update')->name('update');
        Route::delete('/{customer}', 'destroy')->middleware('permission:customers.delete')->name('destroy');
    });

    Route::prefix('sliders')->as('sliders.')->controller(SliderController::class)->group(function (): void {
        Route::get('/', 'index')->middleware('permission:sliders.view')->name('index');
        Route::get('/create', 'create')->middleware('permission:sliders.create')->name('create');
        Route::post('/', 'store')->middleware('permission:sliders.create')->name('store');
        Route::get('/{slider}/edit', 'edit')->middleware('permission:sliders.update')->name('edit');
        Route::put('/{slider}', 'update')->middleware('permission:sliders.update')->name('update');
        Route::delete('/{slider}', 'destroy')->middleware('permission:sliders.delete')->name('destroy');
    });

    Route::prefix('clients')->as('clients.')->controller(ClientController::class)->group(function (): void {
        Route::get('/', 'index')->middleware('permission:clients.view')->name('index');
        Route::get('/create', 'create')->middleware('permission:clients.create')->name('create');
        Route::post('/', 'store')->middleware('permission:clients.create')->name('store');
        Route::get('/{client}/edit', 'edit')->middleware('permission:clients.update')->name('edit');
        Route::put('/{client}', 'update')->middleware('permission:clients.update')->name('update');
        Route::delete('/{client}', 'destroy')->middleware('permission:clients.delete')->name('destroy');
    });

    Route::prefix('products')->as('products.')->controller(ProductController::class)->group(function (): void {
        Route::get('/', 'index')->middleware('permission:products.view')->name('index');
        Route::get('/create', 'create')->middleware('permission:products.create')->name('create');
        Route::post('/', 'store')->middleware('permission:products.create')->name('store');
        Route::get('/{product}/edit', 'edit')->middleware('permission:products.update')->name('edit');
        Route::put('/{product}', 'update')->middleware('permission:products.update')->name('update');
        Route::delete('/{product}', 'destroy')->middleware('permission:products.delete')->name('destroy');
    });

    Route::prefix('products/{product}/variants')->as('products.variants.')->controller(ProductVariantController::class)->group(function (): void {
        Route::post('/', 'store')->middleware('permission:products.update')->name('store');
        Route::put('/{variant}', 'update')->middleware('permission:products.update')->name('update');
        Route::delete('/{variant}', 'destroy')->middleware('permission:products.update')->name('destroy');
    });

    Route::prefix('products/{product}/images')->as('products.images.')->controller(ProductImageController::class)->group(function (): void {
        Route::post('/', 'store')->middleware('permission:products.update')->name('store');
        Route::put('/{image}', 'update')->middleware('permission:products.update')->name('update');
        Route::delete('/{image}', 'destroy')->middleware('permission:products.update')->name('destroy');
    });

    Route::prefix('orders')->as('orders.')->controller(OrderController::class)->group(function (): void {
        Route::get('/', 'index')->middleware('permission:orders.view')->name('index');
        Route::get('/{order}', 'show')->middleware('permission:orders.view')->name('show');
        Route::put('/{order}', 'update')->middleware('permission:orders.update')->name('update');
    });

    Route::prefix('carts')->as('carts.')->controller(CartController::class)->group(function (): void {
        Route::get('/', 'index')->middleware('permission:carts.view')->name('index');
        Route::get('/{cart}', 'show')->middleware('permission:carts.view')->name('show');
    });

    Route::prefix('welcome-coupons')->as('welcome-coupons.')->controller(WelcomeCouponController::class)->group(function (): void {
        Route::get('/', 'index')->middleware('permission:welcome_coupons.view')->name('index');
    });

    Route::prefix('contact-messages')->as('contact-messages.')->controller(ContactMessageController::class)->group(function (): void {
        Route::get('/', 'index')->middleware('permission:contact_messages.view')->name('index');
        Route::get('/{contactMessage}', 'show')->middleware('permission:contact_messages.view')->name('show');
        Route::post('/{contactMessage}/reply', 'reply')->middleware('permission:contact_messages.update')->name('reply');
        Route::delete('/{contactMessage}', 'destroy')->middleware('permission:contact_messages.delete')->name('destroy');
    });
});
