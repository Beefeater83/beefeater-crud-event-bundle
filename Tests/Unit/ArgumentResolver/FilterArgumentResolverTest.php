<?php

namespace Beefeater\CrudEventBundle\Tests\Unit\ArgumentResolver;

use Beefeater\CrudEventBundle\ArgumentResolver\FilterArgumentResolver;
use Beefeater\CrudEventBundle\ArgumentResolver\PageArgumentResolver;
use Beefeater\CrudEventBundle\Model\Filter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class FilterArgumentResolverTest extends TestCase
{
    public function testResolvesFilterArgument(): void
    {
        $queryString = [
            'filter' => [
                'weight' => [
                    'eq' => [
                        0 => '65.00',
                        1 => '75.00'
                    ]
                ],
                'age' => [
                    'gte' => [
                        0 => '10',
                    ]
                ]
            ]
        ];
        $request = new Request($queryString);
        $argument = new ArgumentMetadata('filter', Filter::class, false, false, null);
        $logger = $this->createMock(LoggerInterface::class);
        $argumentResolver = new FilterArgumentResolver($logger);

        $result = $argumentResolver->resolve($request, $argument);
        foreach ($result as $filter) {
            $this->assertInstanceOf(Filter::class, $filter);

            $expectedCriteria = [
                'weight' => [
                    'eq' => [
                        '65.00',
                        '75.00'
                    ]
                ],
                'age' => [
                    'gte' => [10]
                ]
            ];

            $this->assertEquals($expectedCriteria, $filter->getCriteria());
        }
    }
}
