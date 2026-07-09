# php-kernel-dev

[![Total Downloads](https://img.shields.io/packagist/dt/macropay-solutions/php-kernel-dev)](https://packagist.org/packages/macropay-solutions/php-kernel-dev)
[![Latest Stable Version](https://img.shields.io/packagist/v/macropay-solutions/php-kernel-dev)](https://packagist.org/packages/macropay-solutions/php-kernel-dev)
[![License](https://img.shields.io/packagist/l/macropay-solutions/php-kernel-dev)](https://packagist.org/packages/macropay-solutions/php-kernel-dev)

Development Library for [PHP-kernel](https://github.com/macropay-solutions/php-kernel)

## Installation

1\. Run composer command:

``` composer require macropay-solutions/php-kernel-dev --dev ```


```

These are already registered in the Framework:
```php
use MacropaySolutions\KernelDev\Cache\Console\CacheTableCommand;
use MacropaySolutions\KernelDev\Database\Console\DumpCommand;
use MacropaySolutions\KernelDev\Database\Console\Migrations\MigrateMakeCommand;
use MacropaySolutions\KernelDev\Database\Console\Seeds\SeederMakeCommand;
use MacropaySolutions\KernelDev\Database\Console\WipeCommand;
use MacropaySolutions\KernelDev\Queue\Console\BatchesTableCommand;
use MacropaySolutions\KernelDev\Queue\Console\FailedTableCommand;
use MacropaySolutions\KernelDev\Queue\Console\TableCommand;

use MacropaySolutions\KernelDev\Database\Console\Migrations\MigrateMakeCommand;
use MacropaySolutions\KernelDev\Database\Migrations\MigrationCreator;
```

## Usage

```bash
php run about {--only= : The section to display}
        {--json : Output the information as JSON}
```
```bash
php run config:show {config : The configuration file to show}
```
```bash
php run db:show {--database= : The database connection}
        {--json : Output the database information as JSON}
        {--counts : Show the table row count <bg=red;options=bold> Note: This can be slow on large databases </>};
        {--views : Show the database views <bg=red;options=bold> Note: This can be slow on large databases </>}
```
```bash
php run db:table
        {table? : The name of the table}
        {--database= : The database connection}
        {--json : Output the table information as JSON}
```
```bash
php run docs {page? : The documentation page to open} {section? : The section of the page to open}
```
```bash
php run env:encrypt
        {--key= : The encryption key}
        {--cipher= : The encryption cipher}
        {--env= : The environment to be encrypted}
        {--force : Overwrite the existing encrypted environment file}
```
```bash
php run event:list {--event= : Filter the events by name}
```
```bash
php run key:generate
        {--show : Display the key instead of modifying files}
        {--force : Force the operation to run when in production}
```
```bash
php run lang:publish
        {--existing : Publish and overwrite only the files that have already been published}
        {--force : Overwrite any existing files}
```
```bash
php run make:migration {name : The name of the migration}
        {--create= : The table to be created}
        {--table= : The table to migrate}
        {--path= : The location where the migration file should be created}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
        {--fullpath : Output the full path of the migration (Deprecated)}
```
```bash
php run model:show {model : The model to show}
        {--database= : The database connection to use}
        {--json : Output the model as JSON}
```
```bash
php run schedule:list
        {--timezone= : The timezone that times should be displayed in}
        {--next : Sort the listed tasks by their next due date}
```
```bash
php run schedule:test {--name= : The name of the scheduled command to run}
```
```bash
php run schema:dump
        {--database= : The database connection to use}
        {--path= : The path where the schema dump file should be stored}
        {--prune : Delete all existing migration files}
```

```bash
php run cache:table
```
```bash
php run channel:list
```
```bash
php run db:wipe
```
```bash
php run make:cast
```
```bash
php run make:channel
```
```bash
php run make:command
```
```bash
php run make:event
```
```bash
php run make:exception
```
```bash
php run make:factory
```
```bash
php run make:job
```
```bash
php run make:listener
```
```bash
php run make:mail
```
```bash
php run make:notification
```
```bash
php run make:observer
```
```bash
php run make:policy
```
```bash
php run make:provider
```
```bash
php run make:request
```
```bash
php run make:rule
```
```bash
php run make:scope
```
```bash
php run make:seeder
```
```bash
php run make:test
```
```bash
php run make:view
```
```bash
php run notifications:table
```
```bash
php run queue:batches-table
```
```bash
php run queue:failed-table
```
```bash
php run queue:table
```
```bash
php run serve
```
```bash
php run session:table
```


## License

This package is licensed under the [license MIT](http://opensource.org/licenses/MIT).
