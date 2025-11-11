<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class EntityBeforeDeserialize extends Event
{
    private ?object $model;
    private string $className;
    private Request $request;

    public function __construct(
        Request $request,
        ?object $model = null,
        string $className
    ) {
        $this->request = $request;
        $this->model = $model;
        $this->className = $className;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

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
