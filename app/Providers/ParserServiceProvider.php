<?php

namespace App\Providers;

use App\Services\Parser\Parser;
use App\Services\Parser\WorkUaParser;
use Illuminate\Support\ServiceProvider;

class ParserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(Parser::class, function ($app) {
            return new Parser(
                $app->make(WorkUaParser::class),
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
