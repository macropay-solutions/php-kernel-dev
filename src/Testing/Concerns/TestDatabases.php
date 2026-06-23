<?php

namespace MacropaySolutions\KernelDev\Testing\Concerns;

use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use MacropaySolutions\KernelDev\Foundation\Testing;

trait TestDatabases
{
    /**
     * Indicates if the test database schema is up to date.
     *
     * @var bool
     */
    protected static $schemaIsUpToDate = false;

    /**
     * Boot a test database.
     *
     * @return void
     */
    protected function bootTestDatabase()
    {
        \app(\MacropaySolutions\KernelDev\Testing\ParallelTesting::class)->setUpProcess(function () {
            $this->whenNotUsingInMemoryDatabase(function ($database) {
                if (\app(\MacropaySolutions\KernelDev\Testing\ParallelTesting::class)->option('recreate_databases')) {
                    \app('db.schema')->dropDatabaseIfExists(
                        $this->testDatabase($database)
                    );
                }
            });
        });

        \app(\MacropaySolutions\KernelDev\Testing\ParallelTesting::class)->setUpTestCase(function ($testCase) {
            $uses = array_flip(class_uses_recursive(get_class($testCase)));

            $databaseTraits = [
                Testing\DatabaseMigrations::class,
                Testing\DatabaseTransactions::class,
                Testing\DatabaseTruncation::class,
                Testing\RefreshDatabase::class,
            ];

            if (Arr::hasAny($uses, $databaseTraits) && !\app(\MacropaySolutions\KernelDev\Testing\ParallelTesting::class)->option('without_databases')) {
                $this->whenNotUsingInMemoryDatabase(function ($database) use ($uses) {
                    [$testDatabase, $created] = $this->ensureTestDatabaseExists($database);

                    $this->switchToDatabase($testDatabase);

                    if (isset($uses[Testing\DatabaseTransactions::class])) {
                        $this->ensureSchemaIsUpToDate();
                    }

                    if ($created) {
                        \app(\MacropaySolutions\KernelDev\Testing\ParallelTesting::class)->callSetUpTestDatabaseCallbacks($testDatabase);
                    }
                });
            }
        });

        \app(\MacropaySolutions\KernelDev\Testing\ParallelTesting::class)->tearDownProcess(function () {
            $this->whenNotUsingInMemoryDatabase(function ($database) {
                if (\app(\MacropaySolutions\KernelDev\Testing\ParallelTesting::class)->option('drop_databases')) {
                    \app('db.schema')->dropDatabaseIfExists(
                        $this->testDatabase($database)
                    );
                }
            });
        });
    }

    /**
     * Ensure a test database exists and returns its name.
     *
     * @param string $database
     * @return array
     */
    protected function ensureTestDatabaseExists($database)
    {
        $testDatabase = $this->testDatabase($database);

        try {
            $this->usingDatabase($testDatabase, function () {
                \app('db.schema')->hasTable('dummy');
            });
        } catch (QueryException) {
            $this->usingDatabase($database, function () use ($testDatabase) {
                \app('db.schema')->dropDatabaseIfExists($testDatabase);
                \app('db.schema')->createDatabase($testDatabase);
            });

            return [$testDatabase, true];
        }

        return [$testDatabase, false];
    }

    /**
     * Ensure the current database test schema is up to date.
     *
     * @return void
     */
    protected function ensureSchemaIsUpToDate()
    {
        if (!static::$schemaIsUpToDate) {
            \app(Illuminate\Contracts\Console\Kernel::class)->call('migrate');

            static::$schemaIsUpToDate = true;
        }
    }

    /**
     * Runs the given callable using the given database.
     *
     * @param string $database
     * @param callable $callable
     * @return void
     */
    protected function usingDatabase($database, $callable)
    {
        $original = \app('db')->getConfig('database');

        try {
            $this->switchToDatabase($database);
            $callable();
        } finally {
            $this->switchToDatabase($original);
        }
    }

    /**
     * Apply the given callback when tests are not using in memory database.
     *
     * @param callable $callback
     * @return void
     */
    protected function whenNotUsingInMemoryDatabase($callback)
    {
        if (\app(\MacropaySolutions\KernelDev\Testing\ParallelTesting::class)->option('without_databases')) {
            return;
        }

        $database = \app('db')->getConfig('database');

        if ($database !== ':memory:') {
            $callback($database);
        }
    }

    /**
     * Switch to the given database.
     *
     * @param string $database
     * @return void
     */
    protected function switchToDatabase($database)
    {
        \app('db')->purge();

        $default = config('database.default');

        $url = config("database.connections.{$default}.url");

        if ($url) {
            config()->set(
                "database.connections.{$default}.url",
                preg_replace('/^(.*)(\/[\w-]*)(\??.*)$/', "$1/{$database}$3", $url),
            );
        } else {
            config()->set(
                "database.connections.{$default}.database",
                $database,
            );
        }
    }

    /**
     * Returns the test database name.
     *
     * @return string
     */
    protected function testDatabase($database)
    {
        $token = \app(\MacropaySolutions\KernelDev\Testing\ParallelTesting::class)->token();

        return "{$database}_test_{$token}";
    }
}
