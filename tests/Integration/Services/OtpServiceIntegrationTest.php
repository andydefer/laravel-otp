<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\Tests\Integration\Services;

use AndyDefer\LaravelOtp\Models\Otp;
use AndyDefer\LaravelOtp\Repositories\OtpRepository;
use AndyDefer\LaravelOtp\Services\OtpGenerator;
use AndyDefer\LaravelOtp\Services\OtpService;
use AndyDefer\LaravelOtp\Tests\Fixtures\Models\TestUser;
use AndyDefer\LaravelOtp\Tests\IntegrationTestCase;
use AndyDefer\LaravelOtp\ValueObjects\PurposeVO;

final class OtpServiceIntegrationTest extends IntegrationTestCase
{
    private OtpService $otpService;

    private TestUser $user;

    private PurposeVO $authenticationPurpose;

    private PurposeVO $passwordResetPurpose;

    private PurposeVO $twoFactorPurpose;

    private PurposeVO $emailVerificationPurpose;

    protected function setUp(): void
    {
        parent::setUp();

        $this->otpService = new OtpService(
            new OtpRepository,
            new OtpGenerator
        );

        $this->user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->authenticationPurpose = new PurposeVO(
            value: 'authentication',
            label: 'Authentification',
            ttl: 300,
            maxAttempts: 3,
        );

        $this->passwordResetPurpose = new PurposeVO(
            value: 'password_reset',
            label: 'Réinitialisation du mot de passe',
            ttl: 900,
            maxAttempts: 5,
        );

        $this->twoFactorPurpose = new PurposeVO(
            value: 'two_factor',
            label: 'Double authentification',
            ttl: 120,
            maxAttempts: 3,
        );

        $this->emailVerificationPurpose = new PurposeVO(
            value: 'email_verification',
            label: 'Vérification email',
            ttl: 3600,
            maxAttempts: 10,
        );
    }

    public function test_create_otp(): void
    {
        $otp = $this->otpService->create(
            $this->user,
            $this->authenticationPurpose
        );

        $otp->refresh();

        $this->assertInstanceOf(Otp::class, $otp);
        $this->assertSame($this->user->id, $otp->identifier_id);
        $this->assertSame('authentication', $otp->getPurpose()->getValue()->toArray()['value']);
        $this->assertFalse($otp->isUsed());
        $this->assertFalse($otp->isExpired());
        $this->assertSame(0, $otp->attempts);
    }

    public function test_create_otp_with_custom_code(): void
    {
        $customCode = '123456';

        $otp = $this->otpService->create(
            $this->user,
            $this->authenticationPurpose,
            $customCode
        );

        $otp->refresh();

        $this->assertSame($customCode, $otp->code);
    }

    public function test_create_otp_with_custom_ttl(): void
    {
        $ttl = 600;

        $otp = $this->otpService->create(
            $this->user,
            $this->authenticationPurpose,
            null,
            $ttl
        );

        $otp->refresh();

        $expiresAt = $otp->expires_at;
        $expected = now()->addSeconds($ttl);

        $this->assertEqualsWithDelta(
            $expected->timestamp,
            $expiresAt->timestamp,
            1
        );
    }

    public function test_verify_valid_otp(): void
    {
        $otp = $this->otpService->create(
            $this->user,
            $this->authenticationPurpose
        );

        $verified = $this->otpService->verify(
            $this->user,
            $otp->code,
            $this->authenticationPurpose
        );

        $this->assertTrue($verified);

        $otp->refresh();
        $this->assertTrue($otp->isUsed());
    }

    public function test_verify_without_marking_as_used(): void
    {
        $otp = $this->otpService->create(
            $this->user,
            $this->authenticationPurpose
        );

        $verified = $this->otpService->verify(
            $this->user,
            $otp->code,
            $this->authenticationPurpose,
            false
        );

        $this->assertTrue($verified);

        $otp->refresh();
        $this->assertFalse($otp->isUsed());
    }

    public function test_verify_invalid_code(): void
    {
        $this->otpService->create(
            $this->user,
            $this->authenticationPurpose
        );

        $verified = $this->otpService->verify(
            $this->user,
            'wrongcode',
            $this->authenticationPurpose
        );

        $this->assertFalse($verified);
    }

    public function test_verify_expired_otp(): void
    {
        $otp = $this->otpService->create(
            $this->user,
            $this->authenticationPurpose,
            null,
            1
        );

        sleep(2);

        $verified = $this->otpService->verify(
            $this->user,
            $otp->code,
            $this->authenticationPurpose
        );

        $this->assertFalse($verified);
    }

    public function test_verify_used_otp(): void
    {
        $otp = $this->otpService->create(
            $this->user,
            $this->authenticationPurpose
        );

        $this->otpService->verify($this->user, $otp->code, $this->authenticationPurpose);

        $verified = $this->otpService->verify(
            $this->user,
            $otp->code,
            $this->authenticationPurpose
        );

        $this->assertFalse($verified);
    }

    public function test_invalidate_otp(): void
    {
        $otp = $this->otpService->create(
            $this->user,
            $this->authenticationPurpose
        );

        $this->otpService->invalidate($this->user, $this->authenticationPurpose);

        $otp->refresh();
        $this->assertTrue($otp->isUsed());
    }

    public function test_get_all_for_identifier(): void
    {
        $otp1 = $this->otpService->create($this->user, $this->authenticationPurpose);
        $otp2 = $this->otpService->create($this->user, $this->passwordResetPurpose);

        $otps = $this->otpService->getAllFor($this->user, $this->authenticationPurpose);

        $this->assertCount(1, $otps);
        $this->assertSame($otp1->id, $otps->first()->id);
    }

    public function test_get_valid_for_identifier(): void
    {
        $otp1 = $this->otpService->create($this->user, $this->authenticationPurpose);
        $otp2 = $this->otpService->create($this->user, $this->authenticationPurpose);

        $this->otpService->verify($this->user, $otp1->code, $this->authenticationPurpose);

        $validOtps = $this->otpService->getValidFor($this->user, $this->authenticationPurpose);

        $this->assertCount(1, $validOtps);
        $this->assertSame($otp2->id, $validOtps->first()->id);
    }

    public function test_delete_expired_otps(): void
    {
        $otp1 = $this->otpService->create(
            $this->user,
            $this->authenticationPurpose,
            null,
            1
        );

        $otp2 = $this->otpService->create(
            $this->user,
            $this->authenticationPurpose
        );

        sleep(2);

        $deleted = $this->otpService->deleteExpired();

        $this->assertSame(1, $deleted);
        $this->assertTrue($otp1->fresh()->trashed());
        $this->assertFalse($otp2->fresh()->trashed());
    }

    public function test_get_attempts(): void
    {
        $otp = $this->otpService->create($this->user, $this->authenticationPurpose);

        for ($i = 0; $i < 3; $i++) {
            $this->otpService->incrementAttempts(
                $this->user,
                $otp->code,
                $this->authenticationPurpose
            );
        }

        $attempts = $this->otpService->getAttempts(
            $this->user,
            $otp->code,
            $this->authenticationPurpose
        );

        $this->assertSame(3, $attempts);
    }

    public function test_rate_limiting(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->otpService->create($this->user, $this->authenticationPurpose);
        }

        $isRateLimited = $this->otpService->isRateLimited(
            $this->user,
            $this->authenticationPurpose,
            5
        );

        $this->assertTrue($isRateLimited);
    }

    public function test_generate_recovery_codes(): void
    {
        $codes = $this->otpService->generateRecoveryCodes($this->user);

        $this->assertCount(10, $codes);

        foreach ($codes as $code) {
            $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $code);
        }
    }

    public function test_generate_secret(): void
    {
        $secret = $this->otpService->generateSecret();

        $this->assertIsString($secret);
        $this->assertMatchesRegularExpression('/^[A-Z2-7]{32}$/', $secret);
    }

    public function test_count_otps(): void
    {
        $this->otpService->create($this->user, $this->authenticationPurpose);
        $this->otpService->create($this->user, $this->passwordResetPurpose);
        $this->otpService->create($this->user, $this->authenticationPurpose);

        $count = $this->otpService->countFor($this->user, $this->authenticationPurpose);

        $this->assertSame(2, $count);
    }

    public function test_count_valid_otps(): void
    {
        $otp = $this->otpService->create($this->user, $this->authenticationPurpose);
        $this->otpService->create($this->user, $this->authenticationPurpose);

        $this->otpService->verify($this->user, $otp->code, $this->authenticationPurpose);

        $validCount = $this->otpService->countValidFor($this->user, $this->authenticationPurpose);

        $this->assertSame(1, $validCount);
    }

    public function test_find_valid_returns_correct_otp(): void
    {
        $otp = $this->otpService->create($this->user, $this->authenticationPurpose);

        $found = $this->otpService->findValid(
            $this->user,
            $otp->code,
            $this->authenticationPurpose
        );

        $this->assertNotNull($found);
        $this->assertSame($otp->id, $found->id);
    }

    public function test_create_with_totp(): void
    {
        $secret = $this->otpService->generateSecret();

        $otp = $this->otpService->createWithTOTP(
            $this->user,
            $this->twoFactorPurpose,
            $secret
        );

        $otp->refresh();

        $this->assertInstanceOf(Otp::class, $otp);
        $this->assertSame('two_factor', $otp->getPurpose()->getValue()->toArray()['value']);
        $this->assertSame(6, strlen($otp->code));
        $this->assertTrue(is_numeric($otp->code));
    }

    public function test_cannot_verify_exceeded_attempts(): void
    {
        $otp = $this->otpService->create($this->user, $this->authenticationPurpose);

        $maxAttempts = $this->authenticationPurpose->getMaxAttempts();

        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->otpService->incrementAttempts(
                $this->user,
                $otp->code,
                $this->authenticationPurpose
            );
        }

        $verified = $this->otpService->verify(
            $this->user,
            $otp->code,
            $this->authenticationPurpose
        );

        $this->assertFalse($verified);
        $this->assertTrue($otp->fresh()->trashed());
    }

    public function test_invalidate_all_otps_for_purpose(): void
    {
        $otp1 = $this->otpService->create($this->user, $this->authenticationPurpose);
        $otp2 = $this->otpService->create($this->user, $this->authenticationPurpose);
        $otp3 = $this->otpService->create($this->user, $this->passwordResetPurpose);

        $this->otpService->invalidate($this->user, $this->authenticationPurpose);

        $otp1->refresh();
        $otp2->refresh();
        $otp3->refresh();

        $this->assertTrue($otp1->isUsed());
        $this->assertTrue($otp2->isUsed());
        $this->assertFalse($otp3->isUsed());
    }

    public function test_custom_purpose_without_label(): void
    {
        $customPurpose = new PurposeVO(
            value: 'custom_operation',
            ttl: 600,
            maxAttempts: 5,
        );

        $otp = $this->otpService->create($this->user, $customPurpose);
        $otp->refresh();

        $purposeData = $otp->getPurpose()->getValue()->toArray();

        $this->assertSame('custom_operation', $purposeData['value']);
        $this->assertNull($purposeData['label']);
        $this->assertSame(600, $purposeData['ttl']);
        $this->assertSame(5, $purposeData['maxAttempts']);
    }

    public function test_custom_purpose_with_all_fields(): void
    {
        $customPurpose = new PurposeVO(
            value: 'api_access',
            label: 'Accès API',
            ttl: 180,
            maxAttempts: 3,
        );

        $otp = $this->otpService->create($this->user, $customPurpose);
        $otp->refresh();

        $purposeData = $otp->getPurpose()->getValue()->toArray();

        $this->assertSame('api_access', $purposeData['value']);
        $this->assertSame('Accès API', $purposeData['label']);
        $this->assertSame(180, $purposeData['ttl']);
        $this->assertSame(3, $purposeData['maxAttempts']);
    }
}
