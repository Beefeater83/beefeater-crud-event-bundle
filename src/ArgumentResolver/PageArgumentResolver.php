<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\ArgumentResolver;

use Beefeater\CrudEventBundle\Model\Page;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class PageArgumentResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (Page::class !== $argument->getType()) {
            return [];
        }

        $page = (int) $request->query->get('page', 1);
        $pageSize = (int) $request->query->get('pageSize', 25);

        return [new Page($page, $pageSize)];
    }
}
