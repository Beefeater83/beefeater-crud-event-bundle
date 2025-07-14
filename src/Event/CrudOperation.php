<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CrudOperation extends Event
{
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const PATCH  = 'patch';
    public const DELETE = 'delete';

    private object $entity;
    private string $operation;
    private array $params;
    private string $version;

    public function __construct(
        object $entity,
        string $operation,
        array $params = [],
        string $version = 'v1'
    ) {
        $this->entity = $entity;
        $this->operation = $operation;
        $this->params = $params;
        $this->version = $version;
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getVersion(): string
    {
        return $this->version;
    }
}
