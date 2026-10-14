<?php
declare(strict_types=1);

namespace CcCheckerBot\Tests\Support;

use Throwable;

/** Minimal dependency-free base for this repository's focused tests. */
abstract class TestCase
{
    private static int $assertions = 0;
    protected function setUp(): void {}
    protected function tearDown(): void {}

    public static function resetAssertions(): void { self::$assertions = 0; }
    public static function assertionCount(): int { return self::$assertions; }

    public static function execute(string $class, string $method): ?Throwable
    {
        $test = new $class();
        $ready = false;
        $failure = null;
        try {
            $test->setUp();
            $ready = true;
            $test->{$method}();
        } catch (Throwable $exception) {
            $failure = $exception;
        } finally {
            if ($ready) {
                try { $test->tearDown(); } catch (Throwable $exception) { $failure ??= $exception; }
            }
        }
        return $failure;
    }

    public static function assertSame(mixed $expected, mixed $actual, string $message = ''): void
    {
        self::check($expected === $actual, $message ?: 'Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
    public static function assertStringContainsString(string $needle, string $haystack, string $message = ''): void
    {
        self::check(str_contains($haystack, $needle), $message ?: 'Expected string to contain ' . var_export($needle, true));
    }
    public static function assertNotNull(mixed $actual, string $message = ''): void
    {
        self::check($actual !== null, $message ?: 'Expected non-null value.');
    }
    public static function assertCount(int $expectedCount, mixed $haystack, string $message = ''): void
    {
        self::check(is_countable($haystack) && count($haystack) === $expectedCount, $message ?: 'Unexpected count.');
    }
    public static function assertArrayHasKey(int|string $key, array $array, string $message = ''): void
    {
        self::check(array_key_exists($key, $array), $message ?: 'Expected array key ' . var_export($key, true));
    }
    public static function assertTrue(bool $condition, string $message = ''): void
    {
        self::check($condition, $message ?: 'Expected true.');
    }
    public static function assertFalse(bool $condition, string $message = ''): void
    {
        self::check(!$condition, $message ?: 'Expected false.');
    }
    public static function assertNull(mixed $actual, string $message = ''): void
    {
        self::check($actual === null, $message ?: 'Expected null.');
    }
    private static function check(bool $condition, string $message): void
    {
        self::$assertions++;
        if (!$condition) throw new \RuntimeException($message);
    }
}
