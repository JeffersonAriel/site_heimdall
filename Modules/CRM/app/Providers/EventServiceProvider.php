<?php

namespace Modules\CRM\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        \Modules\CRM\Events\CustomerConverted::class => [
            \Modules\CRM\Listeners\CreateLeadOnCustomerCreated::class,
        ],
        \Modules\Orders\Events\OrderCreated::class => [
            \Modules\CRM\Listeners\CreateDealOnOrderCreated::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
