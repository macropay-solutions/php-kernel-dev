<?php

namespace MacropaySolutions\KernelDev;

use MacropaySolutions\Kernel\Contracts\Support\DeferrableProvider;
use MacropaySolutions\Kernel\Support\ServiceProvider as ParentServiceProvider;
use MacropaySolutions\KernelDev\Console\ClearAllCachedCommands;
use MacropaySolutions\KernelDev\Console\Scheduling\ScheduleListCommand;
use MacropaySolutions\KernelDev\Console\Scheduling\ScheduleTestCommand;
use MacropaySolutions\KernelDev\Database\Console\Factories\FactoryMakeCommand;
use MacropaySolutions\KernelDev\Database\Console\ShowCommand;
use MacropaySolutions\KernelDev\Database\Console\ShowModelCommand;
use MacropaySolutions\KernelDev\Database\Console\TableCommand as DatabaseTableCommand;
use MacropaySolutions\KernelDev\Foundation\Console\ChannelListCommand;
use MacropaySolutions\KernelDev\Foundation\Console\ChannelMakeCommand;
use MacropaySolutions\KernelDev\Foundation\Console\ComponentMakeCommand;
use MacropaySolutions\KernelDev\Foundation\Console\ConfigShowCommand;
use MacropaySolutions\KernelDev\Foundation\Console\ConsoleMakeCommand;
use MacropaySolutions\KernelDev\Foundation\Console\EnvironmentEncryptCommand;
use MacropaySolutions\KernelDev\Foundation\Console\EventGenerateCommand;
use MacropaySolutions\KernelDev\Foundation\Console\EventListCommand;
use MacropaySolutions\KernelDev\Foundation\Console\EventMakeCommand;
use MacropaySolutions\KernelDev\Foundation\Console\ExceptionMakeCommand;
use MacropaySolutions\KernelDev\Foundation\Console\JobMakeCommand;
use MacropaySolutions\KernelDev\Foundation\Console\KeyGenerateCommand;
use MacropaySolutions\KernelDev\Foundation\Console\LangPublishCommand;
use MacropaySolutions\KernelDev\Foundation\Console\ListenerMakeCommand;
use MacropaySolutions\KernelDev\Foundation\Console\MailMakeCommand;
use MacropaySolutions\KernelDev\Foundation\Console\NotificationMakeCommand;
use MacropaySolutions\KernelDev\Foundation\Console\ObserverMakeCommand;
use MacropaySolutions\KernelDev\Foundation\Console\PolicyMakeCommand;
use MacropaySolutions\KernelDev\Foundation\Console\ProviderMakeCommand;
use MacropaySolutions\KernelDev\Foundation\Console\RequestMakeCommand;
use MacropaySolutions\KernelDev\Foundation\Console\RouteListCommand;
use MacropaySolutions\KernelDev\Foundation\Console\RuleMakeCommand;
use MacropaySolutions\KernelDev\Foundation\Console\ScopeMakeCommand;
use MacropaySolutions\KernelDev\Foundation\Console\TestMakeCommand;
use MacropaySolutions\KernelDev\Foundation\Console\ViewMakeCommand;
use MacropaySolutions\KernelDev\Session\Console\SessionTableCommand;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;

class ServiceProvider extends ParentServiceProvider implements DeferrableProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->singleton(FactoryMakeCommand::class, [function ($app) {
            return new FactoryMakeCommand($app->make('files'));
        }, '__invoke']);
        $this->app->singleton(SessionTableCommand::class, [function ($app) {
            return new SessionTableCommand($app->make('files'), $app->make('composer'));
        }, '__invoke']);

        $this->app->bind(OutputFormatterInterface::class, OutputFormatter::class);

        $this->commands([
            ClearAllCachedCommands::class,
            ScheduleListCommand::class,
            ScheduleTestCommand::class,
            FactoryMakeCommand::class,
            ShowCommand::class,
            ShowModelCommand::class,
            DatabaseTableCommand::class,
            ChannelListCommand::class,
            ChannelMakeCommand::class,
            ComponentMakeCommand::class,
            ConfigShowCommand::class,
            ConsoleMakeCommand::class,
            EnvironmentEncryptCommand::class,
            EventGenerateCommand::class,
            EventListCommand::class,
            EventMakeCommand::class,
            ExceptionMakeCommand::class,
            JobMakeCommand::class,
            KeyGenerateCommand::class,
            LangPublishCommand::class,
            ListenerMakeCommand::class,
            MailMakeCommand::class,
            NotificationMakeCommand::class,
            ObserverMakeCommand::class,
            PolicyMakeCommand::class,
            ProviderMakeCommand::class,
            RequestMakeCommand::class,
            RouteListCommand::class,
            RuleMakeCommand::class,
            ScopeMakeCommand::class,
            TestMakeCommand::class,
            ViewMakeCommand::class,
            SessionTableCommand::class,
        ]);
    }
}