<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::defaultView('dasgboard.partials.pagination');

        // TLS may terminate at the hosting proxy while PHP receives HTTP.
        if ($this->app->environment('production') && parse_url(config('app.url'), PHP_URL_SCHEME) === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\View::composer(['dasgboard.layouts.app', 'auth.login'], function ($view) {
            $settings = \App\Models\LandingSection::where('slug', 'site')->value('content') ?? [];
            $view->with('siteBrandName', $settings['site_name'] ?? \App\Models\LandingSection::defaults('site')['site_name']);
            $view->with('siteBrandLogo', empty($settings['image']) ? null : route('media.show', ['path' => $settings['image']]));
        });
    }
}
