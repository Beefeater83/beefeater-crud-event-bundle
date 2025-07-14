<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class PayloadValidationException extends BadRequestHttpException
{
    protected ConstraintViolationListInterface $violations;

    public function __construct(string $className, ConstraintViolationListInterface $violations)
    {
        parent::__construct(sprintf('Validation failed for %s', $className));

        $this->violations = $violations;
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
