<?php

namespace Beefeater\CrudEventBundle\Event;

use Beefeater\CrudEventBundle\Model\Filter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class FilterBuildEvent extends Event
{
    public function __construct(
        private Request $request,
        private Filter $filter
    ) {
    }


    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getFilter(): Filter
    {
        return $this->filter;
    }
}
