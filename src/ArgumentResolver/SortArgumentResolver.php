<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\ArgumentResolver;

use Beefeater\CrudEventBundle\Model\Sort;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class SortArgumentResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (Sort::class !== $argument->getType()) {
            return [];
        }

        $sort = (string) $request->query->get('sort', '');

        return [new Sort($sort)];
    }
}
