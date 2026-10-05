<?php

return [
    // Your AbrovaTrace API key (ab_live_xxx or ab_test_xxx)
    'api_key' => env('ABROVATRACE_API_KEY', ''),

    // Server secret for authentication (ab_srv_xxx)
    'server_secret' => env('ABROVATRACE_SERVER_SECRET', ''),

    // Enable or disable the SDK
    'enabled' => env('ABROVATRACE_ENABLED', true),

    // Environment name (defaults to APP_ENV)
    'environment' => env('ABROVATRACE_ENVIRONMENT', env('APP_ENV', 'production')),

    // Release/version of your app
    'release' => env('ABROVATRACE_RELEASE', '0.1.0'),

    // Error capture sample rate (0.0 to 1.0)
    'sample_rate' => env('ABROVATRACE_SAMPLE_RATE', 1.0),

    // Last look at an error or message before it is sent. Receives a
    // AbrovaTraceError (exceptions) or a AbrovaTraceMessage (captureMessage).
    // Return it to send it, a modified one, or null to drop it - for scrubbing
    // a token out of a URL or discarding noise. Set it in a service provider, since a closure
    // cannot live in a cached config file:
    //
    //   config(['abrovatrace.before_send' => function ($error) {
    //       return str_contains($error->url, '/health') ? null : $error;
    //   }]);
    'before_send' => null,

    // Enable SDK debug logging
    'debug' => env('ABROVATRACE_DEBUG', false),

    // HTTP request timeout in seconds
    'timeout' => env('ABROVATRACE_TIMEOUT', 5),

    // Max breadcrumbs to keep per request (max 100)
    'max_breadcrumbs' => 50,

    // Rate limiting config
    'rate_limiting' => [
        'window_seconds' => 60,
        'max_errors_per_key' => 10,
        'max_total' => 100,
    ],

    // Logging config
    'logging' => [
        'enabled' => env('ABROVATRACE_LOGGING_ENABLED', true),
        'source_id' => env('ABROVATRACE_LOG_SOURCE_ID', null),
        'source_name' => env('ABROVATRACE_LOG_SOURCE_NAME', null),
        'batch_size' => 50,
    ],

    // Performance / APM config
    'performance' => [
        'enabled' => env('ABROVATRACE_PERFORMANCE_ENABLED', true),
        'batch_size' => 10,
        'track_db_queries' => true,
        'slow_query_threshold_ms' => 500,
    ],

    // Middleware config
    'middleware' => [
        'enabled' => true,
        'track_requests' => true,
        'track_user' => true,
        'capture_request_body' => false,
        'ignored_paths' => [
            '_debugbar/*',
            'telescope/*',
            'horizon/*',
        ],
    ],

    // Exception handler config
    'exception_handler' => [
        'enabled' => true,
        'report_4xx' => false,
        'ignored_exceptions' => [
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Validation\ValidationException::class,
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        ],
    ],

    // API endpoint (do not change unless directed by support)
    'api_url' => env('ABROVATRACE_API_URL', 'https://trace.abrova.ir'),
];
