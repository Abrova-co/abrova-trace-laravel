<?php

namespace AbrovaTrace\Laravel\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use AbrovaTrace\Laravel\Models\Breadcrumb;
use AbrovaTrace\Laravel\Performance\PerformanceSpan;
use AbrovaTrace\Laravel\AbrovaTrace;

class QueryListener
{
    private AbrovaTrace $sdk;

    public function __construct(AbrovaTrace $sdk)
    {
        $this->sdk = $sdk;
    }

    public function handle(QueryExecuted $event): void
    {
        $this->sdk->addBreadcrumb(
            Breadcrumb::database(query: $event->sql, ms: $event->time)
        );

        $threshold = config('abrovatrace.performance.slow_query_threshold_ms', 500);

        if ($event->time >= $threshold && config('abrovatrace.performance.enabled', true)) {
            $this->sdk->reportPerformanceSpan(
                PerformanceSpan::forDbQuery([
                    'queryType' => $this->parseQueryType($event->sql),
                    'tableName' => $this->parseTableName($event->sql),
                    'durationMs' => $event->time,
                    'startTime' => now()->subMilliseconds((int) $event->time),
                ])
            );
        }
    }

    private function parseQueryType(string $sql): string
    {
        $first = strtoupper(strtok(ltrim($sql), ' ') ?: '');
        $known = ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'ALTER', 'DROP'];
        return in_array($first, $known, true) ? $first : 'OTHER';
    }

    private function parseTableName(string $sql): string
    {
        if (preg_match('/\b(?:FROM|INTO|UPDATE|TABLE)\s+[`"\']?(\w+)[`"\']?/i', $sql, $m)) {
            return $m[1];
        }
        return 'unknown';
    }
}
