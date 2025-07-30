<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Tests\Unit\Routing;

use Beefeater\CrudEventBundle\Routing\CrudRouteLoader;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouteCollection;

class CrudRouteLoaderTest extends TestCase
{
    private string $yamlPath;
    protected function setUp(): void
    {
        $this->yamlPath = __DIR__ . '/crud_routes_test.yaml';
    }

    public function testLoadReturnsRouteCollection(): void
    {
        $params = $this->createMock(ParameterBagInterface::class);
        $params->method('resolveValue')->willReturn($this->yamlPath);

        $logger = $this->createMock(LoggerInterface::class);

        $loader = new CrudRouteLoader($params, $logger);

        $routes = $loader->load($this->yamlPath, 'crud_routes');

        $this->assertInstanceOf(RouteCollection::class, $routes);

        $this->assertCount(12, $routes);

        $routeName = 'api_v1_tournaments_C';

        $route = $routes->get($routeName);
        $this->assertSame('/api/v1/tournaments', $route->getPath());
        $this->assertSame(['POST'], $route->getMethods());

        $controller = $route->getDefault('_controller');
        $this->assertStringContainsString('CrudEventController', $controller);
        $this->assertStringContainsString('create', $controller);
    }
}
