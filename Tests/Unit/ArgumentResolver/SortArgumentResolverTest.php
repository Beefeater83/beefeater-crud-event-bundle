<?php

namespace Beefeater\CrudEventBundle\Tests\Unit\ArgumentResolver;

use Beefeater\CrudEventBundle\Model\Sort;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Beefeater\CrudEventBundle\ArgumentResolver\SortArgumentResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class SortArgumentResolverTest extends TestCase
{
    public function testResolvesSortArgument(): void
    {
        $request = new Request(['sort' => 'name,-date']);
        $argument = new ArgumentMetadata('sort', Sort::class, false, false, null);
        $argumentResolver = new SortArgumentResolver();

        $result = $argumentResolver->resolve($request, $argument);
        foreach ($result as $item) {
            $this->assertInstanceOf(Sort::class, $item);
            $this->assertEquals(['name' => 'ASC', 'date' => 'DESC'], $item->getOrderBy());
        }
    }
}
