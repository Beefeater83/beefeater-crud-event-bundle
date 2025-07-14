<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Routing;

use Beefeater\CrudEventBundle\Controller\Api\CrudEventController;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;

class CrudRouteLoader extends Loader
{
    private ParameterBagInterface $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }
    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        $resource = $this->params->resolveValue($resource);
        dump($resource);
        $routes = new RouteCollection();
        $config = Yaml::parseFile($resource);
        $version = $config['version'];

        foreach ($config['resources'] as $name => $data) {
            foreach ($data['operations'] as $op) {
                $routes->add(
                    "api_{$version}_{$name}_{$op}",
                    $this->buildRoute($name, $data['entity'], $data['path'], $op, $version)
                );
            }
        }

        return $routes;
    }

    private function buildRoute(string $name, string $entity, string $basePath, string $op, string $version): Route
    {
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
            ($version === 'v1' ? '/api' : "/api/$version") . $path,
            [
                '_controller' => CrudEventController::class . "::{$controllerMethodName}",
                '_resource' => $name,
                '_entity' => $entity,
                '_operation' => $op,
                '_version' => $version,
            ],
            [],
            [],
            '',
            [],
            $methods
        );
    }

    public function supports($resource, ?string $type = null): bool
    {
        return $type === 'crud_routes';
    }
}
