<?php

namespace MacropaySolutions\KernelDev\Database\Console;

use MacropaySolutions\Kernel\Console\Command;
use MacropaySolutions\Kernel\Contracts\Events\Dispatcher;
use MacropaySolutions\Kernel\Database\Connection;
use MacropaySolutions\Kernel\Database\ConnectionResolverInterface;
use MacropaySolutions\Kernel\Database\Events\SchemaDumped;
use MacropaySolutions\Kernel\Filesystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'schema:dump')]
class DumpCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'schema:dump
        {--database= : The database connection to use}
        {--path= : The path where the schema dump file should be stored}
        {--prune : Delete all existing migration files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump the given database schema';

    /**
     * Execute the console command.
     *
     * @param \MacropaySolutions\Kernel\Database\ConnectionResolverInterface $connections
     * @param \MacropaySolutions\Kernel\Contracts\Events\Dispatcher $dispatcher
     * @return void
     */
    public function handle(ConnectionResolverInterface $connections, Dispatcher $dispatcher)
    {
        $connection = $connections->connection($database = $this->input->getOption('database'));

        $this->schemaState($connection)->dump(
            $connection,
            $path = $this->path($connection)
        );

        $dispatcher->dispatch(new SchemaDumped($connection, $path));

        $info = 'Database schema dumped';

        if ($this->option('prune')) {
//            (new Filesystem)->deleteDirectory(
            \app(Filesystem::class)->deleteDirectory(
                database_path('migrations'),
                $preserve = false
            );

            $info .= ' and pruned';
        }

        $this->components->info($info . ' successfully.');
    }

    /**
     * Create a schema state instance for the given connection.
     *
     * @param \MacropaySolutions\Kernel\Database\Connection $connection
     * @return mixed
     */
    protected function schemaState(Connection $connection)
    {
        return $connection->getSchemaState()
            ->withMigrationTable($connection->getTablePrefix() . \config()->get('database.migrations', 'migrations'))
            ->handleOutputUsing(function ($type, $buffer) {
                $this->output->write($buffer);
            });
    }

    /**
     * Get the path that the dump should be written to.
     *
     * @param \MacropaySolutions\Kernel\Database\Connection $connection
     */
    protected function path(Connection $connection)
    {
        return tap(
            $this->option('path') ?: database_path('schema/' . $connection->getName() . '-schema.sql'),
            function ($path) {
                 //(new Filesystem)->ensureDirectoryExists(dirname($path));
                \app(Filesystem::class)->ensureDirectoryExists(dirname($path));
            }
        );
    }
}
