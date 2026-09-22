<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\Models;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelOtp\ValueObjects\PurposeVO;
use AndyDefer\PhpVo\ValueObjects\DateTimeZuluVO;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Otp extends Model
{
    use SoftDeletes;

    protected $table = 'otps';

    protected $fillable = [
        'identifier_type',
        'identifier_id',
        'code',
        'purpose',
        'expires_at',
        'used_at',
        'attempts',
        'metadata',
    ];

    protected $casts = [
        'purpose' => 'array',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'attempts' => 'integer',
        'metadata' => 'array',
    ];

    public function identifier()
    {
        return $this->morphTo();
    }

    public function getPurpose(): PurposeVO
    {
        return PurposeVO::from($this->purpose ?? []);
    }

    public function getCreatedAt(): ?DateTimeZuluVO
    {
        $value = $this->created_at;

        return $value ? new DateTimeZuluVO($value->utc()->format('Y-m-d\TH:i:s\Z')) : null;
    }

    public function getUpdatedAt(): ?DateTimeZuluVO
    {
        $value = $this->updated_at;

        return $value ? new DateTimeZuluVO($value->utc()->format('Y-m-d\TH:i:s\Z')) : null;
    }

    public function getExpiresAt(): ?DateTimeZuluVO
    {
        $value = $this->expires_at;

        return $value ? new DateTimeZuluVO($value->utc()->format('Y-m-d\TH:i:s\Z')) : null;
    }

    public function getUsedAt(): ?DateTimeZuluVO
    {
        $value = $this->used_at;

        return $value ? new DateTimeZuluVO($value->utc()->format('Y-m-d\TH:i:s\Z')) : null;
    }

    public function getDeletedAt(): ?DateTimeZuluVO
    {
        $value = $this->deleted_at;

        return $value ? new DateTimeZuluVO($value->utc()->format('Y-m-d\TH:i:s\Z')) : null;
    }

    public function getMetadata(): ?StrictDataObject
    {
        $value = $this->metadata;

        if ($value === null) {
            return null;
        }

        $data = is_string($value) ? json_decode($value, true) : $value;

        return is_array($data) ? new StrictDataObject($data) : null;
    }

    public function isExpired(): bool
    {
        if ($this->used_at !== null) {
            return true;
        }

        if ($this->expires_at === null) {
            return false;
        }

        return now()->greaterThan($this->expires_at);
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function isValid(): bool
    {
        return ! $this->isExpired() && ! $this->isUsed();
    }
}
