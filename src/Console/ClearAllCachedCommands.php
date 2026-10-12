<?php

namespace MacropaySolutions\KernelDev\Console;

use MacropaySolutions\Kernel\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'bootstrap-cache:clear')]
class ClearAllCachedCommands extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bootstrap-cache:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all bootstrap-file cache';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->call('macro:clear');
        $this->call('merge-cached-files:clear');
        $this->call('config:clear');
        $this->call('commands:clear');
        $this->call('route:clear');
        $this->call('autowiring:clear');
        $this->call('event:clear');
        $this->call('view:clear');
    }
}
