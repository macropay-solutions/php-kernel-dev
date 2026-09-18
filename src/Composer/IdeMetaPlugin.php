<?php

namespace MacropaySolutions\KernelDev\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use MacropaySolutions\KernelDev\Support\IdeMetaGenerator;

class IdeMetaPlugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
        // Required by interface, no action needed on activation
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        // Required by interface
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        // Required by interface
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_AUTOLOAD_DUMP => 'onPostAutoloadDump',
        ];
    }

    public function onPostAutoloadDump(Event $event): void
    {
        // Fail silently if the application class isn't loaded yet
        if (!\class_exists(\MacropaySolutions\Framework\Application::class)) {
            return;
        }

        try {
            // getcwd() ensures it writes to the root of the project running Composer
            if (IdeMetaGenerator::generate(getcwd())) {
                $event->getIO()->write('<info>Macropay-Solutions: IDE Meta file generated successfully.</info>');
            }
        } catch (\Throwable $e) {
            $event->getIO()->writeError('<error>Macropay-Solutions: Failed to generate IDE Meta file - ' . $e->getMessage() . '</error>');
        }
    }
}