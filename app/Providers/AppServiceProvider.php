<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use App\Support\CurrencyManager;
use App\Support\MediaHelper;
use App\Support\ThemeHelper;
use App\Models\Currency;
use App\Models\EmailSetting;
use App\Models\SiteSetting;
use App\Models\StorageSetting;
use App\Models\UserNotification;
use App\Services\OccasionReminderService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Currency formatter directive
        Blade::directive('currency', function ($expression) {
            return "<?php echo \\App\\Support\\CurrencyManager::format($expression); ?>";
        });

        // Date formatter directive - Format: "12 Feb 2025, 10:00 PM"
        Blade::directive('formatDate', function ($expression) {
            return "<?php echo \\App\\Support\\DateHelper::format($expression); ?>";
        });
        Blade::directive('mediaContent', function ($expression) {
            return "<?php echo \\App\\Support\\MediaHelper::replaceShortcodes($expression); ?>";
        });

        // Share currencies and current currency with all views
        view()->composer('*', function ($view) {
            try {
                $view->with('currentCurrency', CurrencyManager::current());
                $view->with('activeCurrencies', Currency::where('is_active', true)->get());
                $view->with('siteSettings', SiteSetting::get());
                $view->with('currentTheme', ThemeHelper::current());

                if (auth()->check()) {
                    $view->with('userNotifications', UserNotification::where('user_id', auth()->id())->latest()->limit(8)->get());
                    $view->with('unreadNotificationCount', UserNotification::where('user_id', auth()->id())->unread()->count());
                    $view->with('upcomingOccasions', app(OccasionReminderService::class)->upcomingForUser(auth()->user(), 5));
                } else {
                    $view->with('userNotifications', collect());
                    $view->with('unreadNotificationCount', 0);
                    $view->with('upcomingOccasions', collect());
                }
            } catch (\Throwable $e) {
                $name = $view->name() ?? '';
                if (str_starts_with($name, 'errors.') || str_starts_with($name, 'frontend.errors.') || str_starts_with($name, 'errors::') || str_contains($name, 'exception') || str_contains($name, 'laravel-exceptions-renderer')) {
                    $view->with('currentCurrency', null);
                    $view->with('activeCurrencies', collect());
                    $view->with('siteSettings', new \stdClass);
                    $view->with('currentTheme', 'theme1');
                } else {
                    throw $e;
                }
            }
        });

        // Dynamic mail configuration from DB settings (if available)
        // This ensures SMTP settings from admin panel are applied globally
        try {
            $smtpEnabled = EmailSetting::get('smtp_enabled', '0') === '1';
            
            if ($smtpEnabled) {
                // Use SMTP settings from database
                config(['mail.default' => 'smtp']);
                
                $host = EmailSetting::get('smtp_host');
                $port = EmailSetting::get('smtp_port');
                $username = EmailSetting::get('smtp_username');
                $password = EmailSetting::get('smtp_password');
                $encryption = EmailSetting::get('smtp_encryption', 'tls');
                $fromAddress = EmailSetting::get('smtp_from_address') ?: EmailSetting::get('smtp_username');
                $fromName = EmailSetting::get('smtp_from_name') ?: EmailSetting::get('from_name', 'eCommerce Store');

                if ($host) {
                    config(['mail.mailers.smtp.host' => $host]);
                }
                if ($port) {
                    config(['mail.mailers.smtp.port' => (int) $port]);
                }
                if ($username) {
                    config(['mail.mailers.smtp.username' => $username]);
                }
                if ($password) {
                    config(['mail.mailers.smtp.password' => $password]);
                }
                if ($encryption) {
                    config(['mail.mailers.smtp.encryption' => $encryption]);
                }
                if ($fromAddress) {
                    config(['mail.from.address' => $fromAddress]);
                }
                if ($fromName) {
                    config(['mail.from.name' => $fromName]);
                }
            } else {
                // Fallback to old mail_ prefixed settings for backward compatibility
                $mailer = EmailSetting::get('mail_mailer');
                if ($mailer) {
                    config(['mail.default' => $mailer]);
                }
                $host = EmailSetting::get('mail_host');
                $port = EmailSetting::get('mail_port');
                $username = EmailSetting::get('mail_username');
                $password = EmailSetting::get('mail_password');
                $encryption = EmailSetting::get('mail_encryption');
                $fromAddress = EmailSetting::get('mail_from_address') ?: EmailSetting::get('from_email');
                $fromName = EmailSetting::get('mail_from_name') ?: EmailSetting::get('from_name', 'eCommerce Store');

                if ($host) config(['mail.mailers.smtp.host' => $host]);
                if ($port) config(['mail.mailers.smtp.port' => (int) $port]);
                if ($username) config(['mail.mailers.smtp.username' => $username]);
                if ($password) config(['mail.mailers.smtp.password' => $password]);
                if ($encryption) config(['mail.mailers.smtp.encryption' => $encryption]);
                if ($fromAddress) config(['mail.from.address' => $fromAddress]);
                if ($fromName) config(['mail.from.name' => $fromName]);
            }
        } catch (\Throwable $e) {
            // ignore if table not migrated yet
        }

        // Dynamic filesystem configuration from DB settings (if available)
        // This ensures storage/CDN settings from admin panel are applied globally
        try {
            $driver = StorageSetting::get('storage_driver', 'local');
            
            if ($driver !== 'local') {
                // Configure based on selected driver
                switch ($driver) {
                    case 's3':
                        config(['filesystems.default' => 's3']);
                        config(['filesystems.disks.s3' => [
                            'driver' => 's3',
                            'key' => StorageSetting::get('s3_key'),
                            'secret' => StorageSetting::get('s3_secret'),
                            'region' => StorageSetting::get('s3_region', 'us-east-1'),
                            'bucket' => StorageSetting::get('s3_bucket'),
                            'url' => StorageSetting::get('s3_url'),
                            'endpoint' => StorageSetting::get('s3_endpoint'),
                            'use_path_style_endpoint' => StorageSetting::isEnabled('s3_use_path_style'),
                        ]]);
                        break;

                    case 'cloudflare':
                        // Cloudflare R2 is S3-compatible
                        config(['filesystems.default' => 'cloudflare']);
                        config(['filesystems.disks.cloudflare' => [
                            'driver' => 's3',
                            'key' => StorageSetting::get('cloudflare_access_key_id'),
                            'secret' => StorageSetting::get('cloudflare_secret_access_key'),
                            'region' => 'auto',
                            'bucket' => StorageSetting::get('cloudflare_bucket'),
                            'endpoint' => StorageSetting::get('cloudflare_endpoint') ?: 'https://' . StorageSetting::get('cloudflare_account_id') . '.r2.cloudflarestorage.com',
                            'use_path_style_endpoint' => false,
                        ]]);
                        break;

                    case 'digitalocean':
                        config(['filesystems.default' => 'digitalocean']);
                        config(['filesystems.disks.digitalocean' => [
                            'driver' => 's3',
                            'key' => StorageSetting::get('digitalocean_key'),
                            'secret' => StorageSetting::get('digitalocean_secret'),
                            'region' => StorageSetting::get('digitalocean_region', 'nyc3'),
                            'bucket' => StorageSetting::get('digitalocean_bucket'),
                            'endpoint' => StorageSetting::get('digitalocean_endpoint') ?: 'https://' . StorageSetting::get('digitalocean_region', 'nyc3') . '.digitaloceanspaces.com',
                            'use_path_style_endpoint' => false,
                        ]]);
                        break;

                    case 'wasabi':
                        config(['filesystems.default' => 'wasabi']);
                        config(['filesystems.disks.wasabi' => [
                            'driver' => 's3',
                            'key' => StorageSetting::get('wasabi_key'),
                            'secret' => StorageSetting::get('wasabi_secret'),
                            'region' => StorageSetting::get('wasabi_region', 'us-east-1'),
                            'bucket' => StorageSetting::get('wasabi_bucket'),
                            'endpoint' => StorageSetting::get('wasabi_endpoint') ?: 'https://s3.' . StorageSetting::get('wasabi_region', 'us-east-1') . '.wasabisys.com',
                            'use_path_style_endpoint' => false,
                        ]]);
                        break;

                    case 'backblaze':
                        // Backblaze B2 is S3-compatible
                        config(['filesystems.default' => 'backblaze']);
                        config(['filesystems.disks.backblaze' => [
                            'driver' => 's3',
                            'key' => StorageSetting::get('backblaze_key_id'),
                            'secret' => StorageSetting::get('backblaze_application_key'),
                            'region' => 'us-west-004',
                            'bucket' => StorageSetting::get('backblaze_bucket_name'),
                            'endpoint' => 'https://s3.us-west-004.backblazeb2.com',
                            'use_path_style_endpoint' => true,
                        ]]);
                        break;
                }
            }

            // Configure CDN URL if enabled
            if (StorageSetting::isEnabled('cdn_enabled')) {
                $cdnUrl = StorageSetting::get('cdn_url');
                if ($cdnUrl) {
                    config(['filesystems.cdn_url' => rtrim($cdnUrl, '/')]);
                }
            }
        } catch (\Throwable $e) {
            // ignore if table not migrated yet
        }
    }
}
