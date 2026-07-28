<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixWkhtmltopdf extends Command
{
    protected $signature = 'wkhtmltopdf:fix';

    protected $description = 'Fix wkhtmltopdf binary permissions and report shared library status';

    public function handle(): int
    {
        $binary = base_path('vendor/silvertipsoftware/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64');

        if (!file_exists($binary)) {
            $this->error("Binary not found: {$binary}");
            $this->line('Reinstall: composer install --no-dev');
            return self::FAILURE;
        }

        $this->line("Binary: {$binary}");
        $this->line("Size : " . filesize($binary) . " bytes");

        $perms = fileperms($binary);
        $this->line(sprintf("Perms: %o (%s)", $perms & 0777, $perms & 0100 ? 'executable' : 'NOT executable'));

        if (!($perms & 0100)) {
            $this->warn('Adding +x ...');
            @chmod($binary, 0755);
            $perms = fileperms($binary);
            $this->line(sprintf("Now  : %o (%s)", $perms & 0777, $perms & 0100 ? 'executable' : 'still NOT executable'));
        }

        $owner = function_exists('posix_getpwuid') ? (posix_getpwuid(fileowner($binary))['name'] ?? (string) fileowner($binary)) : (string) fileowner($binary);
        $group = function_exists('posix_getgrgid') ? (posix_getgrgid(filegroup($binary))['name'] ?? (string) filegroup($binary)) : (string) filegroup($binary);
        $this->line("Owner: {$owner}:{$group}");
        $phpUser = function_exists('posix_geteuid') ? (posix_getpwuid(posix_geteuid())['name'] ?? (string) posix_geteuid()) : 'n/a';
        $this->line("PHP user: {$phpUser}");

        $this->line('');
        $this->line('--- Shared library check ---');
        exec("ldd {$binary} 2>&1", $lddOut, $lddCode);
        foreach ($lddOut as $row) {
            if (stripos($row, 'not found') !== false) {
                $this->error($row);
            } else {
                $this->line($row);
            }
        }

        $this->line('');
        $this->line('--- Version test ---');
        exec("{$binary} --version 2>&1", $verOut, $verCode);
        foreach ($verOut as $row) {
            $this->line($row);
        }
        $this->line("Exit code: {$verCode}");

        if ($verCode === 126) {
            $this->error('Still failing. Likely fixes:');
            $this->line('  sudo chmod +x ' . $binary);
            $this->line('  sudo apt-get install -y libfontconfig1 libpng16-16 libjpeg62-turbo libxext6 libxrender1 libxtst6 libxi6');
            $this->line('Or install wkhtmltopdf system-wide and set WKHTMLTOPDF_BINARY=/usr/local/bin/wkhtmltopdf in .env');
            return self::FAILURE;
        }

        if ($verCode === 0) {
            $this->info('OK. wkhtmltopdf works.');
            return self::SUCCESS;
        }

        $this->warn('Non-zero exit but not 126. Check version output above.');
        return self::FAILURE;
    }
}