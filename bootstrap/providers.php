<?php

return [

    /*
     * Laravel Framework Service Providers...
     */
    Illuminate\Auth\AuthServiceProvider::class,
    Illuminate\Broadcasting\BroadcastServiceProvider::class,
    Illuminate\Bus\BusServiceProvider::class,
    Illuminate\Cache\CacheServiceProvider::class,
    Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
    Illuminate\Cookie\CookieServiceProvider::class,
    Illuminate\Database\DatabaseServiceProvider::class,
    Illuminate\Encryption\EncryptionServiceProvider::class,
    Illuminate\Filesystem\FilesystemServiceProvider::class,
    Illuminate\Foundation\Providers\FoundationServiceProvider::class,
    Illuminate\Hashing\HashServiceProvider::class,
    Illuminate\Mail\MailServiceProvider::class,
    Illuminate\Notifications\NotificationServiceProvider::class,
    Illuminate\Pagination\PaginationServiceProvider::class,
    Illuminate\Pipeline\PipelineServiceProvider::class,
    Illuminate\Queue\QueueServiceProvider::class,
    Illuminate\Redis\RedisServiceProvider::class,
    Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
    Illuminate\Session\SessionServiceProvider::class,
    Illuminate\Translation\TranslationServiceProvider::class,
    Illuminate\Validation\ValidationServiceProvider::class,
    Illuminate\View\ViewServiceProvider::class,

    /*
     * Package Service Providers...
     */

    /*
     * Application Service Providers...
     */
    Acelle\Providers\AppServiceProvider::class,
    Acelle\Providers\AuthServiceProvider::class,
    Acelle\Providers\EventServiceProvider::class,
    Acelle\Providers\RouteServiceProvider::class,
    Acelle\Providers\JobServiceProvider::class,

    /*
     * Acelle Extended Provider
     */
    Acelle\Extra\LogViewer\ServiceProvider::class,
    Lawepham\Geoip\LaweGeoIpProvider::class,

    /*
     * 3rd Service Provider
     */
    Intervention\Image\ImageServiceProvider::class,

    // Laravel\Cashier\CashierServiceProvider::class,
    Acelle\Cashier\CashierServiceProvider::class,

    // More
    Acelle\Providers\MailerServiceProvider::class,
    Acelle\Providers\StorageServiceProvider::class,
    Acelle\Providers\CheckoutServiceProvider::class,
];
