<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Tests\Unit\Model;

use Beefeater\CrudEventBundle\Model\Sort;
use PHPUnit\Framework\TestCase;

class SortTest extends TestCase
{
    public function testSingleFieldAscending()
    {
        $sort = new Sort('name');
        $this->assertSame(['name' => 'ASC'], $sort->getOrderBy());
    }

    public function testSingleFieldWithPlus()
    {
        $sort = new Sort('+name');
        $this->assertSame(['name' => 'ASC'], $sort->getOrderBy());
    }

    public function testSingleFieldDescending()
    {
        $sort = new Sort('-name');
        $this->assertSame(['name' => 'DESC'], $sort->getOrderBy());
    }

    public function testMultipleFields()
    {
        $sort = new Sort('name,-email');
        $this->assertSame([
            'name' => 'ASC',
            'email' => 'DESC'
        ], $sort->getOrderBy());
    }

    public function testSortingWithSpaces()
    {
        $sort = new Sort(' name , +email ');
        $this->assertSame([
            'name' => 'ASC',
            'email' => 'ASC'
        ], $sort->getOrderBy());
    }
}
