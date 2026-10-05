<?php

namespace AbrovaTrace\Laravel\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use AbrovaTrace\Laravel\Config\AbrovaTraceConfig;

class AbrovaTraceConfigTest extends TestCase
{
    public function test_creates_config_with_valid_options(): void
    {
        $config = new AbrovaTraceConfig([
            'api_key' => 'ab_test_abc123',
            'server_secret' => 'ab_srv_secret123',
            'environment' => 'production',
            'release' => '2.0.0',
        ]);

        $this->assertEquals('ab_test_abc123', $config->apiKey);
        $this->assertEquals('ab_srv_secret123', $config->serverSecret);
        $this->assertEquals('production', $config->environment);
        $this->assertEquals('2.0.0', $config->release);
        $this->assertTrue($config->isEnabled());
    }

    public function test_rejects_invalid_api_key_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('API key must start with ab_live_ or ab_test_');

        new AbrovaTraceConfig([
            'api_key' => 'invalid_key',
            'server_secret' => 'ab_srv_secret123',
        ]);
    }

    public function test_rejects_invalid_server_secret_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Server secret must start with ab_srv_');

        new AbrovaTraceConfig([
            'api_key' => 'ab_test_abc123',
            'server_secret' => 'bad_secret',
        ]);
    }

    public function test_disabled_when_api_key_empty(): void
    {
        $config = new AbrovaTraceConfig([
            'api_key' => '',
            'server_secret' => '',
        ]);

        $this->assertFalse($config->isEnabled());
    }

    public function test_disabled_when_explicitly_disabled(): void
    {
        $config = new AbrovaTraceConfig([
            'api_key' => 'ab_test_abc123',
            'server_secret' => 'ab_srv_secret123',
            'enabled' => false,
        ]);

        $this->assertFalse($config->isEnabled());
    }

    public function test_default_values(): void
    {
        $config = new AbrovaTraceConfig([
            'api_key' => '',
            'server_secret' => '',
        ]);

        $this->assertEquals('production', $config->environment);
        $this->assertEquals('0.1.0', $config->release);
        $this->assertEquals(5, $config->timeout);
        $this->assertEquals(50, $config->maxBreadcrumbs);
        $this->assertEquals(1.0, $config->sampleRate);
        $this->assertFalse($config->debug);
    }

    public function test_clamps_max_breadcrumbs(): void
    {
        $config = new AbrovaTraceConfig([
            'api_key' => '',
            'server_secret' => '',
            'max_breadcrumbs' => 200,
        ]);

        $this->assertEquals(100, $config->maxBreadcrumbs);
    }

    public function test_clamps_sample_rate(): void
    {
        $config = new AbrovaTraceConfig([
            'api_key' => '',
            'server_secret' => '',
            'sample_rate' => 2.0,
        ]);

        $this->assertEquals(1.0, $config->sampleRate);
    }

    public function test_clamps_timeout(): void
    {
        $config = new AbrovaTraceConfig([
            'api_key' => '',
            'server_secret' => '',
            'timeout' => 60,
        ]);

        $this->assertEquals(30, $config->timeout);
    }

    public function test_endpoint_helpers(): void
    {
        $config = new AbrovaTraceConfig([
            'api_key' => 'ab_test_abc123',
            'server_secret' => 'ab_srv_secret123',
            'api_url' => 'https://trace.abrova.ir',
        ]);

        $this->assertEquals('https://trace.abrova.ir/api/errors', $config->getErrorEndpoint());
        $this->assertEquals('https://trace.abrova.ir/api/messages', $config->getMessageEndpoint());
        $this->assertEquals('https://trace.abrova.ir/api/logs/ingest', $config->getLogIngestEndpoint());
        $this->assertEquals('https://trace.abrova.ir/api/logs/ingest/batch', $config->getLogBatchEndpoint());
        $this->assertEquals('https://trace.abrova.ir/api/performance/spans/batch', $config->getPerformanceBatchEndpoint());
    }

    public function test_user_agent_format(): void
    {
        $config = new AbrovaTraceConfig([
            'api_key' => '',
            'server_secret' => '',
        ]);

        $ua = $config->getUserAgent();
        $this->assertStringContainsString('AbrovaTrace-SDK/', $ua);
        $this->assertStringContainsString('laravel', $ua);
        $this->assertStringContainsString('PHP', $ua);
    }

    public function test_strips_trailing_slash_from_api_url(): void
    {
        $config = new AbrovaTraceConfig([
            'api_key' => '',
            'server_secret' => '',
            'api_url' => 'https://trace.abrova.ir/',
        ]);

        $this->assertEquals('https://trace.abrova.ir', $config->apiUrl);
    }

    public function test_sdk_version_and_platform_constants(): void
    {
        $this->assertEquals('0.2.2', AbrovaTraceConfig::SDK_VERSION);
        $this->assertEquals('laravel', AbrovaTraceConfig::PLATFORM);
    }
}
