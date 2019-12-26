<?php declare(strict_types=1);

namespace Sunrise\Http\Router\OpenApi\Tests\Fixture;

/**
 * Import classes
 */
use Doctrine\Common\Annotations\SimpleAnnotationReader;

/**
 * AwareSimpleAnnotationReader
 */
trait AwareSimpleAnnotationReader
{

    /**
     * @return SimpleAnnotationReader
     */
    private function createSimpleAnnotationReader() : SimpleAnnotationReader
    {
        $annotationReader = new SimpleAnnotationReader();
        $annotationReader->addNamespace('Sunrise\Http\Router\OpenApi\Annotation');

        return $annotationReader;
    }
}
