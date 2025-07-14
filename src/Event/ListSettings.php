<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class ListSettings extends Event
{
    public function __construct(
        private Request $request,
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
