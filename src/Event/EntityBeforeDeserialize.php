<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class EntityBeforeDeserialize extends Event
{
    private ?object $model;
    private string $className;

    public function getModel(): ?object
    {
        return $this->model;
    }

    public function setModel(?object $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function setClassName(string $className): static
    {
        $this->className = $className;

        return $this;
    }
}
