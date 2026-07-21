<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\Tests\Fixtures\Models;

use AndyDefer\LaravelOtp\Tests\Fixtures\Enums\TestUserRole;
use AndyDefer\LaravelOtp\Tests\Fixtures\Enums\TestUserStatus;
use Illuminate\Database\Eloquent\Model;

class TestUser extends Model
{
    protected $table = 'test_users';

    protected $fillable = [
        'name',
        'email',
        'status',
        'role',
        'age',
        'metadata',
    ];

    protected $casts = [
        'status' => TestUserStatus::class,
        'role' => TestUserRole::class,
        'metadata' => 'array',
    ];
}
