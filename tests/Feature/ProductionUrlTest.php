<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProductionUrlTest extends TestCase
{
    public function test_https_production_urls_work_behind_an_http_proxy(): void
    {
        $this->app->instance('env', 'production');
        config(['app.url' => 'https://shop.example.com']);
        URL::forceRootUrl('http://shop.example.com');
        (new AppServiceProvider($this->app))->boot();

        $this->assertSame('https://shop.example.com/asset/css/responsive.css', asset('asset/css/responsive.css'));
        $this->assertStringStartsWith('https://shop.example.com/', route('media.show', ['path' => 'products/example.png']));
        $this->assertSame('/orders', route('orders.store', [], false));
    }

    public function test_local_http_development_is_preserved(): void
    {
        $this->app->instance('env', 'local');
        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
        URL::forceScheme(null);
        (new AppServiceProvider($this->app))->boot();

        $this->assertSame('http://localhost/asset/css/responsive.css', asset('asset/css/responsive.css'));
    }
}
