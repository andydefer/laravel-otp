<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\Repositories;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\LaravelOtp\Models\Otp;
use AndyDefer\LaravelOtp\Records\OtpFilterRecord;
use AndyDefer\LaravelOtp\Records\OtpRecord;
use AndyDefer\Repository\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class OtpRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(
            modelClass: Otp::class,
            recordClass: OtpRecord::class,
        );
    }

    protected function applyFilters(Builder $query, AbstractRecord $filters): void
    {
        if (! $filters instanceof OtpFilterRecord) {
            return;
        }

        if ($filters->identifier_type !== null) {
            $query->where('identifier_type', $filters->identifier_type);
        }

        if ($filters->identifier_id !== null) {
            $query->where('identifier_id', $filters->identifier_id);
        }

        if ($filters->code !== null) {
            $query->where('code', $filters->code);
        }

        if ($filters->purpose !== null) {
            $value = $filters->purpose->getValue()->toArray()['value'] ?? '';
            $driver = $query->getConnection()->getDriverName();

            if ($driver === 'sqlite') {
                $query->whereRaw('json_extract(purpose, "$.value") = ?', [$value]);
            } elseif ($driver === 'mysql') {
                $query->whereJsonContains('purpose', ['value' => $value]);
            } elseif ($driver === 'pgsql') {
                $query->whereRaw('purpose->>\'value\' = ?', [$value]);
            } else {
                $query->whereJsonContains('purpose', ['value' => $value]);
            }
        }

        if ($filters->is_valid !== null) {
            if ($filters->is_valid === true) {
                $query->whereNull('used_at')
                    ->where('expires_at', '>', now());
            } else {
                $query->where(function ($q) {
                    $q->whereNotNull('used_at')
                        ->orWhere('expires_at', '<=', now());
                });
            }
        }

        if ($filters->is_used !== null) {
            if ($filters->is_used === true) {
                $query->whereNotNull('used_at');
            } else {
                $query->whereNull('used_at');
            }
        }

        if ($filters->is_expired !== null) {
            if ($filters->is_expired === true) {
                $query->where('expires_at', '<=', now());
            } else {
                $query->where('expires_at', '>', now());
            }
        }

        if ($filters->expires_before !== null) {
            $query->where('expires_at', '<=', $filters->expires_before->toDateTimeString());
        }

        if ($filters->expires_after !== null) {
            $query->where('expires_at', '>=', $filters->expires_after->toDateTimeString());
        }

        if ($filters->used_before !== null) {
            $query->where('used_at', '<=', $filters->used_before->toDateTimeString());
        }

        if ($filters->used_after !== null) {
            $query->where('used_at', '>=', $filters->used_after->toDateTimeString());
        }
    }

    private function notFound(int $id): ModelNotFoundException
    {
        return (new ModelNotFoundException)->setModel(Otp::class, $id);
    }

    public function incrementAttempts(int $id): Otp
    {
        $otp = $this->find($id);

        if ($otp === null) {
            throw $this->notFound($id);
        }

        $otp->attempts++;
        $otp->save();

        return $otp;
    }

    public function markAsUsed(int $id): Otp
    {
        $otp = $this->find($id);

        if ($otp === null) {
            throw $this->notFound($id);
        }

        $otp->used_at = now();
        $otp->save();

        return $otp;
    }
}
