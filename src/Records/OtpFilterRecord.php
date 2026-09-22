<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\LaravelOtp\ValueObjects\PurposeVO;
use AndyDefer\PhpVo\ValueObjects\DateTimeZuluVO;

final class OtpFilterRecord extends AbstractRecord
{
    public function __construct(
        public readonly ?string $identifier_type = null,
        public readonly ?string $identifier_id = null,
        public readonly ?string $code = null,
        public readonly ?PurposeVO $purpose = null,
        public readonly ?bool $is_valid = null,
        public readonly ?bool $is_used = null,
        public readonly ?bool $is_expired = null,
        public readonly ?DateTimeZuluVO $expires_before = null,
        public readonly ?DateTimeZuluVO $expires_after = null,
        public readonly ?DateTimeZuluVO $used_before = null,
        public readonly ?DateTimeZuluVO $used_after = null,
    ) {}
}
