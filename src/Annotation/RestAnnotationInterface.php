<?php
declare(strict_types=1);

namespace Maba\Bundle\RestBundle\Annotation;

use Maba\Bundle\RestBundle\Service\Annotation\ReflectionMethodWrapper;
use Maba\Bundle\RestBundle\Entity\RestRequestOptions;

interface RestAnnotationInterface
{
    public function isSeveralSupported(): bool;

    public function apply(RestRequestOptions $options, ReflectionMethodWrapper $reflectionMethod);
}
