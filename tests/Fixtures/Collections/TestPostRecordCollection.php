<?php

declare(strict_types=1);

namespace AndyDefer\LaravelOtp\Tests\Fixtures\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\LaravelOtp\Tests\Fixtures\Records\TestPostRecord;

final class TestPostRecordCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(TestPostRecord::class);
    }
}
