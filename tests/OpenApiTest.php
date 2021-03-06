<?php declare(strict_types=1);

namespace Sunrise\Http\Router\OpenApi\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\OpenApi\Object\ExternalDocumentation;
use Sunrise\Http\Router\OpenApi\Object\Info;
use Sunrise\Http\Router\OpenApi\Object\SecurityRequirement;
use Sunrise\Http\Router\OpenApi\Object\Server;
use Sunrise\Http\Router\OpenApi\Object\Tag;
use Sunrise\Http\Router\OpenApi\AbstractObject;
use Sunrise\Http\Router\OpenApi\ComponentObjectInterface;
use Sunrise\Http\Router\OpenApi\OpenApi;
use Sunrise\Http\Router\OpenApi\Tests\Fixture;
use Sunrise\Http\Router\RouteInterface;

/**
 * OpenApiTest
 */
class OpenApiTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new OpenApi(new Info('foo', 'bar'));

        $this->assertInstanceOf(AbstractObject::class, $object);
    }

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $object = new OpenApi(new Info('foo', 'bar'));

        $this->assertSame([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'foo',
                'version' => 'bar',
            ],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testAddServer() : void
    {
        $object = new OpenApi(new Info('foo', 'bar'));

        $object->addServer(
            new Server('baz'),
            new Server('qux')
        );

        $this->assertSame([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'foo',
                'version' => 'bar',
            ],
            'servers' => [
                [
                    'url' => 'baz',
                ],
                [
                    'url' => 'qux',
                ],
            ],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testAddComponentObject() : void
    {
        $object = new OpenApi(new Info('foo', 'bar'));

        $co1 = $this->createMock(ComponentObjectInterface::class);
        $co1->method('getComponentName')->willReturn('foo');
        $co1->method('getReferenceName')->willReturn('bar');
        $co1->method('toArray')->willReturn(['baz']);

        $co2 = $this->createMock(ComponentObjectInterface::class);
        $co2->method('getComponentName')->willReturn('qux');
        $co2->method('getReferenceName')->willReturn('quux');
        $co2->method('toArray')->willReturn(['quuux']);

        $object->addComponentObject($co1, $co2);

        $this->assertSame([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'foo',
                'version' => 'bar',
            ],
            'components' => [
                'foo' => [
                    'bar' => [
                        'baz',
                    ],
                ],
                'qux' => [
                    'quux' => [
                        'quuux',
                    ],
                ],
            ],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testAddSecurityRequirement() : void
    {
        $object = new OpenApi(new Info('foo', 'bar'));

        $object->addSecurityRequirement(
            new SecurityRequirement('baz'),
            new SecurityRequirement('qux')
        );

        $this->assertSame([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'foo',
                'version' => 'bar',
            ],
            'security' => [
                [
                    'baz' => [],
                ],
                [
                    'qux' => [],
                ],
            ],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testAddTag() : void
    {
        $object = new OpenApi(new Info('foo', 'bar'));

        $object->addTag(
            new Tag('baz'),
            new Tag('qux')
        );

        $this->assertSame([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'foo',
                'version' => 'bar',
            ],
            'tags' => [
                [
                    'name' => 'baz',
                ],
                [
                    'name' => 'qux',
                ],
            ],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetExternalDocs() : void
    {
        $object = new OpenApi(new Info('foo', 'bar'));

        $object->setExternalDocs(new ExternalDocumentation('baz'));

        $this->assertSame([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'foo',
                'version' => 'bar',
            ],
            'externalDocs' => [
                'url' => 'baz',
            ],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testAddRoute() : void
    {
        $route = $this->createMock(RouteInterface::class);
        $route->method('getRequestHandler')->willReturn(new Fixture\PetStore\Endpoint());
        $route->method('getName')->willReturn('foo');
        $route->method('getMethods')->willReturn(['GET']);
        $route->method('getPath')->willReturn('/foo(/{a<\d+>})/{b<\w+>}/{c}');

        $object = new OpenApi(new Info('foo', 'bar'));
        $object->addRoute($route);

        $this->assertSame([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'foo',
                'version' => 'bar',
            ],
            'paths' => [
                '/foo/{a}/{b}/{c}' => [
                    'get' => [
                        'operationId' => 'foo',
                        'parameters' => [
                            [
                                'name' => 'a',
                                'in' => 'path',
                                'required' => false,
                                'schema' => [
                                    'pattern' => '\d+',
                                    'type' => 'string',
                                ],
                            ],
                            [
                                'name' => 'b',
                                'in' => 'path',
                                'required' => true,
                                'schema' => [
                                    'pattern' => '\w+',
                                    'type' => 'string',
                                ],
                            ],
                            [
                                'name' => 'c',
                                'in' => 'path',
                                'required' => true,
                            ],
                        ],
                        'responses' => [
                            200 => [
                                'description' => 'All okay',
                            ],
                            'default' => [
                                'description' => 'Any error',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/Error',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'components' => [
                'schemas' => [
                    'Error' => [
                        'properties' => [
                            'code' => [
                                'format' => 'int32',
                                'type' => 'integer',
                            ],
                            'message' => [
                                'type' => 'string',
                            ],
                        ],
                        'required' => [
                            'code',
                            'message',
                        ],
                        'type' => 'object',
                    ],
                ],
            ],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testAddRouteWithoutDescription() : void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);

        $route = $this->createMock(RouteInterface::class);
        $route->method('getRequestHandler')->willReturn($handler);
        $route->method('getName')->willReturn('foo');
        $route->method('getMethods')->willReturn(['GET']);
        $route->method('getPath')->willReturn('/foo');

        $object = new OpenApi(new Info('foo', 'bar'));
        $object->addRoute($route);

        $this->assertSame([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'foo',
                'version' => 'bar',
            ],
            'paths' => [
                '/foo' => [
                    'get' => [
                        'operationId' => 'foo',
                    ],
                ],
            ],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testNotIncludeUndescribedRoutes() : void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);

        $route = $this->createMock(RouteInterface::class);
        $route->method('getRequestHandler')->willReturn($handler);
        $route->method('getName')->willReturn('foo');
        $route->method('getMethods')->willReturn(['GET']);
        $route->method('getPath')->willReturn('/foo');

        $object = new OpenApi(new Info('foo', 'bar'));
        $object->includeUndescribedOperations(false);
        $object->addRoute($route);

        $this->assertSame([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'foo',
                'version' => 'bar',
            ],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testRouteData() : void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);

        $route = $this->createMock(RouteInterface::class);
        $route->method('getRequestHandler')->willReturn($handler);
        $route->method('getName')->willReturn('foo');
        $route->method('getMethods')->willReturn(['GET']);
        $route->method('getPath')->willReturn('/foo');
        $route->method('getTags')->willReturn(['foo', 'bar']);
        $route->method('getSummary')->willReturn('summary of the route');
        $route->method('getDescription')->willReturn('description of the route');

        $object = new OpenApi(new Info('foo', 'bar'));
        $object->addRoute($route);

        $this->assertSame([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'foo',
                'version' => 'bar',
            ],
            'paths' => [
                '/foo' => [
                    'get' => [
                        'operationId' => 'foo',
                        'tags' => ['foo', 'bar'],
                        'summary' => 'summary of the route',
                        'description' => 'description of the route',
                    ],
                ],
            ],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testJson() : void
    {
        $object = new OpenApi(new Info('foo', 'bar'));

        $expected = json_encode($object->toArray(), \JSON_PRETTY_PRINT);

        $this->assertSame($expected, $object->toJson(\JSON_PRETTY_PRINT));
    }
}
