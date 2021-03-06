<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2019, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router-openapi/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router-openapi
 */

namespace Sunrise\Http\Router\OpenApi\Middleware;

/**
 * Import classes
 */
use JsonSchema\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\BadRequestException;
use Sunrise\Http\Router\Exception\UnsupportedMediaTypeException;
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedMediaTypeException as LocalUnsupportedMediaTypeException;
use Sunrise\Http\Router\OpenApi\Utility\JsonSchemaBuilder;
use Sunrise\Http\Router\Route;
use Sunrise\Http\Router\RouteInterface;
use ReflectionClass;
use RuntimeException;

/**
 * Import functions
 */
use function class_exists;
use function json_decode;
use function json_encode;
use function strpos;
use function substr;

/**
 * RequestBodyValidationMiddleware
 *
 * Don't use this middleware globally!
 */
class RequestBodyValidationMiddleware implements MiddlewareInterface
{

    /**
     * @var bool
     */
    private $useCache = false;

    /**
     * Constructor of the class
     *
     * @throws RuntimeException
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        if (!class_exists('JsonSchema\Validator')) {
            throw new RuntimeException('To use request body validation, install the "justinrainbow/json-schema"');
        }
    }

    /**
     * @return void
     */
    public function useCache() : void
    {
        $this->useCache = true;
    }

    /**
     * {@inheritDoc}
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $this->validate($request);

        return $handler->handle($request);
    }

    /**
     * Tries to determine a media type for the request body
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     *
     * @link https://tools.ietf.org/html/rfc7231#section-3.1.1.1
     */
    protected function fetchMediaType(ServerRequestInterface $request) : string
    {
        $result = $request->getHeaderLine('Content-Type');
        $semicolon = strpos($result, ';');

        if (false !== $semicolon) {
            $result = substr($result, 0, $semicolon);
        }

        return $result;
    }

    /**
     * Validates the given request
     *
     * @param ServerRequestInterface $request
     *
     * @return void
     *
     * @throws BadRequestException
     * @throws UnsupportedMediaTypeException
     */
    protected function validate(ServerRequestInterface $request) : void
    {
        $route = $request->getAttribute(Route::ATTR_NAME_FOR_ROUTE);
        if (!($route instanceof RouteInterface)) {
            return;
        }

        $operationSource = new ReflectionClass($route->getRequestHandler());
        $jsonSchemaBuilder = new JsonSchemaBuilder($operationSource);

        if ($this->useCache) {
            $jsonSchemaBuilder->useCache();
        }

        try {
            $mediaType = $this->fetchMediaType($request);
            $jsonSchema = $jsonSchemaBuilder->forRequestBody($mediaType);
        } catch (LocalUnsupportedMediaTypeException $e) {
            throw new UnsupportedMediaTypeException($e->getMessage(), [
                'type' => $e->getType(),
                'supported' => $e->getSupportedTypes(),
            ], $e->getCode(), $e);
        }

        if (false === isset($jsonSchema, $jsonSchema['type'])) {
            return;
        }

        $payload = null;
        $parsedBody = $request->getParsedBody();

        switch ($jsonSchema['type']) {
            case 'array':
                if ([] === $parsedBody) {
                    $payload = [];
                } else {
                    $payload = json_encode($parsedBody);
                    $payload = (array) json_decode($payload);
                }
                break;

            case 'object':
                if ([] === $parsedBody) {
                    $payload = new \stdClass();
                } else {
                    $payload = json_encode($parsedBody);
                    $payload = (object) json_decode($payload);
                }
                break;

            case 'string':
                $payload = (string) $request->getBody();
                break;
        }

        $validator = new Validator();
        $validator->validate($payload, $jsonSchema);

        if (!$validator->isValid()) {
            throw new BadRequestException('The request body is not valid for this resource.', [
                'jsonSchema' => $jsonSchema,
                'violations' => $validator->getErrors(),
            ]);
        }
    }
}
