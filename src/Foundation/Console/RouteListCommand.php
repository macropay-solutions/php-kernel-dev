<?php

namespace MacropaySolutions\KernelDev\Foundation\Console;

use MacropaySolutions\Framework\Routing\Router;
use MacropaySolutions\Kernel\Console\Command;
use MacropaySolutions\Kernel\Support\Arr;
use MacropaySolutions\Kernel\Support\Collection;
use MacropaySolutions\Kernel\Support\Str;
use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Terminal;

#[AsCommand(name: 'route:list')]
class RouteListCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'route:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all registered routes';

    /**
     * The router instance.
     *
     * @var \MacropaySolutions\Framework\Routing\Router
     */
    protected $router;

    /**
     * The table headers for the command.
     *
     * @var string[]
     */
    protected $headers = ['Domain', 'Method', 'URI', 'Name', 'Action', 'Middleware'];

    /**
     * The terminal width resolver callback.
     *
     * @var callable|null
     */
    protected static $terminalWidthResolver;

    /**
     * The verb colors for the command.
     *
     * @var array
     */
    protected $verbColors = [
        'ANY' => 'red',
        'GET' => 'blue',
        'HEAD' => '#6C7280',
        'OPTIONS' => '#6C7280',
        'POST' => 'yellow',
        'PUT' => 'yellow',
        'PATCH' => 'yellow',
        'DELETE' => 'red',
        'QUERY' => 'blue',
    ];

    /**
     * Create a new route command instance.
     *
     * @param \MacropaySolutions\Framework\Routing\Router $router
     * @return void
     */
    public function __construct(Router $router)
    {
        parent::__construct();

        $this->router = $router;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['json', null, InputOption::VALUE_NONE, 'Output the route list as JSON'],
            ['method', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by method'],
            ['name', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by name'],
            ['domain', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by domain'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'Only show routes matching the given path pattern'],
            [
                'except-path',
                null,
                InputOption::VALUE_OPTIONAL,
                'Do not display the routes matching the given path pattern',
            ],
            ['reverse', 'r', InputOption::VALUE_NONE, 'Reverse the ordering of the routes'],
            [
                'sort',
                null,
                InputOption::VALUE_OPTIONAL,
                'The column (domain, method, uri, name, action, middleware) to sort by',
                'uri',
            ],
            ['except-vendor', null, InputOption::VALUE_NONE, 'Do not display routes defined by vendor packages'],
            ['only-vendor', null, InputOption::VALUE_NONE, 'Only display routes defined by vendor packages'],
        ];
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if ([] === $this->router->getAllRoutes()) {
            $this->error("Your application doesn't have any routes.");

            return;
        }

        if ([] === ($routes = $this->getRoutes())) {
            $this->error("Your application doesn't have any routes matching the given criteria.");

            return;
        }

        $this->displayRoutes($routes);
    }

    /**
     * Compile the routes into a displayable format.
     *
     * @return array
     */
    protected function getRoutes()
    {
        $routes = \collect($this->router->getAllRoutes())->map(function ($route) {
            return $this->getRouteInformation($route);
        })->filter()->all();

        $routes = ($sort = $this->option('sort')) !== null ?
            $this->sortRoutes($sort, $routes) :
            $this->sortRoutes('uri', $routes);

        if ($this->option('reverse')) {
            $routes = array_reverse($routes);
        }

        return $this->pluckColumns($routes);
    }

    /**
     * Get the route information for a given route payload.
     */
    protected function getRouteInformation(array $route): ?array
    {
        $action = $route['action'] ?? [];
        $uses = $action['uses'] ?? '';

        $actionName = match (true) {
            \is_string($uses) => \ltrim($uses, '\\'),
            \is_array($uses)  => (\is_object($uses[0]) ? $uses[0]::class : $uses[0]) . '@' . ($uses[1] ?? ''),
            default => '',
        };

        $domain = $action['domain'] ?? '';

        if (\is_array($domain)) {
            $domain = implode('|', $domain);
        }

        return $this->filterRoute([
            'domain' => $domain,
            'method' => $route['method'] ?? '',
            'uri' => $route['uri'] ?? '',
            'name' => $action['as'] ?? '',
            'action' => $actionName,
            'middleware' => $this->getMiddleware($route),
            'vendor' => $this->isVendorRoute($route),
        ]);
    }

    /**
     * Sort the routes by a given element.
     */
    protected function sortRoutes(string $sort, array $routes): array
    {
        return Arr::sort($routes, function ($route) use ($sort) {
            return $route[$sort] ?? null;
        });
    }

    /**
     * Remove unnecessary columns from the routes.
     */
    protected function pluckColumns(array $routes): array
    {
        return array_map(function ($route) {
            return Arr::only($route, $this->getColumns());
        }, $routes);
    }

    /**
     * Display the route information on the console.
     *
     * @param array $routes
     * @return void
     */
    protected function displayRoutes(array $routes)
    {
        $routes = collect($routes);

        $this->output->writeln(
            $this->option('json') ? $this->asJson($routes) : $this->forCli($routes)
        );
    }

    /**
     * Get the middleware for the route.
     */
    protected function getMiddleware(array $route): string
    {
        $middleware = $route['action']['middleware'] ?? [];

        if (\is_string($middleware)) {
            $middleware = explode('|', $middleware);
        }

        return \collect((array)$middleware)->implode("\n");
    }

    /**
     * Determine if the route has been defined outside of the application.
     */
    protected function isVendorRoute(array $route): bool
    {
        $uses = $route['action']['uses'] ?? null;

        $controllerClass = match (true) {
            \is_string($uses) => \explode('@', $uses)[0],
            \is_array($uses)  => \is_object($uses[0]) ? $uses[0]::class : $uses[0],
            default           => null,
        };

        if (null === $controllerClass || $this->isFrameworkController($controllerClass) || !\class_exists($controllerClass)) {
            return false;
        }

        $path = (new ReflectionClass($controllerClass))->getFileName();

        return $path && \str_starts_with($path, \base_path('vendor'));
    }

    /**
     * Determine if the route uses a framework controller.
     */
    protected function isFrameworkController(string $controllerClass): bool
    {
        return \ltrim($controllerClass, '\\') === 'MacropaySolutions\Framework\Routing\Controller';
    }

    /**
     * Filter the route by URI and / or name.
     */
    protected function filterRoute(array $route): ?array
    {
        if (
            ($this->option('name') && !Str::contains((string)$route['name'], $this->option('name'))) ||
            ($this->option('path') && !Str::contains($route['uri'], $this->option('path'))) ||
            ($this->option('method') && !Str::contains($route['method'], strtoupper($this->option('method')))) ||
            ($this->option('domain') && !Str::contains((string)$route['domain'], $this->option('domain'))) ||
            ($this->option('except-vendor') && $route['vendor']) ||
            ($this->option('only-vendor') && !$route['vendor'])
        ) {
            return null;
        }

        if ($this->option('except-path')) {
            foreach (\explode(',', $this->option('except-path')) as $path) {
                if (\str_contains($route['uri'], $path)) {
                    return null;
                }
            }
        }

        return $route;
    }

    /**
     * Get the table headers for the visible columns.
     */
    protected function getHeaders(): array
    {
        return Arr::only($this->headers, \array_keys($this->getColumns()));
    }

    /**
     * Get the column names to show (lowercase table headers).
     */
    protected function getColumns():array
    {
        return \array_map('strtolower', $this->headers);
    }

    /**
     * Convert the given routes to JSON.
     */
    protected function asJson(Collection $routes): string
    {
        return $routes
            ->map(function ($route) {
                $route['middleware'] = empty($route['middleware']) ? [] : explode("\n", $route['middleware']);

                return $route;
            })
            ->values()
            ->toJson();
    }

    /**
     * Convert the given routes to regular CLI output.
     */
    protected function forCli(Collection $routes): array
    {
        $routes = $routes->map(
            fn($route) => array_merge($route, [
                'action' => $this->formatActionForCli($route),
                'method' => $route['method'],
                'uri' => $route['domain'] ? ($route['domain'] . '/' . ltrim($route['uri'], '/')) : $route['uri'],
            ]),
        );

        $maxMethod = \mb_strlen((string)$routes->max('method'));

        $terminalWidth = $this->getTerminalWidth();

        $routeCount = $this->determineRouteCountOutput($routes, $terminalWidth);

        return $routes->map(function ($route) use ($maxMethod, $terminalWidth) {
            [
                'action' => $action,
                'domain' => $domain,
                'method' => $method,
                'middleware' => $middleware,
                'uri' => $uri,
            ] = $route;

            $middleware = Str::of($middleware)->explode("\n")->filter()->whenNotEmpty(
                fn($collection) => $collection->map(
                    fn($middleware) => sprintf('         %s⇂ %s', str_repeat(' ', $maxMethod), $middleware)
                )
            )->implode("\n");

            $spaces = str_repeat(' ', max($maxMethod + 6 - mb_strlen($method), 0));

            $dots = str_repeat(
                '.',
                max(
                    $terminalWidth - mb_strlen($method . $spaces . $uri . $action) - 6 - ($action ? 1 : 0),
                    0
                )
            );

            $dots = empty($dots) ? $dots : " $dots";

            if (
                $action
                && !$this->output->isVerbose()
                && mb_strlen($method . $spaces . $uri . $action . $dots) > ($terminalWidth - 6)
            ) {
                $action = substr($action, 0, $terminalWidth - 7 - mb_strlen($method . $spaces . $uri . $dots)) . '…';
            }

            $method = Str::of($method)->explode('|')->map(
                fn($method) => sprintf('<fg=%s>%s</>', $this->verbColors[$method] ?? 'default', $method),
            )->implode('<fg=#6C7280>|</>');

            return [
                sprintf(
                    '  <fg=white;options=bold>%s</> %s<fg=white>%s</><fg=#6C7280>%s %s</>',
                    $method,
                    $spaces,
                    preg_replace('#({[^}]+})#', '<fg=yellow>$1</>', $uri),
                    $dots,
                    str_replace('   ', ' › ', $action ?? ''),
                ),
                $this->output->isVerbose() && !empty($middleware) ? "<fg=#6C7280>$middleware</>" : null,
            ];
        })
            ->flatten()
            ->filter()
            ->prepend('')
            ->push('')->push($routeCount)->push('')
            ->toArray();
    }

    /**
     * Get the formatted action for display on the CLI.
     *
     * @param array $route
     * @return string
     */
    protected function formatActionForCli(array $route)
    {
        ['action' => $action, 'name' => $name] = $route;

        $name = $name ? "$name   " : null;

        $rootControllerNamespace = $this->app->getNamespace() . 'Http\\Controllers';

        if (str_starts_with($action, $rootControllerNamespace)) {
            return $name . substr($action, mb_strlen($rootControllerNamespace) + 1);
        }

        $actionClass = explode('@', $action)[0];

        if (
            \class_exists($actionClass)
            && str_starts_with((new ReflectionClass($actionClass))->getFileName(), base_path('vendor'))
        ) {
            $actionCollection = collect(explode('\\', $action));

            return $name . $actionCollection->take(2)->implode('\\') . '   ' . $actionCollection->last();
        }

        return $name . $action;
    }

    /**
     * Determine and return the output for displaying the number of routes in the CLI output.
     */
    protected function determineRouteCountOutput(Collection $routes, int $terminalWidth): string
    {
        $routeCountText = 'Showing [' . $routes->count() . '] routes';

        $offset = \max($terminalWidth - \mb_strlen($routeCountText) - 2, 0);

        $spaces = str_repeat(' ', $offset);

        return $spaces . '<fg=blue;options=bold>Showing [' . $routes->count() . '] routes</>';
    }

    /**
     * Get the terminal width.
     */
    public static function getTerminalWidth(): int
    {
        return null === static::$terminalWidthResolver
            ? (new Terminal())->getWidth()
            : (static::$terminalWidthResolver)();
    }

    /**
     * Set a callback that should be used when resolving the terminal width.
     */
    public static function resolveTerminalWidthUsing(?callable $resolver): void
    {
        static::$terminalWidthResolver = $resolver;
    }
}
