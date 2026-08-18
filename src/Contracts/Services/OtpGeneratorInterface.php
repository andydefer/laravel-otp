<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\Contracts\Services;

interface OtpGeneratorInterface
{
    /**
     * Generate an OTP code
     *
     * @param  int  $length  Length of the OTP
     * @param  bool  $numeric  If true, generate numeric OTP, else alphanumeric
     * @return string The generated OTP
     */
    public function generate(int $length = 6, bool $numeric = true): string;

    /**
     * Generate a secret key for TOTP/HOTP
     *
     * @param  int  $length  Length of the secret
     * @return string The generated secret
     */
    public function generateSecret(int $length = 32): string;

    /**
     * Generate recovery codes
     *
     * @param  int  $count  Number of recovery codes
     * @param  int  $length  Length of each recovery code
     * @return array<string> Array of recovery codes
     */
    public function generateRecoveryCodes(int $count = 10, int $length = 8): array;
}
