<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$this->shouldLog($request)) {
            return $response;
        }

        $route = $request->route();
        $routeName = (string) optional($route)->getName();

        $subjectType = null;
        $subjectId = null;
        $routeParams = [];

        foreach ((array) optional($route)->parameters() as $key => $value) {
            if (is_scalar($value)) {
                $routeParams[$key] = $value;
                continue;
            }

            if (is_object($value) && method_exists($value, 'getKey')) {
                $routeParams[$key] = $value->getKey();
                $subjectType ??= $value::class;
                $subjectId ??= $value->getKey();
            }
        }

        ActivityLogger::log([
            'user_id' => $request->user()?->id,
            'category' => ActivityLogger::resolveRequestCategory($request),
            'action' => 'request_' . strtolower($request->method()),
            'description' => $this->requestDescription($request, $routeName),
            'subject_type' => $subjectType,
            'subject_id' => is_numeric($subjectId) ? (int) $subjectId : null,
            'context' => [
                'method' => $request->method(),
                'route_name' => $routeName !== '' ? $routeName : null,
                'path' => '/' . ltrim($request->path(), '/'),
                'status' => $response->getStatusCode(),
                'route_params' => $routeParams,
            ],
        ]);

        return $response;
    }

    private function shouldLog(Request $request): bool
    {
        if (!$request->user()) {
            return false;
        }

        if (in_array($request->method(), ['HEAD', 'OPTIONS'], true)) {
            return false;
        }

        if ($request->isMethod('GET')) {
            return false;
        }

        $routeName = (string) optional($request->route())->getName();
        if ($routeName === '') {
            return false;
        }

        if (str_starts_with($routeName, 'admin.logs.')) {
            return false;
        }

        if (str_contains($routeName, 'unread-count') || str_contains($routeName, 'recipients.search')) {
            return false;
        }

        if ($request->isMethod('GET') && $request->expectsJson()) {
            return false;
        }

        return true;
    }

    private function requestDescription(Request $request, string $routeName): string
    {
        $method = strtoupper($request->method());

        if ($routeName !== '') {
            return sprintf('%s request on %s.', $method, str_replace('.', ' > ', $routeName));
        }

        return sprintf('%s request on %s.', $method, '/' . ltrim($request->path(), '/'));
    }
}
