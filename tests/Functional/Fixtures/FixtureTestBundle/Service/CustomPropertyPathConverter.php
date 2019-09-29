<?php
declare(strict_types=1);

namespace Maba\Bundle\RestBundle\Tests\Functional\Fixtures\FixtureTestBundle\Service;

use Maba\Bundle\RestBundle\Service\Validation\PropertyPathConverterInterface;

class CustomPropertyPathConverter implements PropertyPathConverterInterface
{
    public function convert($path)
    {
        return 'prefixed:' . $path;
    }
}
