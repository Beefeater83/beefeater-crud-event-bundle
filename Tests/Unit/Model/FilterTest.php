<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Tests\Unit\Model;

use Beefeater\CrudEventBundle\Model\Filter;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testValidNumericValues()
    {
        $queryString = [
            'filter' => [
                'weight' => [
                    'eq' => [
                        0 => '65.00',
                        1 => '75.00'
                    ]
                ]
            ]
        ];

        $filter = new Filter($queryString['filter']);
        $this->assertSame(['weight' => ['eq' => ['65.00', '75.00']]], $filter->getCriteria());
    }

    public function testValidStringValues(): void
    {
        $queryString = [
            'filter' => [
                'name' => [
                    'eq' => [
                        0 => 'Vlad',
                        1 => 'Anton'
                    ]
                ]
            ]
        ];

        $filter = new Filter($queryString['filter']);
        $this->assertSame(['name' => ['eq' => ['Vlad', 'Anton']]], $filter->getCriteria());
    }

    public function testValidBoolValue(): void
    {
        $queryString = [
            'filter' => [
                'competeInKata' => [
                    'eq' => 'true'
                ],
                'another' => [
                    'eq' => 'false'
                ]
            ]
        ];

        $filter = new Filter($queryString['filter']);
        $this->assertSame(
            ['competeInKata' => ['eq' => true],
            'another' => ['eq' => false]
            ],
            $filter->getCriteria()
        );
    }

    public function testValidMixedValues(): void
    {
        $queryString = [
            'filter' => [
                'weight' => [
                    'eq' => [
                        0 => '65.00',
                        1 => '75.00'
                    ]
                ],
                'name' => [
                    'eq' => [
                        0 => 'Vlad',
                        1 => 'Anton'
                    ]
                ],
                'competeInKata' => [
                    'eq' => 'true'
                ]
            ]
        ];

        $filter = new Filter($queryString['filter']);

        $expected = [
            'weight' => ['eq' => ['65.00', '75.00']],
            'name' => ['eq' => ['Vlad', 'Anton']],
            'competeInKata' => ['eq' => true]
        ];

        $this->assertSame($expected, $filter->getCriteria());
    }

    public function testValidNoneValue(): void
    {
        $queryString = [
            'filter' => [
                'competeInKata' => [
                    'eq' => 'none'
                ]
            ]
        ];

        $filter = new Filter($queryString['filter']);
        $this->assertSame(['competeInKata' => ['eq' => null]], $filter->getCriteria());
    }

    public function testValidNoneInArrayValues(): void
    {
        $queryString = [
            'filter' => [
                'name' => [
                    'eq' => [
                        0 => 'Vlad',
                        1 => 'none'
                    ]
                ]
            ]
        ];

        $filter = new Filter($queryString['filter']);
        $this->assertSame(['name' => ['eq' => ['Vlad', null]]], $filter->getCriteria());
    }

    public function testAllBoolValues()
    {
        $queryString = [
            'filter' => [
                'weight' => [
                    'eq' => [
                        0 => 'true',
                        1 => 'false'
                    ]
                ]
            ]
        ];

        $filter = new Filter($queryString['filter']);
        $this->assertSame([], $filter->getCriteria());
    }
}
