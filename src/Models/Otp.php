<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\Models;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelOtp\ValueObjects\PurposeVO;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;
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

    public function getCreatedAt(): ?DateTimeVO
    {
        $value = $this->created_at;

        return $value ? new DateTimeVO($value) : null;
    }

    public function getUpdatedAt(): ?DateTimeVO
    {
        $value = $this->updated_at;

        return $value ? new DateTimeVO($value) : null;
    }

    public function getExpiresAt(): ?DateTimeVO
    {
        $value = $this->expires_at;

        return $value ? new DateTimeVO($value) : null;
    }

    public function getUsedAt(): ?DateTimeVO
    {
        $value = $this->used_at;

        return $value ? new DateTimeVO($value) : null;
    }

    public function getDeletedAt(): ?DateTimeVO
    {
        $value = $this->deleted_at;

        return $value ? new DateTimeVO($value) : null;
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
