<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Model;

class Sort
{
    private array $orderBy;

    public function __construct(string $sort = '')
    {
        $this->orderBy = $this->parseSort($sort);
    }

    private function parseSort(string $sort): array
    {
        if (empty($sort)) {
            return [];
        }

        $orderBy = [];

        $fields = explode(',', $sort);

        foreach ($fields as $field) {
            $field = trim($field);
            $direction = 'ASC';
            if (str_starts_with($field, '-')) {
                $direction = 'DESC';
                $field = substr($field, 1);
            } elseif (str_starts_with($field, '+')) {
                $field = substr($field, 1);
            }
            $orderBy[$field] = $direction;
        }

        return $orderBy;
    }

    public function getOrderBy(): array
    {
        return $this->orderBy;
    }
}
