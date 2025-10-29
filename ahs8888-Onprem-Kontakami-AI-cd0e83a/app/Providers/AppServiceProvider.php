<?php

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\RateLimiter;

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
        $this->registerBuilderMarco();

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by(user()?->id ?: $request->ip());
        });

        if (App::environment('production')) {
            URL::forceScheme('https');
        }
    }

    private function registerBuilderMarco()
    {
        Builder::macro("whereLike", function ($attributes, $searchTerm) {
            $this->when($searchTerm,function(Builder $query) use($attributes,$searchTerm){
                $query->where(function (Builder $query) use ($attributes, $searchTerm) {
                    foreach (Arr::wrap($attributes) as $attribute) {
                        $query->when(
                            !($attribute instanceof \Illuminate\Contracts\Database\Query\Expression) && str_contains((string) $attribute, "."),
                            function (Builder $query) use ($attribute, $searchTerm) {
                                [$relation, $relationAttribute] = explode(".", $attribute);
                                $query->orWhereHas($relation, function (Builder $query) use ($relationAttribute, $searchTerm) {
                                    $query->where($relationAttribute, 'LIKE', "%{$searchTerm}%");
                                });
                            },
                            function(Builder $query) use ($attribute, $searchTerm) {
                                $query->orWhere($attribute,"LIKE",  "%{$searchTerm}%");
                            }
                        );
                    }
                });
            });
            return $this;
        });
    }
}
