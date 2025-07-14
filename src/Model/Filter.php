<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Model;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Filter
{
    public const FILTER_VALUE_NULL = 'none';
    public const FILTER_VALUE_TRUE = 'true';
    public const FILTER_VALUE_FALSE = 'false';

    private array $criteria;

    public function __construct(array $queryString = [])
    {
        $this->parseFilters($queryString);
    }

    private function parseFilters(array $queryString): void
    {
        $this->criteria = [];

        foreach ($queryString as $field => $filters) {
            if (!is_array($filters)) {
                $this->criteria[$field]['eq'] = $this->normalizeValue($filters);
                continue;
            }

            foreach ($filters as $operator => $value) {
                if (!is_array($value)) {
                    $value = $this->normalizeValue($value);

                    $this->criteria[$field][$operator] = $value;
                    continue;
                }

                if (is_numeric(key($value))) {
                    foreach ($value as &$subValue) {
                        $subValue = $this->normalizeValue($subValue);
                    }

                    if ($this->allBoolValues($value)) {
                        continue;
                    }

                    $this->criteria[$field][$operator] = $value;

                    continue;
                }
                throw new BadRequestHttpException("Invalid structure key: {$field}, operator: {$operator}");
            }
        }
    }

    public function getCriteria(): array
    {
        return $this->criteria;
    }

    public function setCriteria(string $key, $value): void
    {
        $this->criteria[$key] = $value;
    }

    private function normalizeValue($value)
    {
        if ($this->isNullValue($value)) {
            return null;
        }
        if ($this->isTrueValue($value)) {
            return true;
        }
        if ($this->isFalseValue($value)) {
            return false;
        }
        return $value;
    }

    protected function isNullValue($value): bool
    {
        return $value === self::FILTER_VALUE_NULL;
    }

    protected function isTrueValue($value): bool
    {
        return $value === self::FILTER_VALUE_TRUE;
    }

    protected function isFalseValue($value): bool
    {
        return $value === self::FILTER_VALUE_FALSE;
    }

    protected function allBoolValues(array $values): bool
    {
        if (
            (count($values) === 2)
            && in_array(true, $values, true)
            && in_array(false, $values, true)
        ) {
            return true;
        }

        return false;
    }
}
