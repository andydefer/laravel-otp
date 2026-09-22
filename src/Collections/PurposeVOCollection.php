<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\LaravelOtp\ValueObjects\PurposeVO;

final class PurposeVOCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(PurposeVO::class);
    }

    public function findByValue(string $value): ?PurposeVO
    {
        return $this->find(
            fn (PurposeVO $purpose): bool => $purpose->getValue()->value === $value
        );
    }

    public function hasValue(string $value): bool
    {
        return $this->findByValue($value) !== null;
    }
}
