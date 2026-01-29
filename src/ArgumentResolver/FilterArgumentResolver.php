<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\ArgumentResolver;

use Beefeater\CrudEventBundle\Model\Filter;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FilterArgumentResolver implements ValueResolverInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (Filter::class !== $argument->getType()) {
            return [];
        }

        $queryString = $request->query->all('filter');

        if (!is_array($queryString)) {
            $this->logger->warning('Invalid filter parameter. Expected array, got: ' . gettype($queryString));
            throw new BadRequestHttpException('The "filter" parameter must be an array IN RESOLVER.');
        }

        $filter = new Filter($queryString);

        $quickSearch = $request->query->get('quickSearch');
        $quickSearchFields = $request->attributes->get(Filter::QUICK_SEARCH_KEY, []);

        if ($quickSearch && $quickSearchFields) {
            $qs = [];

            foreach ($quickSearchFields as $field) {
                $qs[] = [$field, $quickSearch];
            }

            $filter->setCriteria(Filter::QUICK_SEARCH_KEY, $qs);
        }

        return [$filter];
    }
}
