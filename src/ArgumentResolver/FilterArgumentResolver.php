<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\ArgumentResolver;

use Beefeater\CrudEventBundle\Model\Filter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FilterArgumentResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (Filter::class !== $argument->getType()) {
            return [];
        }

        $queryString = $request->query->all('filter');

        if (!is_array($queryString)) {
            throw new BadRequestHttpException('The "filter" parameter must be an array IN RESOLVER.');
        }

        return [new Filter($queryString)];
    }
}
