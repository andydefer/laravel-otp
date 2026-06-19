<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\Services;

use Illuminate\Support\Str;

final class OtpGenerator
{
    public function generate(
        int $length = 6,
        bool $numeric = true,
    ): string {
        if ($numeric) {
            return $this->generateNumeric($length);
        }

        return $this->generateAlphanumeric($length);
    }

    private function generateNumeric(int $length): string
    {
        return Str::padLeft((string) random_int(0, (int) str_repeat('9', $length)), $length, '0');
    }

    private function generateAlphanumeric(int $length): string
    {
        return Str::upper(Str::random($length));
    }

    private function base32Decode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = strtoupper($secret);
        $buffer = '';

        foreach (str_split($secret) as $char) {
            $index = strpos($alphabet, $char);
            if ($index === false) {
                continue;
            }
            $buffer .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }

        if ($buffer === '') {
            return '';
        }

        $result = '';
        foreach (str_split($buffer, 8) as $byte) {
            if (strlen($byte) === 8) {
                $result .= chr(bindec($byte));
            }
        }

        return $result;
    }

    public function generateSecret(int $length = 32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        $maxIndex = strlen($alphabet) - 1;

        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[random_int(0, $maxIndex)];
        }

        return $secret;
    }

    public function generateRecoveryCodes(int $count = 10, int $length = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::upper(Str::random($length));
        }

        return $codes;
    }
}
