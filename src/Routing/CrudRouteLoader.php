<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Routing;

use Beefeater\CrudEventBundle\Controller\Api\CrudEventController;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;

class CrudRouteLoader extends Loader
{
    private ParameterBagInterface $params;
    private LoggerInterface $logger;

    public function __construct(ParameterBagInterface $params, LoggerInterface $logger)
    {
        $this->params = $params;
        $this->logger = $logger;
    }
    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        $resource = $this->params->resolveValue($resource);
        $routes = new RouteCollection();
        $config = Yaml::parseFile($resource);
        $version = $config['version'] ?? null;
        if (!isset($config['version'])) {
            $this->logger->warning("No version defined in route config file: {$resource}");
        }

        $securityRoles = $config['security'] ?? null;
        $securityRolesByEndpoints = [];
        if (isset($securityRoles)) {
            $securityRolesByEndpoints = $this->securityRolesForOp($securityRoles);
            $this->logger->warning("Security defined in route config file: {$resource}");
        }

        foreach ($config['resources'] as $name => $data) {
            foreach ($data['operations'] as $op) {
                if (!in_array($op, ['C', 'R', 'U', 'D', 'L', 'P'], true)) {
                    $this->logger->error("Unsupported operation in resource '{$name}': '{$op}'");
                }
                $routeName = "api_" . ($version ? "{$version}_" : "") . "{$name}_{$op}";
                $routes->add(
                    $routeName,
                    $this->buildRoute($name, $data['entity'], $data['path'], $op, $version, $securityRolesByEndpoints)
                );

                $this->logger->info("Adding route: {$routeName}");
            }
        }

        return $routes;
    }

    private function buildRoute(
        string $name,
        string $entity,
        string $basePath,
        string $op,
        ?string $version,
        ?array $securityRolesByEndpoints
    ): Route {
        $methods = match ($op) {
            'C' => ['POST'],
            'R' => ['GET'],
            'U' => ['PUT'],
            'D' => ['DELETE'],
            'L' => ['GET'],
            'P' => ['PATCH'],
            default => throw new \InvalidArgumentException("Unsupported operation: $op"),
        };

        $path = match ($op) {
            'C', 'L' => $basePath,
            'R', 'U', 'D', 'P' => $basePath . '/{id}',
            default => throw new \InvalidArgumentException("Unsupported operation for path: $op"),
        };

        $controllerMethodName = match ($op) {
            'C' => 'create',
            'R' => 'read',
            'U' => 'update',
            'D' => 'delete',
            'L' => 'list',
            'P' => 'patch',
            default => throw new \InvalidArgumentException("Unsupported controller method for: $op"),
        };

        return new Route(
            '/api' . ($version ? "/$version" : '') . $path,
            [
                '_controller' => CrudEventController::class . "::{$controllerMethodName}",
                '_resource' => $name,
                '_entity' => $entity,
                '_operation' => $op,
                '_version' => $version,
                '_security' => $securityRolesByEndpoints[$controllerMethodName] ?? [],
            ],
            [],
            [],
            '',
            [],
            $methods
        );
    }

    public function securityRolesForOp(array $configSecurity): array
    {
        $securityRolesByEndpoints = [];
        foreach ($configSecurity as $operation => $rolesArray) {
            if (!in_array($operation, ['C','R','U','D','L','P'], true)) {
                $this->logger->error("Security operation: '{$operation}' is not allowed");
                throw new \InvalidArgumentException("Invalid security operation: $operation");
            }

            if (!is_array($rolesArray)) {
                $this->logger->error("Security roles array must be array");
                throw new \InvalidArgumentException("Security roles for $operation must be array");
            }
            switch ($operation) {
                case 'C':
                    $securityRolesByEndpoints['create'] = $rolesArray;
                    break;
                case 'R':
                    $securityRolesByEndpoints['read'] = $rolesArray;
                    break;
                case 'U':
                    $securityRolesByEndpoints['update'] = $rolesArray;
                    break;
                case 'D':
                    $securityRolesByEndpoints['delete'] = $rolesArray;
                    break;
                case 'L':
                    $securityRolesByEndpoints['list'] = $rolesArray;
                    break;
                case 'P':
                    $securityRolesByEndpoints['patch'] = $rolesArray;
                    break;
            }
        }
        return $securityRolesByEndpoints;
    }

    public function supports($resource, ?string $type = null): bool
    {
        return $type === 'crud_routes';
    }
}
