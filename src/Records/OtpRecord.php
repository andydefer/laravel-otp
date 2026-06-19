<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelOtp\ValueObjects\PurposeVO;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;

final class OtpRecord extends AbstractRecord
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $identifier_type = null,
        public readonly ?int $identifier_id = null,
        public readonly ?string $code = null,
        public readonly ?PurposeVO $purpose = null,
        public readonly ?DateTimeVO $expires_at = null,
        public readonly ?DateTimeVO $used_at = null,
        public readonly ?int $attempts = null,
        public readonly ?StrictDataObject $metadata = null,
        public readonly ?DateTimeVO $created_at = null,
        public readonly ?DateTimeVO $updated_at = null,
        public readonly ?DateTimeVO $deleted_at = null,
    ) {}

}
