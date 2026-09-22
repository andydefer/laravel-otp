<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelOtp\ValueObjects\PurposeVO;
use AndyDefer\PhpVo\ValueObjects\DateTimeZuluVO;

final class OtpData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly int $id,
        public readonly string $identifier_type,
        public readonly string $identifier_id,
        public readonly string $code,
        public readonly PurposeVO $purpose,
        public readonly ?DateTimeZuluVO $expires_at,
        public readonly ?DateTimeZuluVO $used_at,
        public readonly int $attempts,
        public readonly ?StrictDataObject $metadata,
        public readonly DateTimeZuluVO $created_at,
        public readonly DateTimeZuluVO $updated_at,
        public readonly ?DateTimeZuluVO $deleted_at,
        public readonly bool $is_expired,
        public readonly bool $is_used,
        public readonly bool $is_valid,
    ) {}
}
