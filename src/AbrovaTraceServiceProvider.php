<?php

namespace AbrovaTrace\Laravel;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use AbrovaTrace\Laravel\Config\AbrovaTraceConfig;
use AbrovaTrace\Laravel\Http\Middleware\AbrovaTraceMiddleware;
use AbrovaTrace\Laravel\Listeners\QueryListener;

class AbrovaTraceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/abrovatrace.php', 'abrovatrace');

        $this->app->singleton(AbrovaTrace::class, function ($app) {
            $cfg = new AbrovaTraceConfig($app['config']->get('abrovatrace', []));
            return new AbrovaTrace($cfg);
        });

        $this->app->alias(AbrovaTrace::class, 'abrovatrace');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/abrovatrace.php' => config_path('abrovatrace.php'),
        ], 'abrovatrace-config');

        $cfg = $this->app['config'];

        if (! $cfg->get('abrovatrace.enabled', true)) {
            return;
        }

        $key = $cfg->get('abrovatrace.api_key', '');
        $secret = $cfg->get('abrovatrace.server_secret', '');
        if (empty($key) || empty($secret)) {
            return;
        }

        if ($cfg->get('abrovatrace.middleware.enabled', true)) {
            $router = $this->app['router'];
            $router->pushMiddlewareToGroup('web', AbrovaTraceMiddleware::class);
            $router->pushMiddlewareToGroup('api', AbrovaTraceMiddleware::class);
        }

        if ($cfg->get('abrovatrace.exception_handler.enabled', true)) {
            $this->setupExceptionHandler();
        }

        if ($cfg->get('abrovatrace.performance.track_db_queries', true)) {
            Event::listen(QueryExecuted::class, QueryListener::class);
        }

        register_shutdown_function(function () {
            try {
                if ($this->app->resolved(AbrovaTrace::class)) {
                    $this->app->make(AbrovaTrace::class)->flush();
                }
            } catch (\Throwable) {
            }
        });
    }

    private function setupExceptionHandler(): void
    {
        $this->app->resolving(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            function ($handler) {
                if (! method_exists($handler, 'reportable')) {
                    return;
                }

                $handler->reportable(function (\Throwable $e) {
                    try {
                        if ($this->app->resolved(AbrovaTrace::class)) {
                            $this->app->make(AbrovaTrace::class)->captureException($e);
                        }
                    } catch (\Throwable) {
                    }
                    return false;
                });
            }
        );
    }
}
