<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\Services;

use AndyDefer\LaravelOtp\Records\OtpFilterRecord;
use AndyDefer\LaravelOtp\Records\OtpRecord;
use AndyDefer\LaravelOtp\Repositories\OtpRepository;
use AndyDefer\LaravelOtp\ValueObjects\PurposeVO;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;
use AndyDefer\Repository\Records\FindByRecord;
use AndyDefer\Repository\ValueObjects\SelectColumns;
use AndyDefer\Repository\ValueObjects\SortColumns;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class OtpService
{
    public function __construct(
        private readonly OtpRepository $otpRepository,
        private readonly OtpGenerator $generator,
    ) {}

    public function create(
        Model $identifier,
        PurposeVO $purpose,
        ?string $code = null,
        ?int $ttl = null,
    ): Model {
        $code = $code ?? $this->generator->generate();
        $ttl = $ttl ?? $purpose->getTtl() ?? 300;

        $this->invalidate($identifier, $purpose);

        $record = OtpRecord::from([
            'identifier_type' => $identifier->getMorphClass(),
            'identifier_id' => $identifier->getKey(),
            'code' => $code,
            'purpose' => $purpose->getValue()->toArray(),
            'expires_at' => now()->addSeconds($ttl),
            'attempts' => 0,
        ]);

        return $this->otpRepository->create($record);
    }

    public function verify(
        Model $identifier,
        string $code,
        PurposeVO $purpose,
        bool $markAsUsed = true,
    ): bool {
        $otp = $this->findValid($identifier, $code, $purpose);

        if (! $otp) {
            return false;
        }

        $maxAttempts = $purpose->getMaxAttempts() ?? 3;

        if ($otp->attempts >= $maxAttempts) {
            $this->otpRepository->delete($otp->id);

            return false;
        }

        if ($markAsUsed) {
            $this->otpRepository->markAsUsed($otp->id);
        }

        return true;
    }

    public function findValid(
        Model $identifier,
        string $code,
        PurposeVO $purpose,
    ): ?Model {
        $filter = OtpFilterRecord::from([
            'identifier_type' => $identifier->getMorphClass(),
            'identifier_id' => $identifier->getKey(),
            'code' => $code,
            'purpose' => $purpose,
            'is_valid' => true,
        ]);

        $findByRecord = new FindByRecord(
            filters: $filter,
            limit: 1,
            sortBy: new SortColumns('created_at:desc'),
            columns: new SelectColumns(['*']),
        );

        $collection = $this->otpRepository->findBy($findByRecord);

        return $collection->first();
    }

    public function invalidate(Model $identifier, PurposeVO $purpose): void
    {
        $otps = $this->getAllFor($identifier, $purpose);

        foreach ($otps as $otp) {
            $this->otpRepository->markAsUsed($otp->id);
        }
    }

    public function getAllFor(Model $identifier, PurposeVO $purpose): Collection
    {
        $filter = OtpFilterRecord::from([
            'identifier_type' => $identifier->getMorphClass(),
            'identifier_id' => $identifier->getKey(),
            'purpose' => $purpose,
        ]);

        $findByRecord = new FindByRecord(
            filters: $filter,
            sortBy: new SortColumns('created_at:desc'),
        );

        return $this->otpRepository->findBy($findByRecord);
    }

    public function getValidFor(Model $identifier, PurposeVO $purpose): Collection
    {
        $filter = OtpFilterRecord::from([
            'identifier_type' => $identifier->getMorphClass(),
            'identifier_id' => $identifier->getKey(),
            'purpose' => $purpose,
            'is_valid' => true,
        ]);

        $findByRecord = new FindByRecord(
            filters: $filter,
            sortBy: new SortColumns('created_at:desc'),
        );

        return $this->otpRepository->findBy($findByRecord);
    }

    public function deleteExpired(): int
    {
        $filter = OtpFilterRecord::from([
            'is_expired' => true,
        ]);

        $expiredOtps = $this->otpRepository->findBy(
            new FindByRecord(filters: $filter)
        );
        $deletedCount = 0;

        foreach ($expiredOtps as $otp) {
            $this->otpRepository->delete($otp->id);
            $deletedCount++;
        }

        return $deletedCount;
    }

    public function getAttempts(Model $identifier, string $code, PurposeVO $purpose): int
    {
        $filter = OtpFilterRecord::from([
            'identifier_type' => $identifier->getMorphClass(),
            'identifier_id' => $identifier->getKey(),
            'code' => $code,
            'purpose' => $purpose,
        ]);

        $findByRecord = new FindByRecord(
            filters: $filter,
            limit: 1,
            sortBy: new SortColumns('created_at:desc'),
            columns: new SelectColumns(['*']),
        );

        $collection = $this->otpRepository->findBy($findByRecord);

        $otp = $collection->first();

        return $otp ? $otp->attempts : 0;
    }

    public function incrementAttempts(Model $identifier, string $code, PurposeVO $purpose): void
    {
        $filter = OtpFilterRecord::from([
            'identifier_type' => $identifier->getMorphClass(),
            'identifier_id' => $identifier->getKey(),
            'code' => $code,
            'purpose' => $purpose,
        ]);

        $findByRecord = new FindByRecord(
            filters: $filter,
            limit: 1,
            sortBy: new SortColumns('created_at:desc'),
            columns: new SelectColumns(['*']),
        );

        $collection = $this->otpRepository->findBy($findByRecord);

        $otp = $collection->first();

        if ($otp) {
            $this->otpRepository->incrementAttempts($otp->id);
        }
    }

    public function countFor(Model $identifier, PurposeVO $purpose): int
    {
        $filter = OtpFilterRecord::from([
            'identifier_type' => $identifier->getMorphClass(),
            'identifier_id' => $identifier->getKey(),
            'purpose' => $purpose,
        ]);

        return $this->otpRepository->count($filter);
    }

    public function countValidFor(Model $identifier, PurposeVO $purpose): int
    {
        $filter = OtpFilterRecord::from([
            'identifier_type' => $identifier->getMorphClass(),
            'identifier_id' => $identifier->getKey(),
            'purpose' => $purpose,
            'is_valid' => true,
        ]);

        return $this->otpRepository->count($filter);
    }

    public function countExpiredFor(Model $identifier, PurposeVO $purpose): int
    {
        $filter = OtpFilterRecord::from([
            'identifier_type' => $identifier->getMorphClass(),
            'identifier_id' => $identifier->getKey(),
            'purpose' => $purpose,
            'is_expired' => true,
        ]);

        return $this->otpRepository->count($filter);
    }

    public function generateRecoveryCodes(Model $identifier, int $count = 10, int $length = 8): array
    {
        return $this->generator->generateRecoveryCodes($count, $length);
    }

    public function generateSecret(): string
    {
        return $this->generator->generateSecret();
    }

    public function isRateLimited(
        Model $identifier,
        PurposeVO $purpose,
        int $limit = 5,
        ?CarbonInterface $window = null,
    ): bool {
        $window = $window ?? now()->subMinutes(15);

        $filter = OtpFilterRecord::from([
            'identifier_type' => $identifier->getMorphClass(),
            'identifier_id' => $identifier->getKey(),
            'purpose' => $purpose,
            'expires_after' => new DateTimeVO($window->toDateTimeString()),
        ]);

        $count = $this->otpRepository->count($filter);

        return $count >= $limit;
    }
}
