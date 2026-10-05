# Changelog

## 0.2.4 - 2026-10-06

- A report that is sent again is counted once: every error carries an `event_id` that stays the same on each attempt.

## 0.2.3 - 2026-10-05

- Exceptions are written to your application's own log again; the SDK used to stop Laravel's default logging.
- An exception captured more than once is sent as one error.

## 0.2.2 - 2026-10-05

First public release.

- Automatic reporting of unhandled exceptions through Laravel's exception handler.
- Request middleware for the `web` and `api` groups: request context, signed-in user, breadcrumbs and request timing.
- Manual `captureException` and `captureMessage`, with user, breadcrumbs, extra data and tags.
- Logging through a Laravel log channel or the `AbrovaTrace` facade, sent in batches.
- Performance monitoring: HTTP requests, slow database queries and `trackOperation` for your own code.
- `before_send` hook to change or drop an event before it is sent.
- Sample rate, rate limiting and ignored exceptions to control what is sent.
