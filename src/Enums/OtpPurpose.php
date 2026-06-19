<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\Enums;

enum OtpPurpose: string
{
    case AUTHENTICATION = 'authentication';
    case PASSWORD_RESET = 'password_reset';
    case EMAIL_VERIFICATION = 'email_verification';
    case PHONE_VERIFICATION = 'phone_verification';
    case TWO_FACTOR = 'two_factor';
    case TRANSACTION = 'transaction';

    public function getLabel(): string
    {
        return match ($this) {
            self::AUTHENTICATION => 'Authentification',
            self::PASSWORD_RESET => 'Réinitialisation du mot de passe',
            self::EMAIL_VERIFICATION => 'Vérification email',
            self::PHONE_VERIFICATION => 'Vérification téléphone',
            self::TWO_FACTOR => 'Double authentification',
            self::TRANSACTION => 'Transaction sécurisée',
        };
    }

    public function getTtl(): int
    {
        return match ($this) {
            self::AUTHENTICATION => 300,  // 5 minutes
            self::PASSWORD_RESET => 900,  // 15 minutes
            self::EMAIL_VERIFICATION => 3600, // 1 heure
            self::PHONE_VERIFICATION => 600,  // 10 minutes
            self::TWO_FACTOR => 120,       // 2 minutes
            self::TRANSACTION => 180,      // 3 minutes
        };
    }

    public function getMaxAttempts(): int
    {
        return match ($this) {
            self::AUTHENTICATION => 3,
            self::PASSWORD_RESET => 5,
            self::EMAIL_VERIFICATION => 10,
            self::PHONE_VERIFICATION => 5,
            self::TWO_FACTOR => 3,
            self::TRANSACTION => 3,
        };
    }
}
