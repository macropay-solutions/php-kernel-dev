<?php

namespace MacropaySolutions\KernelDev\Testing;

use MacropaySolutions\Kernel\Contracts\Support\DeferrableProvider;
use MacropaySolutions\Kernel\Support\ServiceProvider;
use MacropaySolutions\KernelDev\Testing\Concerns\TestCaches;
use MacropaySolutions\KernelDev\Testing\Concerns\TestDatabases;
use MacropaySolutions\KernelDev\Testing\Concerns\TestViews;

class ParallelTestingServiceProvider extends ServiceProvider implements DeferrableProvider
{
    use TestCaches;
    use TestDatabases;
    use TestViews;

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->bootTestCache();
            $this->bootTestDatabase();
            $this->bootTestViews();
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->app->singleton(ParallelTesting::class, function () {
                return new ParallelTesting($this->app);
            });
        }
    }
}
