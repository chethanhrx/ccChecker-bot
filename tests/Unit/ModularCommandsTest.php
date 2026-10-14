<?php

declare(strict_types=1);

namespace CcCheckerBot\Tests\Unit;

use CcCheckerBot\Bot\UpdateDispatcher;
use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Tests\Support\FakeTelegramClient;
use PHPUnit\Framework\TestCase;

final class ModularCommandsTest extends TestCase
{
    private string $directory;
    private UserStore $users;
    private FakeTelegramClient $telegram;
    private UpdateDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/flintbot-commands-' . bin2hex(random_bytes(6));
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

    /** @param list<string> $texts */
    private function send(string $text): void
    {
        $this->dispatcher->dispatch([
            'message' => [
                'chat' => ['id' => 42],
                'from' => ['id' => 42, 'first_name' => 'Test', 'username' => 'tester'],
                'text' => $text,
            ],
        ]);
    }

    public function testCommandDirectoryAndIdentityCommands(): void
    {
        $this->send('/cmd');
        self::assertStringContainsString('/redeem DEMO-STARTER', $this->telegram->messages[0]['text']);

        $this->send('/id');
        self::assertStringContainsString('<code>42</code>', $this->telegram->messages[1]['text']);

        $this->send('/ping');
        self::assertStringContainsString('Pong', $this->telegram->messages[2]['text']);

        $this->send('/info');
        self::assertStringContainsString('FlintPHP', $this->telegram->messages[3]['text']);
    }

    public function testDemoPurchaseAndRedeemAreClearlySimulated(): void
    {
        $this->send('/register');
        $this->send('/buy');
        self::assertStringContainsString('no payment is taken', strtolower($this->telegram->messages[1]['text']));

        $this->send('/redeem DEMO-STARTER');
        self::assertStringContainsString('Demo code redeemed', $this->telegram->messages[2]['text']);
        self::assertStringContainsString('10', $this->telegram->messages[2]['text']);

        $this->send('/redeem DEMO-STARTER');
        self::assertStringContainsString('already been redeemed', $this->telegram->messages[3]['text']);

        $this->send('/redeem NOT-A-CODE');
        self::assertStringContainsString('Unknown demo code', $this->telegram->messages[4]['text']);
    }

    public function testBalanceCommandShowsPersistentDemoCredits(): void
    {
        $this->send('/register');
        $this->send('/redeem DEMO-STARTER');
        $this->send('/balance');

        self::assertStringContainsString('Available demo credits', $this->telegram->messages[2]['text']);
        self::assertStringContainsString('<b>10</b>', $this->telegram->messages[2]['text']);
        self::assertStringContainsString('DEMO-STARTER', $this->telegram->messages[2]['text']);
    }

    public function testBalanceInlineButtonRoutesToBalanceCommand(): void
    {
        $this->send('/register');
        $this->send('/redeem DEMO-STARTER');

        $this->dispatcher->dispatch([
            'callback_query' => [
                'id' => 'callback-1',
                'from' => ['id' => 42, 'first_name' => 'Test', 'username' => 'tester'],
                'message' => ['chat' => ['id' => 42]],
                'data' => 'balance',
            ],
        ]);

        self::assertSame(['callback-1'], $this->telegram->answeredCallbacks);
        self::assertStringContainsString('Available demo credits', $this->telegram->messages[2]['text']);
        self::assertStringContainsString('<b>10</b>', $this->telegram->messages[2]['text']);
    }

    public function testSyntheticCheckWorksButRejectsCardLikeInput(): void
    {
        $this->send('/chk DEMO-1234');
        self::assertStringContainsString('SIMULATED_ONLY', $this->telegram->messages[0]['text']);

        $this->send('/chk 4111111111111111|12|30|123');
        self::assertStringContainsString('does not accept payment-card numbers', $this->telegram->messages[1]['text']);

        $this->send('/bin');
        self::assertStringContainsString('BIN lookup is disabled', $this->telegram->messages[2]['text']);
    }


    public function testStandaloneFileCommandIsAutoDiscoveredAndUsesCurrentChat(): void
    {
        $this->dispatcher->dispatch([
            'message' => [
                'chat' => ['id' => -100123],
                'from' => ['id' => 42, 'first_name' => 'Group Tester'],
                'text' => '/hello',
            ],
        ]);

        self::assertSame(-100123, $this->telegram->messages[0]['chat_id']);
        self::assertStringContainsString('Group Tester', $this->telegram->messages[0]['text']);
        self::assertStringContainsString('auto-discovered', $this->telegram->messages[0]['text']);
    }

    public function testRegisterCommandCreatesProfile(): void
    {
        $this->send('/register');
        self::assertNotNull($this->users->find(42));
        self::assertStringContainsString('Registration complete', $this->telegram->messages[0]['text']);
    }
}
