<?php

namespace MacropaySolutions\KernelDev\Testing;

use MacropaySolutions\Kernel\Container\Container;
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
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->app->singleton(ParallelTesting::class, [function () {
                return new ParallelTesting($this->app);
            }, '__invoke']);
            $this->app->afterResolving(ParallelTesting::class, [$this, 'afterResolving']);
        }
    }

    public function afterResolving(ParallelTesting $parallelTesting, Container $app): void
    {
        $this->bootTestCache($parallelTesting);
        $this->bootTestDatabase($parallelTesting);
        $this->bootTestViews($parallelTesting);
    }

    public function provides()
    {
        return [
            ParallelTesting::class
        ];
    }
}
