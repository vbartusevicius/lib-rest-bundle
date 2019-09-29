<?php
declare(strict_types=1);

namespace Maba\Bundle\RestBundle\Annotation;

use Maba\Bundle\RestBundle\Entity\RestRequestOptions;
use Maba\Bundle\RestBundle\Service\Annotation\ReflectionMethodWrapper;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class RequiredPermissions implements RestAnnotationInterface
{
    /**
     * @var array
     */
    private $permissions;

    public function __construct(array $options)
    {
        $this->setPermissions($options['permissions']);
    }

    /**
     * @param array $permissions
     * @return $this
     */
    private function setPermissions(array $permissions): self
    {
        $this->permissions = $permissions;
        return $this;
    }

    public function isSeveralSupported(): bool
    {
        return true;
    }

    public function apply(RestRequestOptions $options, ReflectionMethodWrapper $reflectionMethod)
    {
        $options->setRequiredPermissions(
            array_unique(array_merge($options->getRequiredPermissions(), $this->permissions))
        );
    }
}
