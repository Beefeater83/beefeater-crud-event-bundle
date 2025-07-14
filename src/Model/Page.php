<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Model;

class Page
{
    private int $page;
    private int $pageSize;

    public function __construct(int $page = 1, int $pageSize = 25)
    {
        $this->page = max(1, $page);
        $this->pageSize = max(1, $pageSize);
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->pageSize;
    }

    public function getLimit(): int
    {
        return $this->pageSize;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function setPage(int $page): static
    {
        $this->page = max(1, $page);

        return $this;
    }

    public function setPageSize(int $pageSize): static
    {
        $this->pageSize = max(1, $pageSize);

        return $this;
    }
}
