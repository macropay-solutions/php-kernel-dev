<?php

namespace MacropaySolutions\KernelDev;

use MacropaySolutions\Kernel\Support\ServiceProvider as ParentServiceProvider;
use MacropaySolutions\KernelDev\Console\Scheduling\ScheduleListCommand;
use MacropaySolutions\KernelDev\Console\Scheduling\ScheduleTestCommand;
use MacropaySolutions\KernelDev\Database\Console\Factories\FactoryMakeCommand;
use MacropaySolutions\KernelDev\Database\Console\ShowCommand;
use MacropaySolutions\KernelDev\Database\Console\ShowModelCommand;
use MacropaySolutions\KernelDev\Database\Console\TableCommand;
use MacropaySolutions\KernelDev\Session\Console\SessionTableCommand;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;

class ServiceProvider extends ParentServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        if (!$this->app instanceof \MacropaySolutions\Framework\Application) {
            return;
        }

        $this->app->singleton(FactoryMakeCommand::class, function ($app) {
            return new FactoryMakeCommand($app['files']);
        });
        $this->app->singleton(SessionTableCommand::class, function ($app) {
            return new SessionTableCommand($app['files'], $app['composer']);
        });

        $this->app->bind(OutputFormatterInterface::class, OutputFormatter::class);

        $this->commands([
            FactoryMakeCommand::class,
            ScheduleListCommand::class,
            ScheduleTestCommand::class,
            SessionTableCommand::class,
            ShowCommand::class,
            ShowModelCommand::class,
            TableCommand::class,
        ]);
    }
}
