<?php declare(strict_types=1);

namespace Sunrise\Http\Router\OpenApi\Tests\Annotation\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\OpenApi\Annotation\OpenApi\Operation;
use Sunrise\Http\Router\OpenApi\Annotation\OpenApi\OperationInterface;
use Sunrise\Http\Router\OpenApi\AbstractAnnotation;
use Sunrise\Http\Router\OpenApi\ObjectInterface;

/**
 * OperationTest
 */
class OperationTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new Operation();

        $this->assertInstanceOf(AbstractAnnotation::class, $object);
        $this->assertInstanceOf(OperationInterface::class, $object);
        $this->assertInstanceOf(ObjectInterface::class, $object);
    }
}
