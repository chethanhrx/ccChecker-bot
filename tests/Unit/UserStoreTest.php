<?php

declare(strict_types=1);

namespace CcCheckerBot\Tests\Unit;

use CcCheckerBot\Storage\UserStore;
use PHPUnit\Framework\TestCase;

final class UserStoreTest extends TestCase
{
    private string $directory;
    private string $path;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/flintbot-' . bin2hex(random_bytes(6));
        $this->path = $this->directory . '/users.json';
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }
        if (is_dir($this->directory)) {
            rmdir($this->directory);
        }
    }

    public function testRegisterAndFindProfile(): void
    {
        $store = new UserStore($this->path);
        $saved = $store->register([
            'id' => 12345,
            'username' => 'demo_user',
            'first_name' => 'Demo',
            'last_name' => 'User',
        ]);

        self::assertSame('12345', $saved['telegram_id']);
        self::assertSame('demo_user', $saved['username']);
        self::assertSame('12345', $store->find(12345)['telegram_id']);
    }

    public function testRegistrationKeepsOriginalRegistrationTimestamp(): void
    {
        $store = new UserStore($this->path);
        $first = $store->register(['id' => 12345, 'first_name' => 'First']);
        $second = $store->register(['id' => 12345, 'first_name' => 'Updated']);

        self::assertSame($first['registered_at'], $second['registered_at']);
        self::assertSame('Updated', $second['first_name']);
    }

    public function testDeleteRemovesProfile(): void
    {
        $store = new UserStore($this->path);
        $store->register(['id' => 12345, 'first_name' => 'Demo']);

        self::assertTrue($store->delete(12345));
        self::assertNull($store->find(12345));
        self::assertFalse($store->delete(12345));
    }

    public function testMissingProfileReturnsNull(): void
    {
        self::assertNull((new UserStore($this->path))->find(999));
    }
}
