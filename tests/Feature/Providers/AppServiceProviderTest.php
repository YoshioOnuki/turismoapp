<?php

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\URL;

test('genera enlaces https cuando la aplicación está en producción', function () {
    $entornoOriginal = $this->app->environment();

    try {
        $this->app->detectEnvironment(fn (): string => 'production');
        (new AppServiceProvider($this->app))->boot();

        expect(route('inicio'))->toStartWith('https://');
    } finally {
        URL::forceHttps(false);
        $this->app->detectEnvironment(fn (): string => $entornoOriginal);
    }
});
