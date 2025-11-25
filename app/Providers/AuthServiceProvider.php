<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\Owner;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Gate for owners to view reports
        Gate::define('view-reports', function ($user) {
            if (!$user) return false;
            return Owner::where('user_id', $user->id)->exists();
        });
    }
}
