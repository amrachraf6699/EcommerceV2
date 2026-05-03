<?php

use App\Http\Controllers\Frontend\CatalogController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\ChatbotController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\ContactMessageController;
use App\Http\Controllers\Frontend\CustomerAddressController;
use App\Http\Controllers\Frontend\CustomerAuthenticatedSessionController;
use App\Http\Controllers\Frontend\CustomerNewPasswordController;
use App\Http\Controllers\Frontend\CustomerOrderController;
use App\Http\Controllers\Frontend\CustomerPasswordResetLinkController;
use App\Http\Controllers\Frontend\CustomerProfileController;
use App\Http\Controllers\Frontend\CustomerRegisteredUserController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\PricingContextController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\ProductReminderController;
use App\Http\Controllers\Frontend\TrackOrderController;
use App\Http\Controllers\Frontend\WelcomeCouponController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/' . config('storefront.default_locale', 'ar'));
Route::redirect('/catalog', '/' . config('storefront.default_locale', 'ar') . '/catalog');
Route::redirect('/categories', '/' . config('storefront.default_locale', 'ar') . '/categories');
Route::get('/categories/{category:slug}', fn (\App\Models\Category $category) => redirect()->route('storefront.categories.show', [
    'locale' => config('storefront.default_locale', 'ar'),
    'category' => $category->slug,
]));
Route::get('/products/{product:slug}', fn (\App\Models\Product $product) => redirect()->route('storefront.products.show', [
    'locale' => config('storefront.default_locale', 'ar'),
    'product' => $product->slug,
]));
Route::get('/pages/{page:slug}', fn (\App\Models\Page $page) => redirect()->route('storefront.pages.show', [
    'locale' => config('storefront.default_locale', 'ar'),
    'page' => $page->slug,
]));

Route::prefix('{locale}')
    ->middleware('storefront.locale')
    ->where(['locale' => implode('|', array_keys(storefront_locales()))])
    ->group(function (): void {
        Route::get('/', HomeController::class)->name('storefront.home');
        Route::get('/home/products-feed', [HomeController::class, 'productsFeed'])->name('storefront.home.products-feed');
        Route::get('/catalog', CatalogController::class)->name('storefront.catalog');
        Route::get('/contact', [ContactMessageController::class, 'show'])->name('storefront.contact.show');
        Route::post('/contact', [ContactMessageController::class, 'store'])->name('storefront.contact.store');
        Route::get('/cart', [CartController::class, 'show'])->name('storefront.cart.show');
        Route::get('/cart/summary', [CartController::class, 'summary'])->name('storefront.cart.summary');
        Route::get('/pricing/context', PricingContextController::class)->name('storefront.pricing.context');
        Route::post('/cart/items', [CartController::class, 'storeItem'])->name('storefront.cart.items.store');
        Route::get('/chatbot/categories', [ChatbotController::class, 'categories'])->name('storefront.chatbot.categories.index');
        Route::get('/chatbot/categories/{category:slug}/products', [ChatbotController::class, 'categoryProducts'])->name('storefront.chatbot.categories.products.index');
        Route::get('/chatbot/categories/{category:slug}/fallback-products', [ChatbotController::class, 'fallbackProducts'])->name('storefront.chatbot.categories.fallback-products.index');
        Route::get('/chatbot/products/{product:slug}/variants', [ChatbotController::class, 'productVariants'])->name('storefront.chatbot.products.variants.index');
        Route::post('/chatbot/cart-items', [ChatbotController::class, 'storeCartItem'])->name('storefront.chatbot.cart-items.store');
        Route::patch('/cart/items/{item}', [CartController::class, 'updateItem'])->name('storefront.cart.items.update');
        Route::delete('/cart/items/{item}', [CartController::class, 'destroyItem'])->name('storefront.cart.items.destroy');
        Route::get('/checkout', [CheckoutController::class, 'show'])->name('storefront.checkout.show');
        Route::get('/checkout/summary', [CheckoutController::class, 'summary'])->name('storefront.checkout.summary');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('storefront.checkout.store');
        Route::get('/checkout/result', [CheckoutController::class, 'result'])->name('storefront.checkout.result');
        Route::post('/checkout/tap/callback', [CheckoutController::class, 'tapCallback'])->name('storefront.checkout.tap.callback');
        Route::get('/checkout/tap/cancel', [CheckoutController::class, 'tapCancel'])->name('storefront.checkout.tap.cancel');
        Route::get('/track-order', [TrackOrderController::class, 'show'])->name('storefront.track-order.show');
        Route::post('/track-order', [TrackOrderController::class, 'store'])->name('storefront.track-order.store');
        Route::post('/welcome-coupon', [WelcomeCouponController::class, 'store'])->name('storefront.welcome-coupon.store');
        Route::get('/categories', [CategoryController::class, 'index'])->name('storefront.categories.index');
        Route::get('/categories/{category:slug}/fallback-products', [CategoryController::class, 'fallbackProducts'])->name('storefront.categories.fallback-products');
        Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('storefront.categories.show');
        Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('storefront.products.show');
        Route::post('/products/{product:slug}/reminders', [ProductReminderController::class, 'store'])->name('storefront.products.reminders.store');
        Route::get('/p/{page:slug}', [PageController::class, 'show'])->name('storefront.pages.show');

        Route::middleware('guest.customer')->group(function (): void {
            Route::get('/login', [CustomerAuthenticatedSessionController::class, 'create'])->name('storefront.auth.login');
            Route::post('/login', [CustomerAuthenticatedSessionController::class, 'store'])->name('storefront.auth.login.store');
            Route::get('/register', [CustomerRegisteredUserController::class, 'create'])->name('storefront.auth.register');
            Route::post('/register', [CustomerRegisteredUserController::class, 'store'])->name('storefront.auth.register.store');
            Route::get('/forgot-password', [CustomerPasswordResetLinkController::class, 'create'])->name('storefront.auth.password.request');
            Route::post('/forgot-password', [CustomerPasswordResetLinkController::class, 'store'])->name('storefront.auth.password.email');
            Route::get('/reset-password/{token}', [CustomerNewPasswordController::class, 'create'])->name('storefront.auth.password.reset');
            Route::post('/reset-password', [CustomerNewPasswordController::class, 'store'])->name('storefront.auth.password.update');
        });

        Route::middleware('auth.customer')->group(function (): void {
            Route::post('/logout', [CustomerAuthenticatedSessionController::class, 'destroy'])->name('storefront.auth.logout');

            Route::get('/account/profile', [CustomerProfileController::class, 'edit'])->name('storefront.profile.edit');
            Route::put('/account/profile', [CustomerProfileController::class, 'update'])->name('storefront.profile.update');
            Route::put('/account/password', [CustomerProfileController::class, 'updatePassword'])->name('storefront.profile.password.update');

            Route::get('/account/orders', [CustomerOrderController::class, 'index'])->name('storefront.orders.index');
            Route::get('/account/orders/{order}', [CustomerOrderController::class, 'show'])->name('storefront.orders.show');

            Route::get('/account/addresses', [CustomerAddressController::class, 'index'])->name('storefront.addresses.index');
            Route::post('/account/addresses', [CustomerAddressController::class, 'store'])->name('storefront.addresses.store');
            Route::put('/account/addresses/{address}', [CustomerAddressController::class, 'update'])->name('storefront.addresses.update');
            Route::delete('/account/addresses/{address}', [CustomerAddressController::class, 'destroy'])->name('storefront.addresses.destroy');
        });
    });
