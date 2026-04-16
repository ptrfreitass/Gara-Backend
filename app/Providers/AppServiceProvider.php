<?php

namespace App\Providers;

use App\Models\FinanceTransaction;
use App\Observers\FinanceTransactionObserver;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use App\Services\Auth\AuthService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind do AuthService como singleton (seguro no Octane pois é stateless)
        $this->app->singleton(AuthService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FinanceTransaction::observe(FinanceTransactionObserver::class);
        // Remover o wrapper "data" das Resources
        JsonResource::withoutWrapping();
    }
}
