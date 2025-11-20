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

    public function testInEqAndNinOperatorsTogether(): void
    {
        $queryString = [
            'filter' => [
                'weight' => [
                    'in' => ['65.00', '75.00']
                ],
                'name' => [
                    'eq' => 'Vlad'
                ],
                'age' => [
                    'eq' => ['20', '50']
                ],
                'status' => [
                    'nin' => ['new', 'blocked']
                ]
            ]
        ];

        $filter = new Filter($queryString['filter']);

        $expected = [
            'weight' => ['in' => ['65.00', '75.00']],
            'name'   => ['eq' => 'Vlad'],
            'age' => ['eq' => ['20', '50']],
            'status' => ['nin' => ['new', 'blocked']]
        ];

        $this->assertSame($expected, $filter->getCriteria());
    }

    public function testNqOperator(): void
    {
        $queryString1 = [
            'filter' => [
                'status' => ['neq' => 'active']
            ]
        ];
        $filter1 = new Filter($queryString1['filter']);
        $expected1 = ['status' => ['neq' => 'active']];
        $this->assertSame($expected1, $filter1->getCriteria());

        $queryString2 = [
            'filter' => [
                'status' => ['neq' => null]
            ]
        ];
        $filter2 = new Filter($queryString2['filter']);
        $expected2 = ['status' => ['neq' => null]];
        $this->assertSame($expected2, $filter2->getCriteria());
    }

    public function testInAndNinWithNull(): void
    {
        $queryString = [
            'filter' => [
                'status' => [
                    'in' => ['new', null],
                    'nin' => ['blocked', null]
                ]
            ]
        ];

        $filter = new Filter($queryString['filter']);

        $expected = [
            'status' => [
                'in' => ['new', null],
                'nin' => ['blocked', null]
            ]
        ];

        $this->assertSame($expected, $filter->getCriteria());
    }

    public function testEqWithNull(): void
    {
        $queryString = [
            'filter' => [
                'status' => [
                    'eq' => 'none'
                ]
            ]
        ];

        $filter = new Filter($queryString['filter']);

        $expected = [
            'status' => ['eq' => null]
        ];

        $this->assertSame($expected, $filter->getCriteria());
    }
}
