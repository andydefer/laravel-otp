# Laravel OTP

> Système de mots de passe à usage unique (OTP) polymorphique pour applications Laravel

[![Latest Version](https://img.shields.io/packagist/v/andydefer/laravel-otp.svg?style=flat-square)](https://packagist.org/packages/andydefer/laravel-otp)
[![Total Downloads](https://img.shields.io/packagist/dt/andydefer/laravel-otp.svg?style=flat-square)](https://packagist.org/packages/andydefer/laravel-otp)
[![PHP Version](https://img.shields.io/packagist/php-v/andydefer/laravel-otp.svg?style=flat-square)](https://packagist.org/packages/andydefer/laravel-otp)
[![License](https://img.shields.io/packagist/l/andydefer/laravel-otp.svg?style=flat-square)](https://packagist.org/packages/andydefer/laravel-otp)

Un package Laravel complet pour gérer des mots de passe à usage unique (OTP) polymorphiques avec le pattern Repository, des DTOs, des Value Objects, le support TOTP (RFC 6238), des codes de récupération et une gestion des tentatives et du rate limiting.

---

## 📋 Table des matières

- [Fonctionnalités](#fonctionnalités)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Structure du package](#structure-du-package)
- [Utilisation](#utilisation)
  - [Créer un OTP avec un type prédéfini](#créer-un-otp-avec-un-type-prédéfini)
  - [Créer un OTP avec un type personnalisé](#créer-un-otp-avec-un-type-personnalisé)
  - [Créer un OTP avec un code personnalisé](#créer-un-otp-avec-un-code-personnalisé)
  - [Créer un OTP avec un TTL personnalisé](#créer-un-otp-avec-un-ttl-personnalisé)
  - [Vérifier un OTP](#vérifier-un-otp)
  - [Vérifier un OTP sans le marquer comme utilisé](#vérifier-un-otp-sans-le-marquer-comme-utilisé)
  - [Invalider un OTP](#invalider-un-otp)
  - [Récupérer tous les OTPs](#récupérer-tous-les-otps)
  - [Récupérer les OTPs valides](#récupérer-les-otps-valides)
  - [Compter les OTPs](#compter-les-otps)
  - [Supprimer les OTPs expirés](#supprimer-les-otps-expirés)
  - [Gestion des tentatives](#gestion-des-tentatives)
  - [TOTP (Time-based OTP)](#totp-time-based-otp)
  - [Codes de récupération](#codes-de-récupération)
  - [Rate Limiting](#rate-limiting)
- [Référence de l'API](#référence-de-lapi)
  - [OtpService](#otpservice)
  - [OtpRepository](#otprepository)
  - [OtpGenerator](#otpgenerator)
  - [PurposeVO](#purposevo)
- [Value Objects](#value-objects)
- [Structure de la base de données](#structure-de-la-base-de-données)
- [Tests](#tests)
- [Journal des modifications](#journal-des-modifications)
- [Contribuer](#contribuer)
- [Licence](#licence)

---

## ✨ Fonctionnalités

- ✅ **Polymorphisme** - OTPs attachables à n'importe quel modèle (User, Admin, etc.)
- ✅ **Types d'OTP flexibles** - Utilisez les types prédéfinis ou créez vos propres types via `PurposeVO`
- ✅ **6 types prédéfinis** - Authentification, Réinitialisation, Vérification email/téléphone, 2FA, Transaction
- ✅ **TOTP Support** - RFC 6238 compatible avec génération de secrets Base32
- ✅ **Codes de récupération** - Génération de codes de secours (configurables)
- ✅ **Gestion des tentatives** - Limitez les tentatives par OTP (configurable par type)
- ✅ **Expiration automatique** - TTL configurable par type d'OTP
- ✅ **Rate Limiting** - Limitez le nombre d'OTP par période (15 min par défaut)
- ✅ **DTO Pattern** - OtpRecord et OtpFilterRecord pour un typage fort
- ✅ **Value Objects** - PurposeVO pour une gestion flexible des types
- ✅ **Repository Pattern** - Séparation propre de la logique d'accès aux données
- ✅ **Multi-bases de données** - Support SQLite, MySQL, PostgreSQL pour les filtres JSON
- ✅ **Support des métadonnées** - Stockez des données supplémentaires au format JSON
- ✅ **Suppression douce** - SoftDeletes pour une suppression sécurisée
- ✅ **Tests complets** - Couverture complète des tests d'intégration

---

## 🚀 Prérequis

- PHP 8.2 ou supérieur
- Laravel 12.0, 13.0, 14.0 ou 15.0

---

## 📦 Installation

Installez le package via Composer :

```bash
composer require andydefer/laravel-otp
```

### Publier les migrations

```bash
php artisan vendor:publish --tag=Otp-migrations
```

### Exécuter les migrations

```bash
php artisan migrate
```

---

## ⚙️ Configuration

Le package est automatiquement découvert par Laravel. Aucune configuration supplémentaire n'est requise.

Si vous devez personnaliser le Service Provider, ajoutez-le manuellement dans `config/app.php` :

```php
'providers' => [
    // ...
    AndyDefer\LaravelOtp\OtpServiceProvider::class,
],
```

---

## 🏗️ Structure du package

```
laravel-otp/
├── src/
│   ├── OtpServiceProvider.php
│   ├── Models/
│   │   └── Otp.php
│   ├── Records/
│   │   ├── OtpRecord.php
│   │   └── OtpFilterRecord.php
│   ├── Repositories/
│   │   └── OtpRepository.php
│   ├── Services/
│   │   ├── OtpService.php
│   │   └── OtpGenerator.php
│   └── ValueObjects/
│       └── PurposeVO.php
├── database/
│   └── migrations/
│       └── create_otps_table.php
└── tests/
    ├── Integration/
    │   └── Services/
    │       └── OtpServiceIntegrationTest.php
    ├── Fixtures/
    └── IntegrationTestCase.php
```

---

## 📖 Utilisation

### Créer un OTP avec un type prédéfini

Le package fournit des constantes prédéfinies via la classe `PurposeVO` :

```php
use AndyDefer\LaravelOtp\Services\OtpService;
use AndyDefer\LaravelOtp\ValueObjects\PurposeVO;

class AuthController extends Controller
{
    public function sendOtp(OtpService $otpService)
    {
        $user = auth()->user();

        // Créer un PurposeVO pour l'authentification
        $purpose = new PurposeVO(
            value: 'authentication',
            label: 'Authentification',
            ttl: 300,
            maxAttempts: 3
        );

        $otp = $otpService->create(
            identifier: $user,
            purpose: $purpose
        );

        // Envoyer le code par SMS ou email
        Notification::send($user, new OtpNotification($otp->code));

        return response()->json([
            'message' => 'OTP envoyé',
            'expires_in' => $otp->expires_at->diffInSeconds(now())
        ]);
    }
}
```

### Créer un OTP avec un type personnalisé

Vous pouvez créer vos propres types d'OTP :

```php
$customPurpose = new PurposeVO(
    value: 'api_access',
    label: 'Accès API',
    ttl: 180,        // 3 minutes
    maxAttempts: 3
);

$otp = $otpService->create($user, $customPurpose);
```

### Créer un OTP avec un code personnalisé

```php
$purpose = new PurposeVO('password_reset', 'Réinitialisation', 900, 5);

$otp = $otpService->create(
    identifier: $user,
    purpose: $purpose,
    code: 'SECURE-CODE-123' // Code personnalisé
);
```

### Créer un OTP avec un TTL personnalisé

```php
$purpose = new PurposeVO('two_factor', 'Double authentification', 120, 3);

// Le TTL du purpose est utilisé par défaut
$otp = $otpService->create($user, $purpose);

// Ou surcharge avec un TTL personnalisé
$otp = $otpService->create(
    identifier: $user,
    purpose: $purpose,
    code: null,
    ttl: 60 // 1 minute
);
```

### Vérifier un OTP

```php
$purpose = new PurposeVO('authentication', 'Authentification', 300, 3);

$valid = $otpService->verify(
    identifier: $user,
    code: $request->input('code'),
    purpose: $purpose
);

if ($valid) {
    // OTP valide, connecter l'utilisateur
    return response()->json(['message' => 'Authentification réussie']);
}

return response()->json(['message' => 'Code invalide ou expiré'], 400);
```

### Vérifier un OTP sans le marquer comme utilisé

```php
// Permet de vérifier un OTP sans le consommer
// Utile pour les pré-vérifications
$valid = $otpService->verify(
    identifier: $user,
    code: $code,
    purpose: $purpose,
    markAsUsed: false
);
```

### Invalider un OTP

```php
// Invalide tous les OTPs d'un type pour un identifiant
$purpose = new PurposeVO('authentication', 'Authentification', 300, 3);
$otpService->invalidate($user, $purpose);
```

### Récupérer tous les OTPs

```php
// Récupère tous les OTPs (valides et invalides) pour un identifiant et un type
$purpose = new PurposeVO('authentication', 'Authentification', 300, 3);
$otps = $otpService->getAllFor($user, $purpose);
```

### Récupérer les OTPs valides

```php
// Récupère uniquement les OTPs valides (non expirés et non utilisés)
$purpose = new PurposeVO('authentication', 'Authentification', 300, 3);
$validOtps = $otpService->getValidFor($user, $purpose);
```

### Compter les OTPs

```php
$purpose = new PurposeVO('authentication', 'Authentification', 300, 3);

// Compter tous les OTPs
$count = $otpService->countFor($user, $purpose);

// Compter les OTPs valides
$validCount = $otpService->countValidFor($user, $purpose);

// Compter les OTPs expirés
$expiredCount = $otpService->countExpiredFor($user, $purpose);
```

### Supprimer les OTPs expirés

```php
// Supprime tous les OTPs expirés de la base de données
$deletedCount = $otpService->deleteExpired();
```

### Gestion des tentatives

```php
$purpose = new PurposeVO('authentication', 'Authentification', 300, 3);

// Incrémenter les tentatives pour un OTP
$otpService->incrementAttempts($user, $code, $purpose);

// Récupérer le nombre de tentatives
$attempts = $otpService->getAttempts($user, $code, $purpose);
```

### TOTP (Time-based OTP)

```php
// 1. Générer un secret pour l'utilisateur
$secret = $otpService->generateSecret();

// 2. Stocker le secret chez l'utilisateur
$user->otp_secret = $secret;
$user->save();

// 3. Créer un OTP basé sur TOTP
$purpose = new PurposeVO('two_factor', 'Double authentification', 120, 3);

$otp = $otpService->createWithTOTP(
    identifier: $user,
    purpose: $purpose,
    secret: $secret
);

// Le code est généré automatiquement selon RFC 6238
// Code valide pendant 30 secondes (intervalle TOTP standard)
```

### Codes de récupération

```php
// Générer 10 codes de récupération de 8 caractères
$recoveryCodes = $otpService->generateRecoveryCodes(
    identifier: $user,
    count: 10,
    length: 8
);

// Stocker les codes hashés chez l'utilisateur
$user->recovery_codes = array_map('hash', $recoveryCodes);
$user->save();

// Afficher les codes à l'utilisateur
return response()->json(['recovery_codes' => $recoveryCodes]);
```

### Rate Limiting

```php
$purpose = new PurposeVO('authentication', 'Authentification', 300, 3);

// Vérifier si l'utilisateur a dépassé la limite
$isRateLimited = $otpService->isRateLimited(
    identifier: $user,
    purpose: $purpose,
    limit: 5,    // 5 tentatives
    window: now()->subMinutes(15)  // sur 15 minutes
);

if ($isRateLimited) {
    return response()->json([
        'message' => 'Trop de tentatives. Veuillez réessayer plus tard.'
    ], 429);
}
```

---

## 📚 Référence de l'API

### OtpService

| Méthode | Description | Retourne |
|---------|-------------|----------|
| `create(Model $identifier, PurposeVO $purpose, ?string $code = null, ?int $ttl = null)` | Créer un OTP | `Model` |
| `createWithTOTP(Model $identifier, PurposeVO $purpose, string $secret, ?int $ttl = null)` | Créer un OTP TOTP | `Model` |
| `verify(Model $identifier, string $code, PurposeVO $purpose, bool $markAsUsed = true)` | Vérifier un OTP | `bool` |
| `findValid(Model $identifier, string $code, PurposeVO $purpose)` | Trouver un OTP valide | `?Model` |
| `invalidate(Model $identifier, PurposeVO $purpose)` | Invalider tous les OTPs d'un type | `void` |
| `getAllFor(Model $identifier, PurposeVO $purpose)` | Récupérer tous les OTPs | `Collection` |
| `getValidFor(Model $identifier, PurposeVO $purpose)` | Récupérer les OTPs valides | `Collection` |
| `deleteExpired()` | Supprimer les OTPs expirés | `int` |
| `getAttempts(Model $identifier, string $code, PurposeVO $purpose)` | Récupérer les tentatives | `int` |
| `incrementAttempts(Model $identifier, string $code, PurposeVO $purpose)` | Incrémenter les tentatives | `void` |
| `countFor(Model $identifier, PurposeVO $purpose)` | Compter les OTPs | `int` |
| `countValidFor(Model $identifier, PurposeVO $purpose)` | Compter les OTPs valides | `int` |
| `countExpiredFor(Model $identifier, PurposeVO $purpose)` | Compter les OTPs expirés | `int` |
| `generateRecoveryCodes(Model $identifier, int $count = 10, int $length = 8)` | Générer des codes de récupération | `array` |
| `generateSecret()` | Générer un secret TOTP | `string` |
| `isRateLimited(Model $identifier, PurposeVO $purpose, int $limit = 5, ?CarbonInterface $window = null)` | Vérifier le rate limiting | `bool` |

### OtpRepository

| Méthode | Description | Retourne |
|---------|-------------|----------|
| `incrementAttempts(int $id)` | Incrémenter les tentatives d'un OTP | `Otp` |
| `markAsUsed(int $id)` | Marquer un OTP comme utilisé | `Otp` |
| `delete(int $id)` | Supprimer un OTP (hérité) | `bool` |
| `find(int $id)` | Trouver un OTP par ID (hérité) | `?Otp` |
| `count(AbstractRecord $criteria)` | Compter les OTPs (hérité) | `int` |

### OtpGenerator

| Méthode | Description | Retourne |
|---------|-------------|----------|
| `generate(int $length = 6, bool $numeric = true)` | Générer un code OTP | `string` |
| `generateTOTP(string $secret, int $digits = 6)` | Générer un code TOTP | `string` |
| `generateSecret(int $length = 32)` | Générer un secret Base32 | `string` |
| `generateRecoveryCodes(int $count = 10, int $length = 8)` | Générer des codes de récupération | `array` |

### PurposeVO

| Méthode | Description | Retourne |
|---------|-------------|----------|
| `__construct(string $value, ?string $label, ?int $ttl, ?int $maxAttempts)` | Créer un PurposeVO | - |
| `getValue()` | Récupérer les données | `StrictDataObject` |
| `getLabel()` | Récupérer le label | `?string` |
| `getTtl()` | Récupérer le TTL | `?int` |
| `getMaxAttempts()` | Récupérer les tentatives max | `?int` |
| `__toString()` | Convertir en JSON | `string` |

---

## 🎯 Value Objects

Le package supporte les Value Objects suivants :

| Value Object | Description | Exemple |
|--------------|-------------|---------|
| `PurposeVO` | Type d'OTP avec label, TTL et maxAttempts | `new PurposeVO('auth', 'Auth', 300, 3)` |
| `DateTimeVO` | Date/heure | `new DateTimeVO('2024-01-01 12:00:00')` |
| `StrictDataObject` | Métadonnées typées | `StrictDataObject::from(['ip' => '127.0.0.1'])` |

### Accesseurs dans le modèle Otp

```php
$otp = Otp::find(1);

// Accès sous forme de Value Objects
$purpose = $otp->getPurpose();        // PurposeVO
$createdAt = $otp->getCreatedAt();    // DateTimeVO
$updatedAt = $otp->getUpdatedAt();    // DateTimeVO
$expiresAt = $otp->getExpiresAt();    // DateTimeVO
$usedAt = $otp->getUsedAt();          // DateTimeVO
$deletedAt = $otp->getDeletedAt();    // DateTimeVO
$metadata = $otp->getMetadata();      // StrictDataObject

// Méthodes utilitaires
$otp->isValid();      // bool
$otp->isUsed();       // bool
$otp->isExpired();    // bool

// Relations
$identifier = $otp->identifier;  // User, Admin, etc.
```

---

## 📝 Structure de la base de données

```sql
CREATE TABLE otps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier_type VARCHAR(255) NOT NULL,    -- Type de l'identifiant (morph)
    identifier_id BIGINT UNSIGNED NOT NULL,   -- ID de l'identifiant
    code VARCHAR(255) NOT NULL,               -- Code OTP
    purpose JSON NOT NULL,                    -- Type d'OTP (JSON)
    expires_at TIMESTAMP NOT NULL,            -- Date d'expiration
    used_at TIMESTAMP NULL,                   -- Date d'utilisation
    attempts INT DEFAULT 0,                   -- Nombre de tentatives
    metadata JSON NULL,                       -- Métadonnées
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_identifier (identifier_type, identifier_id),
    INDEX idx_code (code),
    INDEX idx_expires_at (expires_at),
    INDEX idx_used_at (used_at)
);
```

### Structure du champ `purpose` (JSON)

```json
{
    "value": "authentication",
    "label": "Authentification",
    "ttl": 300,
    "maxAttempts": 3
}
```

---

## 🔍 Exemple complet

```php
use AndyDefer\LaravelOtp\Services\OtpService;
use AndyDefer\LaravelOtp\ValueObjects\PurposeVO;

class AuthController extends Controller
{
    public function __construct(
        private readonly OtpService $otpService
    ) {}

    public function requestOtp(Request $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();

        $purpose = new PurposeVO(
            value: 'authentication',
            label: 'Authentification',
            ttl: 300,
            maxAttempts: 3
        );

        // Rate limiting
        if ($this->otpService->isRateLimited($user, $purpose)) {
            return response()->json([
                'message' => 'Too many attempts. Please try again later.'
            ], 429);
        }

        $otp = $this->otpService->create($user, $purpose);

        // Envoyer par SMS ou email
        Notification::send($user, new OtpNotification($otp->code));

        return response()->json([
            'message' => 'OTP sent',
            'expires_in' => $otp->expires_at->diffInSeconds(now())
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();

        $purpose = new PurposeVO(
            value: 'authentication',
            label: 'Authentification',
            ttl: 300,
            maxAttempts: 3
        );

        $valid = $this->otpService->verify(
            identifier: $user,
            code: $request->code,
            purpose: $purpose
        );

        if (!$valid) {
            $this->otpService->incrementAttempts(
                $user,
                $request->code,
                $purpose
            );

            return response()->json([
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        auth()->login($user);

        return response()->json([
            'message' => 'Authenticated successfully',
            'user' => $user
        ]);
    }

    public function enableTwoFactor(Request $request)
    {
        $user = $request->user();

        // Générer un secret TOTP
        $secret = $this->otpService->generateSecret();
        $user->otp_secret = $secret;
        $user->save();

        // Générer des codes de récupération
        $recoveryCodes = $this->otpService->generateRecoveryCodes($user, 10, 8);

        return response()->json([
            'secret' => $secret,
            'recovery_codes' => $recoveryCodes,
            'qr_code_url' => $this->generateQrCode($user->email, $secret)
        ]);
    }

    public function verifyTwoFactor(Request $request)
    {
        $user = $request->user();

        $purpose = new PurposeVO(
            value: 'two_factor',
            label: 'Double authentification',
            ttl: 120,
            maxAttempts: 3
        );

        $otp = $this->otpService->createWithTOTP(
            identifier: $user,
            purpose: $purpose,
            secret: $user->otp_secret
        );

        $valid = $this->otpService->verify(
            identifier: $user,
            code: $request->code,
            purpose: $purpose
        );

        if (!$valid) {
            return response()->json(['message' => 'Invalid 2FA code'], 400);
        }

        $user->two_factor_enabled = true;
        $user->save();

        return response()->json(['message' => '2FA enabled successfully']);
    }

    public function customPurpose()
    {
        $user = User::find(1);

        // Créer un type d'OTP personnalisé
        $customPurpose = new PurposeVO(
            value: 'api_access',
            label: 'Accès API',
            ttl: 180,
            maxAttempts: 3
        );

        $otp = $this->otpService->create($user, $customPurpose);

        return $otp;
    }
}
```

---

## 🧪 Tests

### Exécuter les tests

```bash
composer test
```

### Exécuter uniquement les tests unitaires

```bash
composer test-unit
```

### Exécuter uniquement les tests d'intégration

```bash
composer test-integration
```

### Configuration des tests

Le package utilise `orchestra/testbench` pour les tests d'intégration avec une base de données SQLite en mémoire.

---

## 🔧 Développement

### Style de code

```bash
./vendor/bin/pint
```

### Analyse statique

```bash
./vendor/bin/phpstan analyse
./vendor/bin/psalm
```

---

## 📄 Journal des modifications

Veuillez consulter le [CHANGELOG](CHANGELOG.md) pour plus d'informations sur les modifications récentes.

---

## 🤝 Contribuer

Veuillez consulter [CONTRIBUTING](CONTRIBUTING.md) pour plus de détails.

### Flux de développement

1. Forkez le dépôt
2. Créez une branche de fonctionnalité (`git checkout -b feature/amazing-feature`)
3. Apportez vos modifications
4. Exécutez les tests (`composer test`)
5. Committez vos modifications (`git commit -m 'Ajouter une fonctionnalité géniale'`)
6. Poussez vers la branche (`git push origin feature/amazing-feature`)
7. Ouvrez une Pull Request

---

## 📦 Dépendances

- [`andydefer/php-vo`](https://github.com/andydefer/php-vo) - Value Objects (DateTimeVO)
- [`andydefer/laravel-repository`](https://github.com/andydefer/laravel-repository) - Implémentation du pattern Repository
- [`andydefer/domain-structures`](https://github.com/andydefer/domain-structures) - Structures de domaine (AbstractRecord, AbstractData, AbstractValueObject)

---

## 👨‍💻 Auteur

**Andy Kani**
- GitHub: [@andydefer](https://github.com/andydefer)
- Email: andykanidimbu@gmail.com

---
## ⭐ Support

Si vous trouvez ce package utile, n'hésitez pas à lui donner une ⭐ sur GitHub !

---

## 🙏 Remerciements

- Framework Laravel
- Tous les contributeurs et utilisateurs de ce package

---

**Construit avec ❤️ pour la communauté Laravel**
