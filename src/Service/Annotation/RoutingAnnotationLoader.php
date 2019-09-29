<?php
declare(strict_types=1);

namespace Maba\Bundle\RestBundle\Service\Annotation;

use Maba\Bundle\RestBundle\Annotation\RestAnnotationInterface;
use Maba\Bundle\RestBundle\Service\RestRequestHelper;
use Symfony\Bundle\FrameworkBundle\Routing\AnnotatedRouteControllerLoader;
use Symfony\Component\Routing\Route;
use ReflectionClass;
use ReflectionMethod;

/**
 * @internal
 */
class RoutingAnnotationLoader extends AnnotatedRouteControllerLoader
{
    /**
     * @var RestRequestHelper
     */
    private $restRequestHelper;

    /**
     * @var RestRequestOptionsBuilder
     */
    private $restRequestOptionsBuilder;

    public function setRequestHelper(RestRequestHelper $restRequestHelper)
    {
        $this->restRequestHelper = $restRequestHelper;
    }

    public function setRestRequestOptionsBuilder(RestRequestOptionsBuilder $restRequestOptionsBuilder)
    {
        $this->restRequestOptionsBuilder = $restRequestOptionsBuilder;
    }

    protected function configureRoute(Route $route, ReflectionClass $class, ReflectionMethod $method, $routeAnnotation)
    {
        parent::configureRoute($route, $class, $method, $routeAnnotation);

        $annotations = [];
        foreach ($this->reader->getClassAnnotations($class) as $annotation) {
            if ($annotation instanceof RestAnnotationInterface) {
                $annotations[] = $annotation;
            }
        }

        foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
            if ($annotation instanceof RestAnnotationInterface) {
                $annotations[] = $annotation;
            }
        }

        if (count($annotations) === 0) {
            return;
        }

        $this->restRequestHelper->setOptionsForRoute(
            $route,
            $this->restRequestOptionsBuilder->buildOptions($annotations, $method)
        );
    }
}
