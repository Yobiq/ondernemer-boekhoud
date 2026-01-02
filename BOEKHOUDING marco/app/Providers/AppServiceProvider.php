<?php

namespace App\Providers;

use App\Models\BtwReport;
use App\Models\Document;
use App\Models\Transaction;
use App\Observers\BtwReportObserver;
use App\Observers\DocumentObserver;
use App\Observers\TransactionObserver;
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
     * Register model observers for immutable audit logging
     */
    public function boot(): void
    {
        // Register observers for audit logging (immutable, append-only)
        Document::observe(DocumentObserver::class);
        Transaction::observe(TransactionObserver::class);
        BtwReport::observe(BtwReportObserver::class);
    }
}
