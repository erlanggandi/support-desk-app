<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    /**
     * Lingkungan Windows bisa membawa variabel proses (mis. APP_ENV=local)
     * yang menang lewat $_SERVER pada Dotenv adapter dan menimpa phpunit.xml.
     * Paksa nilai testing sebelum aplikasi di-boot.
     */
    private const TESTING_ENV = [
        'APP_ENV' => 'testing',
        'BCRYPT_ROUNDS' => '4',
        'CACHE_STORE' => 'array',
        'DB_CONNECTION' => 'sqlite',
        'DB_DATABASE' => ':memory:',
        'MAIL_MAILER' => 'array',
        'QUEUE_CONNECTION' => 'sync',
        'SESSION_DRIVER' => 'array',
    ];

    protected function setUp(): void
    {
        foreach (self::TESTING_ENV as $key => $value) {
            $_SERVER[$key] = $value;
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }

        parent::setUp();
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
