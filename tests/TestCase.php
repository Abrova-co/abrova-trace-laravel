<?php

namespace AbrovaTrace\Laravel\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use AbrovaTrace\Laravel\AbrovaTraceServiceProvider;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            AbrovaTraceServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'AbrovaTrace' => \AbrovaTrace\Laravel\AbrovaTraceFacade::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('abrovatrace.api_key', 'ab_test_abc123');
        $app['config']->set('abrovatrace.server_secret', 'ab_srv_secret123');
        $app['config']->set('abrovatrace.enabled', true);
        $app['config']->set('abrovatrace.debug', false);
        $app['config']->set('abrovatrace.environment', 'testing');
    }
}
