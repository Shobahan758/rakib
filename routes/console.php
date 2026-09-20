<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:production-check', function () {
    $checks = [
        'Production environment' => app()->environment('production'),
        'Debug disabled' => ! config('app.debug'),
        'Application key configured' => filled(config('app.key')),
        'HTTPS public URL configured' => filter_var(config('app.url'), FILTER_VALIDATE_URL)
            && parse_url(config('app.url'), PHP_URL_SCHEME) === 'https'
            && ! in_array(parse_url(config('app.url'), PHP_URL_HOST), ['localhost', '127.0.0.1', 'your-domain.com'], true),
        'Secure session cookies' => (bool) config('session.secure'),
        'Storage writable' => is_writable(storage_path('framework')) && is_writable(storage_path('logs')),
        'Bootstrap cache writable' => is_writable(base_path('bootstrap/cache')),
        'Production assets present' => is_file(public_path('asset/css/responsive.css'))
            && is_file(public_path('asset/js/video-reviews.js')),
        'Vite development marker absent' => ! is_file(public_path('hot')),
    ];

    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $checks['Database reachable'] = true;
        $migrator = app('migrator');
        $checks['Migrations up to date'] = $migrator->repositoryExists()
            && count(array_diff(array_keys($migrator->getMigrationFiles(database_path('migrations'))), $migrator->getRepository()->getRan())) === 0;
    } catch (\Throwable) {
        $checks['Database reachable and migrated'] = false;
    }

    foreach ($checks as $label => $passed) {
        $passed ? $this->info('PASS: '.$label) : $this->error('FAIL: '.$label);
    }

    return in_array(false, $checks, true) ? 1 : 0;
})->purpose('Check live hosting configuration without changing data');
