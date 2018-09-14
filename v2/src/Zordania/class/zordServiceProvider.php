<?php

namespace Zordania\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class zordServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        DB::listen(function ($query) {
            var_dump([
                $query->sql,
                $query->bindings,
                $query->time
            ]);
        });

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
