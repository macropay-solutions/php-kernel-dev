<?php

namespace MacropaySolutions\KernelDev\Foundation\Testing;

use MacropaySolutions\Kernel\Contracts\Console\Kernel as ConsoleKernel;

trait WithConsoleEvents
{
    /**
     * Register console events.
     *
     * @return void
     */
    protected function setUpWithConsoleEvents()
    {
        $this->app[ConsoleKernel::class]->rerouteSymfonyCommandEvents();
    }
}
