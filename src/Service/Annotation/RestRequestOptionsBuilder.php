<?php
declare(strict_types=1);

namespace Maba\Bundle\RestBundle\Service\Annotation;

use Maba\Bundle\RestBundle\Exception\ConfigurationException;
use Maba\Bundle\RestBundle\Service\RestRequestOptionsValidator;
use ReflectionMethod;
use Maba\Bundle\RestBundle\Annotation\RestAnnotationInterface;
use Maba\Bundle\RestBundle\Entity\RestRequestOptions;

/**
 * @internal
 */
class RestRequestOptionsBuilder
{
    private $validator;

    public function __construct(RestRequestOptionsValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param array|RestAnnotationInterface[] $annotations
     * @param ReflectionMethod $reflectionMethod
     * @return RestRequestOptions
     * @throws ConfigurationException
     */
    public function buildOptions(array $annotations, ReflectionMethod $reflectionMethod): RestRequestOptions
    {
        $options = new RestRequestOptions();
        $wrapper = new ReflectionMethodWrapper($reflectionMethod);
        $usedAnnotations = [];

        foreach ($annotations as $annotation) {
            $className = get_class($annotation);
            if (!$annotation->isSeveralSupported() && isset($usedAnnotations[$className])) {
                throw new ConfigurationException(
                    sprintf('Only one annotation of type %s is supported', $className)
                );
            }
            $usedAnnotations[$className] = true;

            $annotation->apply($options, $wrapper);
        }

        $this->validator->validateRestRequestOptions($options, $wrapper->getFriendlyName());

        return $options;
    }
}
