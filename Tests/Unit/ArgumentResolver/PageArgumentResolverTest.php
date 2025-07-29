<?php

namespace Beefeater\CrudEventBundle\Tests\Unit\ArgumentResolver;

use Beefeater\CrudEventBundle\ArgumentResolver\PageArgumentResolver;
use Beefeater\CrudEventBundle\ArgumentResolver\SortArgumentResolver;
use Beefeater\CrudEventBundle\Model\Page;
use Beefeater\CrudEventBundle\Model\Sort;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class PageArgumentResolverTest extends TestCase
{
    public function testResolvesPageArgument(): void
    {
        $request = new Request(['page' => 3]);
        $argument = new ArgumentMetadata('page', Page::class, false, false, null);
        $argumentResolver = new PageArgumentResolver();

        $result = $argumentResolver->resolve($request, $argument);
        foreach ($result as $item) {
            $this->assertInstanceOf(Page::class, $item);
            $this->assertEquals(3, $item->getPage());
            $this->assertEquals(25, $item->getPageSize());
        }
    }
}
