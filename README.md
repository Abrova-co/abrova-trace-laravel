# Abrova Trace for Laravel

Laravel package for Abrova Trace: it reports exceptions, messages, logs and performance data from your Laravel application to your Abrova Trace project.

## Requirements

- PHP 8.1 or later
- Laravel 10, 11 or 12

## Install

Add the Abrova package repository to your `composer.json`:

```json
// composer.json
"repositories": [
    { "type": "composer", "url": "https://center.abrova.ir/api/packages/abrova/composer" }
]
```

Then install the package:

```
composer require abrova-trace/laravel-sdk
```

The service provider and the `AbrovaTrace` facade are registered automatically.

## Quick start

Add your API key and server secret to `.env`. Both come from the Abrova console, and the SDK stays off until both are set.

```env
ABROVATRACE_API_KEY=ab_live_your_api_key
ABROVATRACE_SERVER_SECRET=ab_srv_your_server_secret
ABROVATRACE_RELEASE=1.0.0
```

Unhandled exceptions are now reported. To change other options, publish the config file to `config/abrovatrace.php`:

```
php artisan vendor:publish --tag=abrovatrace-config
```

## What is captured automatically

- Unhandled exceptions, through Laravel's exception handler
- Every request in the `web` and `api` middleware groups: method, path, route, status and duration
- The signed-in user (id, email, name)
- Database queries as breadcrumbs, and queries slower than 500 ms as performance data

## Usage

Capture an exception:

```php
use AbrovaTrace\Laravel\AbrovaTraceFacade as AbrovaTrace;

try {
    $payment->charge($order);
} catch (\Throwable $e) {
    AbrovaTrace::captureException($e, ['extra' => ['order_id' => $order->id]]);
}
```

Capture a message (`level` is `debug`, `info`, `warning` or `error`):

```php
AbrovaTrace::captureMessage('Payment processed', ['level' => 'info', 'extra' => ['amount' => 99.99]]);
```

Set the user (done for you on requests with a signed-in user):

```php
AbrovaTrace::setUser(['id' => $user->id, 'email' => $user->email, 'name' => $user->name]);
```

Add a breadcrumb:

```php
use AbrovaTrace\Laravel\Models\Breadcrumb;

AbrovaTrace::addBreadcrumb(Breadcrumb::custom('User signed up', 'auth', ['plan' => 'pro']));
```

Send logs through a Laravel log channel. Add the channel to `config/logging.php`:

```php
'abrovatrace' => [
    'driver' => 'custom',
    'via' => \AbrovaTrace\Laravel\Logging\AbrovaTraceLogChannel::class,
],
```

```php
Log::channel('abrovatrace')->info('Order created', ['order_id' => 123]);
```

You can also add `abrovatrace` to the `channels` of your `stack` channel, or log without a channel: `AbrovaTrace::info()`, `warn()`, `logError()`, `fatal()`.

Measure an operation:

```php
$result = AbrovaTrace::trackOperation('process_payment', fn () => $gateway->charge($order), [
    'tags' => ['provider' => 'bank'],
]);
```

Change or drop an event before it is sent. Set this in a service provider's `boot()` method; return `null` to drop the event:

```php
config(['abrovatrace.before_send' => function ($event) {
    return str_contains($event->url ?? '', '/health') ? null : $event;
}]);
```

## Configuration

Keys of `config/abrovatrace.php`:

| Key | Env variable | Default | Description |
|-----|--------------|---------|-------------|
| `api_key` | `ABROVATRACE_API_KEY` | empty | API key (`ab_live_...` or `ab_test_...`) |
| `server_secret` | `ABROVATRACE_SERVER_SECRET` | empty | Server secret (`ab_srv_...`) |
| `enabled` | `ABROVATRACE_ENABLED` | `true` | Set to `false` to send nothing |
| `environment` | `ABROVATRACE_ENVIRONMENT` | `APP_ENV` | Environment name |
| `release` | `ABROVATRACE_RELEASE` | `0.1.0` | Your app version |
| `sample_rate` | `ABROVATRACE_SAMPLE_RATE` | `1.0` | Share of errors and messages to send, from 0.0 to 1.0 |
| `timeout` | `ABROVATRACE_TIMEOUT` | `5` | Request timeout in seconds (1 to 30) |
| `debug` | `ABROVATRACE_DEBUG` | `false` | Write SDK debug lines to the PHP error log |
| `logging.source_id` | `ABROVATRACE_LOG_SOURCE_ID` | `null` | Id of this log source, for example `my-laravel-api` |
| `performance.enabled` | `ABROVATRACE_PERFORMANCE_ENABLED` | `true` | Send performance data |
| `performance.slow_query_threshold_ms` | | `500` | Queries at or above this duration are reported |
| `middleware.enabled` | | `true` | Add the request middleware to `web` and `api` |
| `middleware.ignored_paths` | | `_debugbar/*`, `telescope/*`, `horizon/*` | Paths the middleware skips |
| `exception_handler.report_4xx` | | `false` | Report HTTP exceptions with a 4xx status |
| `exception_handler.ignored_exceptions` | | authentication, validation, not found | Exception classes that are never reported |
| `api_url` | `ABROVATRACE_API_URL` | `https://trace.abrova.ir` | Abrova Trace API address |

## License

MIT, see [LICENSE](LICENSE).
