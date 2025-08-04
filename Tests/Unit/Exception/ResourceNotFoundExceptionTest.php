<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Tests\Unit\Exception;

use Beefeater\CrudEventBundle\Exception\ResourceNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResourceNotFoundExceptionTest extends TestCase
{
    public function testResourseNotFound(): void
    {
        $className = "RandomClass";
        $id = 1;
        $exception = new ResourceNotFoundException($className, $id);
        $expectedMessage = $className . " with id " . $id . " not found";
        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame(404, $exception->getStatusCode());
        $this->assertInstanceOf(NotFoundHttpException::class, $exception);
    }
}
