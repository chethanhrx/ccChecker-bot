<?php

declare(strict_types=1);

namespace CcCheckerBot\Tests\Unit;

use CcCheckerBot\Bot\UpdateDispatcher;
use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Tests\Support\FakeTelegramClient;
use PHPUnit\Framework\TestCase;

final class UpdateDispatcherTest extends TestCase
{
    private string $directory;
    private UserStore $users;
    private FakeTelegramClient $telegram;
    private UpdateDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/flintbot-dispatch-' . bin2hex(random_bytes(6));
        $this->users = new UserStore($this->directory . '/users.json');
        $this->telegram = new FakeTelegramClient();
        $this->dispatcher = new UpdateDispatcher($this->telegram, $this->users);
    }

    protected function tearDown(): void
    {
        $path = $this->directory . '/users.json';
        if (is_file($path)) {
            unlink($path);
        }
        if (is_dir($this->directory)) {
            rmdir($this->directory);
        }
    }

    public function testStartShowsWelcomeKeyboard(): void
    {
        $this->dispatcher->dispatch([
            'message' => [
                'chat' => ['id' => 77],
                'from' => ['id' => 77, 'first_name' => 'Ada'],
                'text' => '/start',
            ],
        ]);

        self::assertCount(1, $this->telegram->messages);
        self::assertStringContainsString('Welcome to FlintBot, Ada', $this->telegram->messages[0]['text']);
        self::assertArrayHasKey('inline_keyboard', $this->telegram->messages[0]['keyboard']);
    }

    public function testToolsAreGatedUntilRegistration(): void
    {
        $this->dispatcher->dispatch([
            'message' => [
                'chat' => ['id' => 77],
                'from' => ['id' => 77, 'first_name' => 'Ada'],
                'text' => '/tools',
            ],
        ]);

        self::assertStringContainsString('register first', $this->telegram->messages[0]['text']);
    }

    public function testRegistrationCallbackSavesProfileAndAcknowledgesButton(): void
    {
        $this->dispatcher->dispatch([
            'callback_query' => [
                'id' => 'callback-1',
                'data' => 'register',
                'from' => ['id' => 77, 'first_name' => 'Ada', 'username' => 'ada'],
                'message' => ['chat' => ['id' => 77]],
            ],
        ]);

        self::assertSame('callback-1', $this->telegram->answeredCallbacks[0]);
        self::assertSame('77', $this->users->find(77)['telegram_id']);
        self::assertStringContainsString('Registration complete', $this->telegram->messages[0]['text']);
    }

    public function testRegisteredUserCanRunMockDemo(): void
    {
        $this->users->register(['id' => 77, 'first_name' => 'Ada']);

        $this->dispatcher->dispatch([
            'message' => [
                'chat' => ['id' => 77],
                'from' => ['id' => 77, 'first_name' => 'Ada'],
                'text' => '/authdemo',
            ],
        ]);

        self::assertStringContainsString('SIMULATED_OK', $this->telegram->messages[0]['text']);
    }
}
