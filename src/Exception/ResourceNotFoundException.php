<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResourceNotFoundException extends NotFoundHttpException
{
    public function __construct(string $entityClass, $id)
    {
        parent::__construct(sprintf('%s with id %s not found', $entityClass, (string) $id));
    }
}
