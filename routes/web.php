<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ThemeController as AdminThemeController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\EmailSettingsController as AdminEmailSettingsController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\NewsletterController as FrontNewsletterController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\OTP\SmsOtpController;
use App\Http\Controllers\OTP\EmailOtpController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\OccasionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PremiumServiceController;
use App\Http\Controllers\StripeWebhookController;

// Installer routes (must be before installation check middleware)
// Only register if InstallerController exists (will be removed after installation)
if (class_exists(\App\Http\Controllers\InstallerController::class)) {
    Route::prefix('installer')->name('installer.')->group(function () {
        Route::get('/', [\App\Http\Controllers\InstallerController::class, 'index'])->name('index');
        Route::get('/database', [\App\Http\Controllers\InstallerController::class, 'database'])->name('database');
        Route::post('/test-database', [\App\Http\Controllers\InstallerController::class, 'testDatabase'])->name('test-database');
        Route::post('/save-database', [\App\Http\Controllers\InstallerController::class, 'saveDatabase'])->name('save-database');
        Route::get('/admin', [\App\Http\Controllers\InstallerController::class, 'admin'])->name('admin');
        Route::post('/install', [\App\Http\Controllers\InstallerController::class, 'install'])->name('install');
        Route::get('/install', [\App\Http\Controllers\InstallerController::class, 'showInstall'])->name('show-install');
        Route::post('/process-install', [\App\Http\Controllers\InstallerController::class, 'processInstall'])->name('process-install');
        Route::get('/complete', [\App\Http\Controllers\InstallerController::class, 'complete'])->name('complete');
    });
}

// Storage file serving fallback (for Windows environments where symlinks may not work)
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    
    if (!file_exists($filePath) || !is_file($filePath)) {
        abort(404);
    }
    
    // Security: prevent directory traversal
    $realPath = realpath($filePath);
    $storagePath = realpath(storage_path('app/public'));
    
    if (!$realPath || strpos($realPath, $storagePath) !== 0) {
        abort(403);
    }
    
    $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
    
    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*')->name('storage.serve');

// Sitemap
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

Route::get('/', [HomeController::class, 'index'])->name('home');

// Premium PURE ROSE services
Route::get('/services/qr-video-messages', [PremiumServiceController::class, 'qrVideo'])->name('services.qr-video');
Route::get('/services/ar-bouquet-preview', [PremiumServiceController::class, 'arPreview'])->name('services.ar-preview');
Route::get('/services/floral-assistant', [PremiumServiceController::class, 'floralAssistant'])->name('services.floral-assistant');
Route::get('/services/corporate-events', [PremiumServiceController::class, 'corporateEvents'])->name('services.corporate-events');

// Notifications API (navbar bell)
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

// Stripe subscription webhooks (no CSRF)
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');

// Currency switch (locked to admin only)
Route::middleware('auth:admin')->post('/currency/switch', [CurrencyController::class, 'switch'])->name('currency.switch');

// OTP routes (public endpoints for demo)
Route::post('/otp/request-email', [EmailOtpController::class, 'request'])->name('otp.request.email');
Route::post('/otp/verify-email', [EmailOtpController::class, 'verify'])->name('otp.verify.email');
Route::post('/otp/request-sms', [SmsOtpController::class, 'request'])->name('otp.request.sms');
Route::post('/otp/verify-sms', [SmsOtpController::class, 'verify'])->name('otp.verify.sms');
Route::get('/otp/email', function() {
    return view(\App\Support\ThemeHelper::view('auth.otp.email'));
})->name('otp.form.email');
Route::get('/otp/sms', function() {
    return view(\App\Support\ThemeHelper::view('auth.otp.sms'));
})->name('otp.form.sms');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/r/{code}', function ($code) {
    // Normalize referral code to uppercase
    $normalizedCode = strtoupper(trim($code));
    
    // Validate that the referral code exists
    $referrer = \App\Models\User::where('referral_code', $normalizedCode)->first();
    
    if ($referrer) {
        session(['referral_code' => $normalizedCode, 'referrer_name' => $referrer->name]);
        return redirect()->route('register')->with('referral_success', 'Referral code applied! You will earn bonus coins when you sign up.');
    } else {
        return redirect()->route('register')->with('referral_error', 'Invalid referral code. You can still register without it.');
    }
})->name('referral');
Route::get('/r', function () {
    return redirect()->route('register');
});
Route::post('/user/logout', [AuthController::class, 'logout'])->name('logout');

    // Newsletter (public)
    Route::post('/newsletter/subscribe', [FrontNewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
    Route::post('/newsletter/unsubscribe', [FrontNewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
    Route::get('/newsletter/confirm/{token}', [FrontNewsletterController::class, 'confirm'])->name('newsletter.confirm');

// User Profile Routes (prefixed with /user)
Route::prefix('user')->middleware('auth')->group(function () {
    Route::get('profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::put('profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('profile/password', [AuthController::class, 'changePassword'])->name('profile.change-password');

    // Address Management
    Route::resource('addresses', AddressController::class);
    Route::post('addresses/{address}/set-default', [AddressController::class, 'setDefault'])->name('addresses.set-default');

    // (moved) wishlist index is now public

    Route::resource('occasions', OccasionController::class)->except(['show']);
    Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::post('subscriptions/{subscription}/pause', [SubscriptionController::class, 'pause'])->name('subscriptions.pause');
    Route::post('subscriptions/{subscription}/resume', [SubscriptionController::class, 'resume'])->name('subscriptions.resume');
    Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
});

// Public wishlist index (guest or user)
Route::get('/user/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');

// Public wishlist toggle (supports guest via session)
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

// Products and categories
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('categories.show');

// Pages (frontend)
Route::get('/page/{slug}', [App\Http\Controllers\PageController::class, 'show'])->name('pages.show');

// Reviews
Route::post('/products/{product}/reviews', [App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store')->middleware('auth');
Route::delete('/reviews/{review}', [App\Http\Controllers\ReviewController::class, 'destroy'])->name('reviews.destroy')->middleware('auth');

// Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::put('/cart/items/{item}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{item}', [CartController::class, 'remove'])->name('cart.items.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// Coupons
Route::post('/coupons/apply', [CouponController::class, 'apply'])->name('coupons.apply');
Route::post('/coupons/remove', [CouponController::class, 'remove'])->name('coupons.remove');
Route::post('/coupons/validate', [CouponController::class, 'validateCode'])->name('coupons.validate');

//

// Checkout and Orders
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
Route::post('/checkout/calculate-shipping-tax', [CheckoutController::class, 'calculateShippingTax'])->name('checkout.calculate-shipping-tax');

// Payment Callbacks
Route::prefix('payment')->group(function () {
    // SSL Commerce
    Route::post('/ssl-commerce/success', [App\Http\Controllers\PaymentCallbackController::class, 'sslCommerceSuccess'])->name('payment.ssl-commerce.success');
    Route::post('/ssl-commerce/fail', [App\Http\Controllers\PaymentCallbackController::class, 'sslCommerceFail'])->name('payment.ssl-commerce.fail');
    Route::post('/ssl-commerce/cancel', [App\Http\Controllers\PaymentCallbackController::class, 'sslCommerceCancel'])->name('payment.ssl-commerce.cancel');
    Route::get('/ssl-commerce/success', [App\Http\Controllers\PaymentCallbackController::class, 'sslCommerceSuccess'])->name('payment.ssl-commerce.success.get');
    Route::get('/ssl-commerce/fail', [App\Http\Controllers\PaymentCallbackController::class, 'sslCommerceFail'])->name('payment.ssl-commerce.fail.get');
    Route::get('/ssl-commerce/cancel', [App\Http\Controllers\PaymentCallbackController::class, 'sslCommerceCancel'])->name('payment.ssl-commerce.cancel.get');
    
    // Stripe
    Route::get('/stripe/success', [App\Http\Controllers\PaymentCallbackController::class, 'stripeSuccess'])->name('payment.stripe.success');
    Route::get('/stripe/cancel', [App\Http\Controllers\PaymentCallbackController::class, 'stripeCancel'])->name('payment.stripe.cancel');
    
    // PayPal
    Route::get('/paypal/success', [App\Http\Controllers\PaymentCallbackController::class, 'paypalSuccess'])->name('payment.paypal.success');
    Route::get('/paypal/cancel', [App\Http\Controllers\PaymentCallbackController::class, 'paypalCancel'])->name('payment.paypal.cancel');
    
    // bKash, Nagad, Rocket - Callbacks from official payment gateways
    Route::get('/bkash/callback', [App\Http\Controllers\PaymentCallbackController::class, 'bkashCallback'])->name('payment.bkash.callback');
    Route::post('/bkash/callback', [App\Http\Controllers\PaymentCallbackController::class, 'bkashCallback'])->name('payment.bkash.callback.post');
    Route::get('/nagad/callback', [App\Http\Controllers\PaymentCallbackController::class, 'nagadCallback'])->name('payment.nagad.callback');
    Route::post('/nagad/callback', [App\Http\Controllers\PaymentCallbackController::class, 'nagadCallback'])->name('payment.nagad.callback.post');
    Route::get('/rocket/callback', [App\Http\Controllers\PaymentCallbackController::class, 'rocketCallback'])->name('payment.rocket.callback');
    Route::post('/rocket/callback', [App\Http\Controllers\PaymentCallbackController::class, 'rocketCallback'])->name('payment.rocket.callback.post');
});

Route::prefix('user')->middleware('auth')->group(function(){
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{id}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
});
// Guest order view via signed URL (no auth)
Route::get('/order/guest/{order}', [OrderController::class, 'showGuest'])
    ->name('orders.guest.show')
    ->middleware('signed');

// Admin routes
// Public theme previews (e.g., login/register pages)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/theme/{path?}', [AdminThemeController::class, 'show'])->where('path', '.*')->name('theme');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.attempt');
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
    Route::get('/captcha', [App\Http\Controllers\Admin\CaptchaController::class, 'generate'])->name('captcha');
    
    // License routes (accessible without license check)
    Route::get('/license/activate', [App\Http\Controllers\Admin\LicenseController::class, 'show'])->name('license.activate');
    Route::post('/license/activate', [App\Http\Controllers\Admin\LicenseController::class, 'activate'])->name('license.activate.post');
    Route::post('/license/remove', [App\Http\Controllers\Admin\LicenseController::class, 'remove'])->name('license.remove');
    Route::get('/license/check', [App\Http\Controllers\Admin\LicenseController::class, 'check'])->name('license.check');

    // Lightweight admin routes (auth only, no permission gate)
    Route::middleware('auth:admin')->group(function(){
        Route::get('users/lookup', [AdminUserController::class, 'lookup'])->name('users.lookup');
        Route::post('clear-cache', App\Http\Controllers\Admin\ClearCacheController::class)->name('clear-cache');
    });

    Route::middleware(['auth:admin','admin.permission'])->group(function () {
        // Allow /admin/users/{user} to accept ID, email, or phone
        \Illuminate\Support\Facades\Route::bind('user', function ($value) {
            return \App\Models\User::query()
                ->where('id', $value)
                ->orWhere('email', $value)
                ->orWhere('phone', $value)
                ->firstOrFail();
        });
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('categories/check-slug', [AdminCategoryController::class, 'checkSlug'])->name('categories.check-slug');
        Route::resource('categories', AdminCategoryController::class);
        // Product helper endpoints must come before resource show route
        Route::get('products/lookup', [AdminProductController::class, 'lookup'])->name('products.lookup');
        Route::get('products/{product}/json', [AdminProductController::class, 'showJson'])->name('products.json');
        Route::post('products/check-slug', [AdminProductController::class, 'checkSlug'])->name('products.check-slug');
        Route::delete('products/images/{image}', [AdminProductController::class, 'deleteImage'])->name('products.images.delete');
        Route::post('products/images/{image}/set-primary', [AdminProductController::class, 'setPrimaryImage'])->name('products.images.set-primary');
        Route::post('products/{product}/images/update-order', [AdminProductController::class, 'updateImageOrder'])->name('products.images.update-order');
        Route::resource('products', AdminProductController::class);
        Route::get('orders/create', [AdminOrderController::class, 'create'])->name('orders.create');
        Route::post('orders', [AdminOrderController::class, 'store'])->name('orders.store');
        Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);
        Route::get('orders/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');
        Route::resource('users', AdminUserController::class)->except(['create', 'store']);
        Route::resource('media', App\Http\Controllers\Admin\MediaController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::post('users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('users/{user}/coins/adjust', [AdminUserController::class, 'adjustCoins'])->name('users.coins.adjust');
        Route::post('users/{user}/coins/reset', [AdminUserController::class, 'resetCoins'])->name('users.coins.reset');
        Route::get('shipping-settings', [\App\Http\Controllers\Admin\ShippingSettingsController::class, 'index'])->name('shipping-settings.index');
        Route::put('shipping-settings', [\App\Http\Controllers\Admin\ShippingSettingsController::class, 'update'])->name('shipping-settings.update');
            Route::get('email-settings', [AdminEmailSettingsController::class, 'index'])->name('email-settings.index');
            Route::put('email-settings', [AdminEmailSettingsController::class, 'update'])->name('email-settings.update');
            Route::post('email-settings/test', [AdminEmailSettingsController::class, 'testEmail'])->name('email-settings.test');
            Route::get('storage-settings', [\App\Http\Controllers\Admin\StorageSettingsController::class, 'index'])->name('storage-settings.index');
            Route::put('storage-settings', [\App\Http\Controllers\Admin\StorageSettingsController::class, 'update'])->name('storage-settings.update');
            Route::resource('roles', AdminRoleController::class)->except(['show']);
            Route::get('roles/{role}/copy', [AdminRoleController::class, 'copy'])->name('roles.copy');
            Route::post('roles/{role}/copy', [AdminRoleController::class, 'storeCopy'])->name('roles.copy.store');
            Route::resource('permissions', AdminPermissionController::class)->except(['show']);
            Route::resource('coupons', AdminCouponController::class)->except(['show']);
            Route::post('coupons/{coupon}/toggle-status', [AdminCouponController::class, 'toggleStatus'])->name('coupons.toggle-status');

            // Newsletter admin
            Route::get('newsletter', [App\Http\Controllers\Admin\NewsletterController::class, 'index'])->name('newsletter.index');
            Route::post('newsletter/{subscriber}/toggle', [App\Http\Controllers\Admin\NewsletterController::class, 'toggle'])->name('newsletter.toggle');
            Route::delete('newsletter/{subscriber}', [App\Http\Controllers\Admin\NewsletterController::class, 'destroy'])->name('newsletter.destroy');
            
            // Backup & Restore
            Route::get('backup', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backup.index');
            Route::get('backup/export/product/{product}', [\App\Http\Controllers\Admin\BackupController::class, 'exportProduct'])->name('backup.export-product');
            Route::get('backup/export/all', [\App\Http\Controllers\Admin\BackupController::class, 'exportAll'])->name('backup.export-all');
            Route::get('backup/import', [\App\Http\Controllers\Admin\BackupController::class, 'import'])->name('backup.import');
            Route::post('backup/import', [\App\Http\Controllers\Admin\BackupController::class, 'processImport'])->name('backup.process-import');
            Route::get('backup/import/wordpress', [\App\Http\Controllers\Admin\BackupController::class, 'importWordPress'])->name('backup.import-wordpress');
            Route::post('backup/import/wordpress', [\App\Http\Controllers\Admin\BackupController::class, 'processWordPressImport'])->name('backup.process-wordpress-import');
            Route::get('backup/import/shopify', [\App\Http\Controllers\Admin\BackupController::class, 'importShopify'])->name('backup.import-shopify');
            Route::post('backup/import/shopify', [\App\Http\Controllers\Admin\BackupController::class, 'processShopifyImport'])->name('backup.process-shopify-import');
            
            // Reviews
            Route::get('reviews', [App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
            Route::post('reviews/{review}/approve', [App\Http\Controllers\Admin\ReviewController::class, 'approve'])->name('reviews.approve');
            Route::post('reviews/{review}/reject', [App\Http\Controllers\Admin\ReviewController::class, 'reject'])->name('reviews.reject');
            Route::delete('reviews/{review}', [App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');
            
            // Payment Gateways
            Route::get('payment-gateways', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'index'])->name('payment-gateways.index');
            Route::get('payment-gateways/{gateway}', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'show'])->name('payment-gateways.show');
            Route::put('payment-gateways/{gateway}', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'update'])->name('payment-gateways.update');
            Route::post('payment-gateways/{gateway}/toggle-status', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'toggleStatus'])->name('payment-gateways.toggle-status');
            Route::post('payment-gateways/{gateway}/test', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'testConnection'])->name('payment-gateways.test');

            // OTP Settings
            Route::get('otp-settings', [App\Http\Controllers\Admin\OtpSettingsController::class, 'index'])->name('otp-settings.index');
            Route::put('otp-settings', [App\Http\Controllers\Admin\OtpSettingsController::class, 'update'])->name('otp-settings.update');

            // API Routes for Admin
            Route::get('api/categories/{category}/subcategories', [AdminCategoryController::class, 'getSubcategories'])->name('api.categories.subcategories');

            // User Activity
            Route::get('activities/carts', [App\Http\Controllers\Admin\UserActivityController::class, 'carts'])->name('activities.carts');
            Route::get('activities/wishlists', [App\Http\Controllers\Admin\UserActivityController::class, 'wishlists'])->name('activities.wishlists');
            Route::get('activities/sessions', [App\Http\Controllers\Admin\UserActivityController::class, 'sessions'])->name('activities.sessions');
            Route::delete('activities/sessions/{id}', [App\Http\Controllers\Admin\UserActivityController::class, 'destroySession'])->name('activities.sessions.destroy');
            Route::delete('activities/users/{user}/sessions', [App\Http\Controllers\Admin\UserActivityController::class, 'destroyUserSessions'])->name('activities.sessions.destroy-user');

            // POS coupon preview
            Route::get('coupons/preview', [App\Http\Controllers\Admin\CouponPreviewController::class, 'preview'])->name('coupons.preview');

            // Currencies
            Route::resource('currencies', App\Http\Controllers\Admin\CurrencyController::class)->except(['show']);
            Route::post('currencies/{currency}/toggle', [App\Http\Controllers\Admin\CurrencyController::class, 'toggle'])->name('currencies.toggle');
            Route::post('currencies/{currency}/default', [App\Http\Controllers\Admin\CurrencyController::class, 'setDefault'])->name('currencies.default');

            // Site Settings
            Route::get('site-settings', [App\Http\Controllers\Admin\SiteSettingsController::class, 'index'])->name('site-settings.index');
            Route::put('site-settings', [App\Http\Controllers\Admin\SiteSettingsController::class, 'update'])->name('site-settings.update');

            // Administrators
            Route::resource('admins', App\Http\Controllers\Admin\AdminController::class)->except(['show']);

            // Error Logs (central error logging)
            Route::get('error-logs', [App\Http\Controllers\Admin\ErrorLogController::class, 'index'])->name('error-logs.index');
            Route::delete('error-logs/clear', [App\Http\Controllers\Admin\ErrorLogController::class, 'clear'])->name('error-logs.clear');
            Route::post('error-logs/clear-resolved', [App\Http\Controllers\Admin\ErrorLogController::class, 'clearResolved'])->name('error-logs.clear-resolved');
            Route::post('error-logs/mark-resolved', [App\Http\Controllers\Admin\ErrorLogController::class, 'markResolved'])->name('error-logs.mark-resolved');
            Route::get('error-logs/{error_log}', [App\Http\Controllers\Admin\ErrorLogController::class, 'show'])->name('error-logs.show');
            Route::post('error-logs/{error_log}/resolve', [App\Http\Controllers\Admin\ErrorLogController::class, 'resolve'])->name('error-logs.resolve');
            Route::delete('error-logs/{error_log}', [App\Http\Controllers\Admin\ErrorLogController::class, 'destroy'])->name('error-logs.destroy');

            // Coin Settings
            Route::get('coin-settings', [App\Http\Controllers\Admin\CoinSettingsController::class, 'index'])->name('coin-settings.index');
            Route::put('coin-settings', [App\Http\Controllers\Admin\CoinSettingsController::class, 'update'])->name('coin-settings.update');

            // Pages
            Route::resource('pages', App\Http\Controllers\Admin\PageController::class);

            // Districts
            Route::resource('districts', App\Http\Controllers\Admin\DistrictController::class);
            Route::post('districts/{district}/toggle-status', [App\Http\Controllers\Admin\DistrictController::class, 'toggleStatus'])->name('districts.toggle-status');

            // DataTables server-side endpoint
            Route::get('datatables/{resource}', [App\Http\Controllers\Admin\DataTableController::class, 'handle'])->name('datatables');
    });
});
