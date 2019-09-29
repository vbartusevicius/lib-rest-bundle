<?php
declare(strict_types=1);

namespace Maba\Bundle\RestBundle\Entity;

use RuntimeException;

class QueryResolverOptions
{
    /**
     * @var string|null
     */
    private $parameterName;

    /**
     * @var string|null
     */
    private $denormalizationType;

    /**
     * @var string|null
     */
    private $denormalizationGroup;

    /**
     * @var ValidationOptions|null
     */
    private $validationOptions;

    public function __construct()
    {
        $this->validationOptions = new ValidationOptions();
    }

    public function setParameterName(string $parameterName): self
    {
        $this->parameterName = $parameterName;
        return $this;
    }

    public function setDenormalizationType(string $denormalizationType): self
    {
        $this->denormalizationType = $denormalizationType;
        return $this;
    }

    /**
     * @param string|null $denormalizationGroup
     * @return QueryResolverOptions
     */
    public function setDenormalizationGroup($denormalizationGroup): self
    {
        $this->denormalizationGroup = $denormalizationGroup;
        return $this;
    }

    /**
     * @param ValidationOptions|null $validationOptions
     * @return $this
     */
    public function setValidationOptions(ValidationOptions $validationOptions = null): self
    {
        $this->validationOptions = $validationOptions;
        return $this;
    }

    public function disableValidation(): self
    {
        $this->validationOptions = null;
        return $this;
    }

    public function getParameterName(): string
    {
        if ($this->parameterName === null) {
            throw new RuntimeException('parameterName was not set');
        }
        return $this->parameterName;
    }

    public function getDenormalizationType(): string
    {
        if ($this->denormalizationType === null) {
            throw new RuntimeException('denormalizationType was not set');
        }
        return $this->denormalizationType;
    }

    /**
     * return string|null
     */
    public function getDenormalizationGroup()
    {
        return $this->denormalizationGroup;
    }

    public function isValidationNeeded(): bool
    {
        return $this->validationOptions !== null && $this->validationOptions->isEnabled();
    }

    public function getValidationOptions(): ValidationOptions
    {
        if ($this->validationOptions === null) {
            throw new RuntimeException('No validationOptions available, call isValidationNeeded beforehand');
        }
        return $this->validationOptions;
    }
}
