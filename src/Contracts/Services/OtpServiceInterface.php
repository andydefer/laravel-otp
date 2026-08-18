<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\Contracts\Services;

use AndyDefer\LaravelOtp\ValueObjects\PurposeVO;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface OtpServiceInterface
{
    /**
     * Create a new OTP for a model
     */
    public function create(
        Model $identifier,
        PurposeVO $purpose,
        ?string $code = null,
        ?int $ttl = null,
    ): Model;

    /**
     * Verify an OTP code
     */
    public function verify(
        Model $identifier,
        string $code,
        PurposeVO $purpose,
        bool $markAsUsed = true,
    ): bool;

    /**
     * Find a valid OTP
     */
    public function findValid(
        Model $identifier,
        string $code,
        PurposeVO $purpose,
    ): ?Model;

    /**
     * Invalidate all OTPs for a model and purpose
     */
    public function invalidate(Model $identifier, PurposeVO $purpose): void;

    /**
     * Get all OTPs for a model and purpose
     */
    public function getAllFor(Model $identifier, PurposeVO $purpose): Collection;

    /**
     * Get valid OTPs for a model and purpose
     */
    public function getValidFor(Model $identifier, PurposeVO $purpose): Collection;

    /**
     * Delete all expired OTPs
     */
    public function deleteExpired(): int;

    /**
     * Get attempts count for an OTP
     */
    public function getAttempts(Model $identifier, string $code, PurposeVO $purpose): int;

    /**
     * Increment attempts for an OTP
     */
    public function incrementAttempts(Model $identifier, string $code, PurposeVO $purpose): void;

    /**
     * Count all OTPs for a model and purpose
     */
    public function countFor(Model $identifier, PurposeVO $purpose): int;

    /**
     * Count valid OTPs for a model and purpose
     */
    public function countValidFor(Model $identifier, PurposeVO $purpose): int;

    /**
     * Count expired OTPs for a model and purpose
     */
    public function countExpiredFor(Model $identifier, PurposeVO $purpose): int;

    /**
     * Generate recovery codes
     */
    public function generateRecoveryCodes(Model $identifier, int $count = 10, int $length = 8): array;

    /**
     * Generate a secret key
     */
    public function generateSecret(): string;

    /**
     * Check if rate limit is exceeded
     */
    public function isRateLimited(
        Model $identifier,
        PurposeVO $purpose,
        int $limit = 5,
        ?CarbonInterface $window = null,
    ): bool;
}
