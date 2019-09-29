<?php
declare(strict_types=1);

namespace Maba\Bundle\RestBundle\Tests\Functional\Fixtures\FixtureTestBundle\PathAttributeResolver;

use Maba\Bundle\RestBundle\Service\PathAttributeResolver\PathAttributeResolverInterface;

class PrefixedPathAttributeResolver implements PathAttributeResolverInterface
{
    public function resolveFromAttribute($attributeValue)
    {
        return 'prefixed_' . $attributeValue;
    }
}
