<?php

namespace MacropaySolutions\KernelDev\Framework\Testing;

trait DatabaseMigrations
{
    /**
     * Run the database migrations for the application.
     *
     * @return void
     */
    public function runDatabaseMigrations()
    {
        $this->consoleApp('migrate:fresh');

        $this->beforeApplicationDestroyed(function () {
            $this->consoleApp('migrate:rollback');
        });
    }
}
