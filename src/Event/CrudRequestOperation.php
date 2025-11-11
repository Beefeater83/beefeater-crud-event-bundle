<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Event;

use Symfony\Component\HttpFoundation\Request;

abstract class CrudRequestOperation extends CrudOperation
{
    private Request $request;

    public function __construct(
        object $entity,
        string $operation,
        Request $request,
        ?string $version = null,
        array $params = []
    ) {
        parent::__construct($entity, $operation, $params, $version);
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): static
    {
        $this->request = $request;
        return $this;
    }
}
