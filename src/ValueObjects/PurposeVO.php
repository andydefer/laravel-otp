<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use InvalidArgumentException;

final class PurposeVO extends AbstractValueObject
{
    public function __construct(
        private string $value,
        private ?string $label = null,
        private ?int $ttl = null,
        private ?int $maxAttempts = null
    ) {
        if (trim($value) === '') {
            throw new InvalidArgumentException('Purpose value cannot be empty');
        }

        $this->value = $value;
        $this->label = $label;
        $this->ttl = $ttl;
        $this->maxAttempts = $maxAttempts;
    }

    public function getValue(): StrictDataObject
    {
        return StrictDataObject::from([
            'value' => $this->value,
            'label' => $this->label,
            'ttl' => $this->ttl,
            'maxAttempts' => $this->maxAttempts,
        ]);
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    public function getMaxAttempts(): ?int
    {
        return $this->maxAttempts;
    }

    public function __toString(): string
    {
        return json_encode($this->getValue());
    }
}
