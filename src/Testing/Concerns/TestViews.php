<?php

namespace MacropaySolutions\KernelDev\Testing\Concerns;

trait TestViews
{
    /**
     * The original compiled view path prior to appending the token.
     *
     * @var string|null
     */
    protected static $originalCompiledViewPath = null;

    /**
     * Boot test views for parallel testing.
     *
     * @return void
     */
    protected function bootTestViews()
    {
        \app(\MacropaySolutions\KernelDev\Testing\ParallelTesting::class)->setUpProcess(function () {
            if ($path = $this->parallelSafeCompiledViewPath()) {
                \app('files')->ensureDirectoryExists($path);
            }
        });

        \app(\MacropaySolutions\KernelDev\Testing\ParallelTesting::class)->setUpTestCase(function () {
            if ($path = $this->parallelSafeCompiledViewPath()) {
                $this->switchToCompiledViewPath($path);
            }
        });
    }

    /**
     * Get the test compiled view path.
     *
     * @return string|null
     */
    protected function parallelSafeCompiledViewPath()
    {
        self::$originalCompiledViewPath ??= $this->app['config']->get('view.compiled', '');

        if (!self::$originalCompiledViewPath) {
            return null;
        }

        return rtrim(self::$originalCompiledViewPath, '\/')
            . '/test_'
            . \app(\MacropaySolutions\KernelDev\Testing\ParallelTesting::class)->token();
    }

    /**
     * Switch to the given compiled view path.
     *
     * @param string $path
     * @return void
     */
    protected function switchToCompiledViewPath($path)
    {
        $this->app['config']->set('view.compiled', $path);

        if ($this->app->resolved('blade.compiler')) {
            $compiler = $this->app['blade.compiler'];

            (function () use ($path) {
                $this->cachePath = $path;
            })->bindTo($compiler, $compiler)();
        }
    }
}