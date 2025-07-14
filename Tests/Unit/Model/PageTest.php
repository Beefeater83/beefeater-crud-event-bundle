<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Tests\Unit\Model;

use Beefeater\CrudEventBundle\Model\Page;
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $page = new Page();

        $this->assertSame(1, $page->getPage());
        $this->assertSame(25, $page->getPageSize());
        $this->assertSame(0, $page->getOffset());
        $this->assertSame(25, $page->getLimit());
    }

    public function testCustomValues(): void
    {
        $page = new Page(2, 50);

        $this->assertSame(2, $page->getPage());
        $this->assertSame(50, $page->getPageSize());
        $this->assertSame(50, $page->getOffset());
        $this->assertSame(50, $page->getLimit());
    }

    public function testNegativeValues(): void
    {
        $page = new Page(-1, -10);

        $this->assertSame(1, $page->getPage());
        $this->assertSame(1, $page->getPageSize());
    }

    public function testSetters(): void
    {
        $page = new Page();
        $page->setPage(5)->setPageSize(100);

        $this->assertSame(5, $page->getPage());
        $this->assertSame(100, $page->getPageSize());
        $this->assertSame(400, $page->getOffset());
    }
}
