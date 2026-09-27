<?php

namespace MacropaySolutions\KernelDev\Testing\Concerns;


use MacropaySolutions\KernelDev\Testing\ParallelTesting;

trait TestCaches
{
    /**
     * The original cache prefix prior to appending the token.
     *
     * @var string|null
     */
    protected static $originalCachePrefix = null;

    /**
     * Boot test cache for parallel testing.
     *
     * @return void
     */
    protected function bootTestCache(ParallelTesting $parallelTesting)
    {
        $parallelTesting->setUpTestCase(function () use ($parallelTesting): void {
            if ($parallelTesting->option('without_cache')) {
                return;
            }

            $this->switchToCachePrefix($this->parallelSafeCachePrefix($parallelTesting));
        });
    }

    /**
     * Get the test cache prefix.
     *
     * @return string
     */
    protected function parallelSafeCachePrefix(ParallelTesting $parallelTesting)
    {
        self::$originalCachePrefix ??= $this->app->make('config')->get('cache.prefix', '');

        return self::$originalCachePrefix . 'test_' . $parallelTesting->token() . '_';
    }

    /**
     * Switch to the given cache prefix.
     *
     * @param string $prefix
     * @return void
     */
    protected function switchToCachePrefix($prefix)
    {
        $this->app->make('config')->set('cache.prefix', $prefix);

        if ($this->app->resolved('cache')) {
            $this->app->make('cache')->forgetDriver();
        }
    }
}