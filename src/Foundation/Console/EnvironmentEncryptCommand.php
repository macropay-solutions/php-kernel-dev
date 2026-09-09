<?php

namespace MacropaySolutions\KernelDev\Foundation\Console;

use Exception;
use MacropaySolutions\Kernel\Console\Command;
use MacropaySolutions\Kernel\Encryption\Encrypter;
use MacropaySolutions\Kernel\Filesystem\Filesystem;
use MacropaySolutions\Kernel\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'env:encrypt')]
class EnvironmentEncryptCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:encrypt
        {--key= : The encryption key}
        {--cipher= : The encryption cipher}
        {--env= : The environment to be encrypted}
        {--force : Overwrite the existing encrypted environment file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt an environment file';

    /**
     * The filesystem instance.
     *
     * @var \MacropaySolutions\Kernel\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @param \MacropaySolutions\Kernel\Filesystem\Filesystem $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $cipher = $this->option('cipher') ?: 'AES-256-CBC';

        $key = $this->option('key');

        $keyPassed = $key !== null;

        $environmentFile = $this->option('env')
            ? base_path('.env') . '.' . $this->option('env')
            : $this->app->environmentFilePath();

        $encryptedFile = $environmentFile . '.encrypted';

        if (!$keyPassed) {
            $key = Encrypter::generateKey($cipher);
        }

        if (!$this->files->exists($environmentFile)) {
            $this->error('Environment file not found.');

            return Command::FAILURE;
        }

        if ($this->files->exists($encryptedFile) && !$this->option('force')) {
            $this->error('Encrypted environment file already exists.');

            return Command::FAILURE;
        }

        try {
            $encrypter = new Encrypter($this->parseKey($key), $cipher);

            $this->files->put(
                $encryptedFile,
                $encrypter->encrypt($this->files->get($environmentFile))
            );
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return Command::FAILURE;
        }

        $this->info('Environment successfully encrypted.');

        $this->twoColumnDetail('Key', $keyPassed ? $key : 'base64:' . base64_encode($key));
        $this->twoColumnDetail('Cipher', $cipher);
        $this->twoColumnDetail('Encrypted file', $encryptedFile);

        $this->newLine();
    }

    /**
     * Parse the encryption key.
     *
     * @throws \Exception
     */
    protected function parseKey(string $key): string
    {
        if (Str::startsWith($key, $prefix = 'base64:')) {
            $key = \base64_decode(Str::after($key, $prefix), true);

            if (false === $key) {
                throw new \Exception('Invalid base64 encoded string.');
            }
        }

        return $key;
    }
}
