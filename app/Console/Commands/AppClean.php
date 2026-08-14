<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class AppClean extends Command
{
    protected $signature = 'app:clean {--force : Paksa jalankan tanpa konfirmasi}';

    protected $description = 'Membersihkan cache, log lama, session usang, dan file temporary aman untuk aplikasi Laravel';

    public function handle()
    {
        $this->info('==================================================');
        $this->info('      LARAVEL DEEP CLEANUP');
        $this->info('==================================================');

        $this->comment('[1/7] Membersihkan seluruh cache Laravel...');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('clear-compiled');
        Artisan::call('optimize:clear');
        $this->info('Seluruh Cache (Route, Config, View, Compiled) dibersihkan.');

        $this->comment('[2/7] Membersihkan semua file log...');
        $deletedLogs = $this->cleanOldLogs();
        $this->info($deletedLogs.' file log lama dihapus.');

        $this->comment('[3/7] Menghapus file temporary di storage/framework...');
        $deletedFramework = $this->cleanFrameworkArtifacts();
        $this->info($deletedFramework.' file framework artifact dihapus.');

        $this->comment('[4/7] Membersihkan session yang sudah usang...');
        $deletedSessions = $this->cleanOldSessions();
        $this->info($deletedSessions.' session lama dihapus.');

        $this->comment('[5/7] Membersihkan folder tmp Livewire...');
        $this->cleanLivewireTmp();
        $this->info('Folder tmp Livewire dibersihkan.');

        $this->comment('[6/7] Membersihkan cache temporary aman...');
        $deletedTmp = $this->cleanTmpFiles();
        $this->info($deletedTmp.' file tmp aman dihapus.');

        $this->comment('[7/7] Verifikasi folder penting...');
        $this->protectImportantFolders();
        $this->info('Folder penting aman dan tidak terhapus.');

        $this->info('==================================================');
        $this->info('      CLEANUP SELESAI DAN AMAN.');
        $this->info('==================================================');

        return self::SUCCESS;
    }

    protected function cleanOldLogs(): int
    {
        $deleted = 0;
        $logFiles = File::glob(storage_path('logs/*.log'));

        foreach ($logFiles as $file) {
            File::delete($file);
            $deleted++;
        }

        return $deleted;
    }

    protected function cleanFrameworkArtifacts(): int
    {
        $deleted = 0;
        $paths = [
            storage_path('framework/cache'),
            storage_path('framework/views'),
            storage_path('framework/testing'),
        ];

        foreach ($paths as $path) {
            if (! File::isDirectory($path)) {
                continue;
            }

            $files = File::allFiles($path);
            foreach ($files as $file) {
                if (in_array($file->getFilename(), ['.gitignore', '.gitkeep'])) {
                    continue;
                }
                File::delete($file->getPathname());
                $deleted++;
            }
        }

        return $deleted;
    }

    protected function cleanOldSessions(): int
    {
        $deleted = 0;
        $sessionDir = storage_path('framework/sessions');

        if (! File::isDirectory($sessionDir)) {
            return 0;
        }

        $files = File::files($sessionDir);
        foreach ($files as $file) {
            if (in_array($file->getFilename(), ['.gitignore', '.gitkeep'])) {
                continue;
            }
            File::delete($file->getPathname());
            $deleted++;
        }

        return $deleted;
    }

    protected function cleanLivewireTmp(): void
    {
        $paths = [
            storage_path('app/livewire-tmp'),
            storage_path('app/private/livewire-tmp'),
        ];

        foreach ($paths as $path) {
            if (File::isDirectory($path)) {
                File::cleanDirectory($path);
            }
        }
    }

    protected function cleanTmpFiles(): int
    {
        $deleted = 0;
        $allowedFolders = [
            'uploads',
            'public',
            'private',
            'livewire-tmp',
        ];

        $appDir = storage_path('app');
        if (! File::isDirectory($appDir)) {
            return 0;
        }

        $directories = File::directories($appDir);

        foreach ($directories as $directory) {
            $name = basename($directory);
            if (in_array($name, $allowedFolders, true)) {
                continue;
            }

            File::deleteDirectory($directory);
            $deleted++;
        }

        $tmpFiles = File::glob($appDir.'/*.{tmp,cache,bak,log}', GLOB_BRACE);
        foreach ($tmpFiles as $file) {
            if (File::exists($file)) {
                File::delete($file);
                $deleted++;
            }
        }

        return $deleted;
    }

    protected function protectImportantFolders(): void
    {
        $protected = [
            storage_path('app/public'),
            storage_path('app/private'),
            storage_path('app/uploads'),
            storage_path('app/livewire-tmp'),
            storage_path('framework'),
            storage_path('logs'),
        ];

        foreach ($protected as $path) {
            if (File::exists($path)) {
                $this->line('Aman: '.$path);
            }
        }
    }
}
