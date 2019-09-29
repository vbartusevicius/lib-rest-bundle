<?php
declare(strict_types=1);

namespace Maba\Bundle\RestBundle\Tests\Unit\Normalizer;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Normalization\ObjectDenormalizerInterface;
use Paysera\Component\ObjectWrapper\ObjectWrapper;

class DenormalizerTestCase extends MockeryTestCase
{
    /**
     * @var MockInterface|DenormalizationContext
     */
    protected $context;

    protected function setUp()
    {
        parent::setUp();
        $this->context = Mockery::mock(DenormalizationContext::class);
    }

    protected function callDenormalize(ObjectDenormalizerInterface $denormalizer, array $data)
    {
        return $denormalizer->denormalize(new ObjectWrapper((object)json_decode(json_encode($data))), $this->context);
    }
}
