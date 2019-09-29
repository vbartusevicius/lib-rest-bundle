<?php
declare(strict_types=1);

namespace Maba\Bundle\RestBundle\Normalizer;

use Maba\Bundle\RestBundle\Entity\Violation;
use Paysera\Component\Normalization\NormalizationContext;
use Paysera\Component\Normalization\NormalizerInterface;
use Paysera\Component\Normalization\TypeAwareInterface;

class ViolationNormalizer implements NormalizerInterface, TypeAwareInterface
{
    /**
     * @param Violation $result
     * @param NormalizationContext $normalizationContext
     * @return array
     */
    public function normalize($result, NormalizationContext $normalizationContext)
    {
        return [
            'code' => $result->getCode(),
            'message' => $result->getMessage(),
            'field' => $result->getField(),
        ];
    }

    public function getType(): string
    {
        return Violation::class;
    }
}
