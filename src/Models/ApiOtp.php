<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\Models;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelOtp\ValueObjects\PurposeVO;
use AndyDefer\PhpVo\ValueObjects\DateTimeZuluVO;
use AndyDefer\Repository\Proxies\AttributeProxy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ApiOtp extends Model
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

    protected $appends = [
        'purpose',
        'created_at',
        'updated_at',
        'expires_at',
        'used_at',
        'deleted_at',
        'metadata',
        'is_used',
        'is_expired',
        'is_valid',
    ];

    public function identifier()
    {
        return $this->morphTo();
    }

    // ==========================================================================
    // ATTRIBUTES
    // ==========================================================================

    protected function purpose(): Attribute
    {
        return AttributeProxy::required(PurposeVO::class, column: 'purpose');
    }

    protected function createdAt(): Attribute
    {
        return AttributeProxy::required(DateTimeZuluVO::class, column: 'created_at');
    }

    protected function updatedAt(): Attribute
    {
        return AttributeProxy::required(DateTimeZuluVO::class, column: 'updated_at');
    }

    protected function expiresAt(): Attribute
    {
        return AttributeProxy::nullable(DateTimeZuluVO::class, column: 'expires_at');
    }

    protected function usedAt(): Attribute
    {
        return AttributeProxy::nullable(DateTimeZuluVO::class, column: 'used_at');
    }

    protected function deletedAt(): Attribute
    {
        return AttributeProxy::nullable(DateTimeZuluVO::class, column: 'deleted_at');
    }

    protected function metadata(): Attribute
    {
        return AttributeProxy::nullable(StrictDataObject::class, column: 'metadata');
    }

    protected function isUsed(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->used_at !== null,
        );
    }

    protected function isExpired(): Attribute
    {
        return Attribute::make(
            get: function (): bool {
                if ($this->used_at !== null) {
                    return true;
                }

                if ($this->expires_at === null) {
                    return false;
                }

                return now()->greaterThan($this->expires_at);
            },
        );
    }

    protected function isValid(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => ! $this->is_expired && ! $this->is_used,
        );
    }
}
