<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Tests\Unit\Exception;

use Beefeater\CrudEventBundle\Exception\PayloadValidationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class PayloadValidationExceptionTest extends TestCase
{
    public function testValidationException(): void
    {
        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $className = "RandomClass";
        $exception = new PayloadValidationException($className, $violations);
        $this->assertSame($violations, $exception->getViolations());
        $this->assertSame("Validation failed for $className", $exception->getMessage());
        $this->assertInstanceOf(BadRequestHttpException::class, $exception);
    }
}
