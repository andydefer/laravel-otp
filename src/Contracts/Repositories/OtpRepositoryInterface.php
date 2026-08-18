<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\Contracts\Repositories;

use AndyDefer\Repository\AbstractRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface OtpRepositoryInterface extends AbstractRepositoryInterface
{
    /**
     * Increment attempts count for an OTP
     *
     * @param  int  $id  OTP ID
     * @return object The updated OTP model
     *
     * @throws ModelNotFoundException
     */
    public function incrementAttempts(int $id): object;

    /**
     * Mark an OTP as used
     *
     * @param  int  $id  OTP ID
     * @return object The updated OTP model
     *
     * @throws ModelNotFoundException
     */
    public function markAsUsed(int $id): object;
}
