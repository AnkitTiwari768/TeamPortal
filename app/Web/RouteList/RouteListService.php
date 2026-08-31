<?php

declare(strict_types=1);

namespace App\Web\RouteList;

use Closure;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;

class RouteListService
{
    /**
     * Build the normalized, UI-ready dataset for the /route-list admin page.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $routes = [];

        foreach (RouteFacade::getRoutes() as $route) {
            $routes[] = $this->transform($route);
        }

        usort($routes, static fn (array $a, array $b) => [$a['uri'], $a['method']] <=> [$b['uri'], $b['method']]);

        return $routes;
    }

    /**
     * Distinct Assignment / Middleware values across all routes, for the filter dropdowns.
     *
     * @return array{assignments: array<int, string>, middlewares: array<int, string>}
     */
    public function filterOptions(): array
    {
        $assignments = [];
        $middlewares = [];

        foreach ($this->all() as $route) {
            $assignments[$route['assignment']] = true;
            foreach ($route['middleware'] as $middleware) {
                $middlewares[$middleware] = true;
            }
        }

        $assignments = array_keys($assignments);
        $middlewares = array_keys($middlewares);
        sort($assignments);
        sort($middlewares);

        return ['assignments' => $assignments, 'middlewares' => $middlewares];
    }

    /**
     * Apply the Route List UI's search/filter/sort/pagination server-side and
     * return just the requested page, mirroring the client-side logic this
     * replaces so behavior stays identical from the user's perspective.
     *
     * @param array<string, mixed> $params
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    public function filter(array $params): array
    {
        $routes = $this->all();

        $search = Str::lower(trim((string) ($params['search'] ?? '')));
        $method = (string) ($params['method'] ?? '');
        $uri = Str::lower(trim((string) ($params['uri'] ?? '')));
        $assignment = (string) ($params['assignment'] ?? '');
        $middleware = (string) ($params['middleware'] ?? '');
        $named = (string) ($params['named'] ?? '');
        $type = (string) ($params['type'] ?? '');
        $cardToggle = (string) ($params['cardToggle'] ?? '');
        $sortKey = (string) ($params['sortKey'] ?? 'uri');
        $sortDir = ($params['sortDir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = max(1, (int) ($params['perPage'] ?? 25));

        $methodOrder = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];
        $methodCard = str_contains($cardToggle, ',') ? explode(',', $cardToggle) : null;

        $filtered = array_values(array_filter($routes, function (array $route) use (
            $search, $method, $uri, $assignment, $middleware, $named, $type, $cardToggle, $methodCard, $methodOrder
        ) {
            if ($search !== '' && !str_contains($route['search'], $search)) {
                return false;
            }
            if ($uri !== '' && !str_contains(Str::lower($route['uri']), $uri)) {
                return false;
            }
            if ($method !== '' && !in_array($method, $route['methods'], true)) {
                return false;
            }
            if ($assignment !== '' && $route['assignment'] !== $assignment) {
                return false;
            }
            if ($middleware !== '' && !in_array($middleware, $route['middleware'], true)) {
                return false;
            }
            if ($named === 'named' && $route['name'] === null) {
                return false;
            }
            if ($named === 'unnamed' && $route['name'] !== null) {
                return false;
            }
            if ($type === 'controller' && $route['isClosure']) {
                return false;
            }
            if ($type === 'closure' && !$route['isClosure']) {
                return false;
            }
            if ($cardToggle === 'named' && $route['name'] === null) {
                return false;
            }
            if ($cardToggle === 'middleware' && $route['middleware'] === []) {
                return false;
            }
            if ($cardToggle === 'controller' && $route['isClosure']) {
                return false;
            }
            if ($methodCard !== null && !array_intersect($methodCard, $route['methods'])) {
                return false;
            }
            if ($methodCard === null && $cardToggle !== '' && in_array($cardToggle, $methodOrder, true) && !in_array($cardToggle, $route['methods'], true)) {
                return false;
            }

            return true;
        }));

        $counts = $this->computeCounts($filtered);

        usort($filtered, static function (array $a, array $b) use ($sortKey, $sortDir) {
            $av = Str::lower((string) ($sortKey === 'method' ? $a['method'] : ($a[$sortKey] ?? '')));
            $bv = Str::lower((string) ($sortKey === 'method' ? $b['method'] : ($b[$sortKey] ?? '')));

            $result = $av <=> $bv;

            return $sortDir === 'desc' ? -$result : $result;
        });

        $filteredTotal = count($filtered);
        $totalPages = max(1, (int) ceil($filteredTotal / $perPage));
        $page = min($page, $totalPages);

        $pageItems = array_slice($filtered, ($page - 1) * $perPage, $perPage);

        return [
            'data' => array_map(static function (array $route) {
                unset($route['search']);

                return $route;
            }, $pageItems),
            'meta' => [
                'total' => count($routes),
                'filteredTotal' => $filteredTotal,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => $totalPages,
                'counts' => $counts,
            ],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $routes
     * @return array<string, int>
     */
    private function computeCounts(array $routes): array
    {
        $counts = [
            'total' => count($routes),
            'GET' => 0,
            'POST' => 0,
            'PUTPATCH' => 0,
            'DELETE' => 0,
            'named' => 0,
            'middleware' => 0,
            'controller' => 0,
        ];

        foreach ($routes as $route) {
            if (in_array('GET', $route['methods'], true)) {
                $counts['GET']++;
            }
            if (in_array('PUT', $route['methods'], true) || in_array('PATCH', $route['methods'], true)) {
                $counts['PUTPATCH']++;
            }
            if (in_array('POST', $route['methods'], true)) {
                $counts['POST']++;
            }
            if (in_array('DELETE', $route['methods'], true)) {
                $counts['DELETE']++;
            }
            if ($route['name'] !== null) {
                $counts['named']++;
            }
            if ($route['middleware'] !== []) {
                $counts['middleware']++;
            }
            if (!$route['isClosure']) {
                $counts['controller']++;
            }
        }

        return $counts;
    }

    private function transform(Route $route): array
    {
        // Laravel always adds HEAD alongside GET; drop it from the display list
        // unless HEAD is genuinely the only method the route responds to.
        $methods = array_values(array_diff($route->methods(), ['HEAD'])) ?: $route->methods();

        $action = $this->resolveAction($route);
        $middleware = array_values($route->gatherMiddleware());
        $name = $route->getName();

        $fields = [
            'uri' => $route->uri(),
            'name' => $name,
            'methods' => $methods,
            'method' => $methods[0] ?? 'GET',
            'action' => $action['action'],
            'controller' => $action['controller'],
            'controllerMethod' => $action['method'],
            'isClosure' => $action['isClosure'],
            'middleware' => $middleware,
            'domain' => $route->domain(),
            'assignment' => $this->resolveAssignment($action['controller'], $action['isClosure']),
        ];

        // Pre-flatten every searchable attribute into one lowercase string once,
        // server-side, so the client filters a single field per row per keystroke
        // instead of re-scanning six fields for every route on every search.
        $fields['search'] = Str::lower(implode(' ', [
            $fields['assignment'],
            implode(' ', $fields['methods']),
            (string) $fields['name'],
            $fields['action'],
            $fields['uri'],
            implode(' ', $fields['middleware']),
            (string) $fields['controller'],
        ]));

        return $fields;
    }

    /**
     * @return array{action: string, controller: ?string, method: ?string, isClosure: bool}
     */
    private function resolveAction(Route $route): array
    {
        $action = $route->getAction();
        $uses = $action['controller'] ?? null;

        if ($uses === null || ($action['uses'] ?? null) instanceof Closure) {
            return ['action' => 'Closure', 'controller' => null, 'method' => null, 'isClosure' => true];
        }

        // Normalize "Controller@method", "Class::method", and bare invokable-controller forms.
        if (str_contains($uses, '@')) {
            [$controller, $method] = explode('@', $uses, 2);
        } elseif (str_contains($uses, '::')) {
            [$controller, $method] = explode('::', $uses, 2);
        } else {
            $controller = $uses;
            $method = '__invoke';
        }

        return ['action' => $uses, 'controller' => $controller, 'method' => $method, 'isClosure' => false];
    }

    private function resolveAssignment(?string $controller, bool $isClosure): string
    {
        if ($isClosure || $controller === null) {
            return 'Closure';
        }

        foreach (config('route-list.assignment_map', []) as $pattern => $label) {
            if (Str::is($pattern, $controller)) {
                return $label;
            }
        }

        // Feature-folder convention used across the app: App\Web\<Feature>\... and
        // App\Domain\<Feature>\... — use that segment as the assignment group.
        if (preg_match('/^App\\\\(?:Web|Domain)\\\\([^\\\\]+)\\\\/', $controller, $matches)) {
            return $this->humanizeSegment($matches[1]);
        }

        if (str_starts_with($controller, 'App\\Http\\Controllers\\')) {
            return 'Core';
        }

        return 'Other';
    }

    /**
     * Splits a StudlyCase namespace segment into words, without breaking up
     * all-caps acronyms — many feature folders here are named like "SNP", "CA",
     * "MIS", "IARegistration" or "BNPRegistration", and Str::headline() would
     * otherwise shatter those into single letters ("S N P", "I A Registration").
     */
    private function humanizeSegment(string $segment): string
    {
        $words = preg_split('/(?<=[a-z0-9])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', $segment);

        return implode(' ', $words);
    }
}
