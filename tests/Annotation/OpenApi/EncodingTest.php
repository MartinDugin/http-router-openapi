<?php declare(strict_types=1);

namespace Sunrise\Http\Router\OpenApi\Tests\Annotation\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\OpenApi\Annotation\OpenApi\Encoding;
use Sunrise\Http\Router\OpenApi\Annotation\OpenApi\EncodingInterface;
use Sunrise\Http\Router\OpenApi\AbstractAnnotation;
use Sunrise\Http\Router\OpenApi\ObjectInterface;

/**
 * EncodingTest
 */
class EncodingTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new Encoding();

        $this->assertInstanceOf(AbstractAnnotation::class, $object);
        $this->assertInstanceOf(EncodingInterface::class, $object);
        $this->assertInstanceOf(ObjectInterface::class, $object);
    }
}
