<?php
declare(strict_types=1);

namespace Maba\Bundle\RestBundle\Tests\Functional\Fixtures\FixtureTestBundle\PathAttributeResolver;

use Maba\Bundle\RestBundle\Service\PathAttributeResolver\PathAttributeResolverInterface;
use Maba\Bundle\RestBundle\Tests\Functional\Fixtures\FixtureTestBundle\Entity\MyObject;

class MyObjectResolver implements PathAttributeResolverInterface
{
    public function resolveFromAttribute($input)
    {
        return (new MyObject())->setField1($input);
    }
}
