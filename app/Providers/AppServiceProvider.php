<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use App\Services\AI\AIManager;
use App\Services\AI\LlamaProvider;
use App\Services\AI\OllamaProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AIManager::class, function () {
            return new AIManager(
                new LlamaProvider(),
                new OllamaProvider()
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Windows / PHP curl sin cadena CA del sistema falla HTTPS (cURL 60).
        // Usamos el bundle Mozilla si está presente (certificates/cacert.pem).
        $caBundle = base_path('certificates/cacert.pem');
        if (is_readable($caBundle)) {
            Http::globalOptions([
                'verify' => $caBundle,
            ]);
        }
    }
}
