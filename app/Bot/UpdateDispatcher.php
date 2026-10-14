<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot;

use CcCheckerBot\Bot\Commands\BinCommand;
use CcCheckerBot\Bot\Commands\BuyCommand;
use CcCheckerBot\Bot\Commands\ChkCommand;
use CcCheckerBot\Bot\Commands\CmdCommand;
use CcCheckerBot\Bot\Commands\CommandHandler;
use CcCheckerBot\Bot\Commands\IdCommand;
use CcCheckerBot\Bot\Commands\InfoCommand;
use CcCheckerBot\Bot\Commands\PingCommand;
use CcCheckerBot\Bot\Commands\RedeemCommand;
use CcCheckerBot\Bot\Commands\RegisterCommand;
use CcCheckerBot\Bot\Commands\StartCommand;
use CcCheckerBot\Demo\MockScenario;
use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;
use CcCheckerBot\UI\Keyboards;

final class UpdateDispatcher
{
    /** @var array<string, CommandHandler> */
    private array $commands;

    public function __construct(
        private readonly TelegramClientInterface $telegram,
        private readonly UserStore $users,
        private readonly MockScenario $scenarios = new MockScenario(),
    ) {
        $this->commands = [
            '/start' => new StartCommand(),
            '/cmd' => new CmdCommand(),
            '/help' => new CmdCommand(),
            '/id' => new IdCommand(),
            '/info' => new InfoCommand(),
            '/register' => new RegisterCommand(),
            '/buy' => new BuyCommand(),
            '/redeem' => new RedeemCommand(),
            '/chk' => new ChkCommand(),
            '/bin' => new BinCommand(),
            '/ping' => new PingCommand(),
        ];
    }

    /** @param array<string, mixed> $update */
    public function dispatch(array $update): void
    {
        if (is_array($update['message'] ?? null)) {
            $this->dispatchMessage($update['message']);
            return;
        }

        if (is_array($update['callback_query'] ?? null)) {
            $this->dispatchCallback($update['callback_query']);
        }
    }

    /** @param array<string, mixed> $message */
    private function dispatchMessage(array $message): void
    {
        $chatId = $message['chat']['id'] ?? null;
        $sender = is_array($message['from'] ?? null) ? $message['from'] : [];
        $text = trim((string) ($message['text'] ?? ''));
        if ($chatId === null || $text === '') {
            return;
        }

        $parts = preg_split('/\s+/', $text, 2) ?: [];
        $command = strtolower((string) ($parts[0] ?? ''));
        $command = explode('@', $command, 2)[0];
        $arguments = trim((string) ($parts[1] ?? ''));

        if (isset($this->commands[$command])) {
            $this->commands[$command]->handle($this->telegram, $this->users, $chatId, $sender, $arguments);
            return;
        }

        switch ($command) {
            case '/account':
                $this->account($chatId, $sender);
                return;
            case '/deleteaccount':
                $this->deleteAccount($chatId, $sender);
                return;
            case '/tools':
                $this->tools($chatId, $sender);
                return;
            case '/authdemo':
                $this->demo($chatId, 'auth', $sender);
                return;
            case '/chargedemo':
                $this->demo($chatId, 'charge', $sender);
                return;
            default:
                $this->telegram->sendMessage($chatId, 'Unknown command. Use /cmd to see the command list.', Keyboards::home());
        }
    }

    /** @param array<string, mixed> $callback */
    private function dispatchCallback(array $callback): void
    {
        $callbackId = (string) ($callback['id'] ?? '');
        if ($callbackId !== '') {
            try {
                $this->telegram->answerCallback($callbackId);
            } catch (\Throwable $exception) {
                error_log('[telegram-bot] Callback acknowledgement failed: ' . $exception->getMessage());
            }
        }

        $sender = is_array($callback['from'] ?? null) ? $callback['from'] : [];
        $message = is_array($callback['message'] ?? null) ? $callback['message'] : [];
        $chatId = $message['chat']['id'] ?? null;
        if ($chatId === null) {
            return;
        }

        switch ((string) ($callback['data'] ?? '')) {
            case 'home':
                (new StartCommand())->handle($this->telegram, $this->users, $chatId, $sender, '');
                break;
            case 'register':
                (new RegisterCommand())->handle($this->telegram, $this->users, $chatId, $sender, '');
                break;
            case 'account':
                $this->account($chatId, $sender);
                break;
            case 'tools':
                $this->tools($chatId, $sender);
                break;
            case 'help':
                (new CmdCommand())->handle($this->telegram, $this->users, $chatId, $sender, '');
                break;
            case 'auth_demo':
                $this->demo($chatId, 'auth', $sender);
                break;
            case 'charge_demo':
                $this->demo($chatId, 'charge', $sender);
                break;
            default:
                $this->telegram->sendMessage($chatId, 'That button is not available anymore.', Keyboards::home());
        }
    }

    /** @param array<string, mixed> $sender */
    private function account(int|string $chatId, array $sender): void
    {
        $id = $sender['id'] ?? '';
        $user = $id !== '' ? $this->users->find((string) $id) : null;
        if ($user === null) {
            $this->telegram->sendMessage($chatId, 'You have not registered yet. Use /register to create a demo profile.', Keyboards::home());
            return;
        }

        $username = (string) ($user['username'] ?? '');
        $username = $username === '' ? 'Not set' : '@' . $this->escape($username);
        $name = trim((string) ($user['first_name'] ?? '') . ' ' . (string) ($user['last_name'] ?? ''));
        $this->telegram->sendMessage(
            $chatId,
            "👤 <b>Demo account</b>\n\n" .
            'Name: ' . $this->escape($name) . "\n" .
            'Username: ' . $username . "\n" .
            'Telegram ID: <code>' . $this->escape((string) $user['telegram_id']) . "</code>\n" .
            'Status: <b>Registered</b>',
            Keyboards::dashboard(),
        );
    }

    /** @param array<string, mixed> $sender */
    private function deleteAccount(int|string $chatId, array $sender): void
    {
        $id = $sender['id'] ?? null;
        if ($id === null) {
            $this->telegram->sendMessage($chatId, 'I could not identify your Telegram profile.');
            return;
        }

        if (!$this->users->delete((string) $id)) {
            $this->telegram->sendMessage($chatId, 'No registered demo profile was found.', Keyboards::home());
            return;
        }

        $this->telegram->sendMessage($chatId, 'Your local demo profile has been deleted.', Keyboards::home());
    }

    /** @param array<string, mixed> $sender */
    private function tools(int|string $chatId, array $sender): void
    {
        if (!$this->isRegistered($sender)) {
            $this->telegram->sendMessage($chatId, 'Please register first to unlock the demo tools.', Keyboards::home());
            return;
        }

        $this->telegram->sendMessage(
            $chatId,
            "🧰 <b>Demo tools</b>\n\nChoose a simulated result below. No sensitive financial details are accepted and no external processor is contacted.",
            Keyboards::tools(),
        );
    }

    /** @param array<string, mixed> $sender */
    private function demo(int|string $chatId, string $scenario, array $sender): void
    {
        if (!$this->isRegistered($sender)) {
            $this->telegram->sendMessage($chatId, 'Please register first to unlock the demo tools.', Keyboards::home());
            return;
        }

        $result = $this->scenarios->result($scenario);
        $this->telegram->sendMessage(
            $chatId,
            '🧪 <b>' . $this->escape($result['title']) . "</b>\n\n" .
            'Status: <code>' . $this->escape($result['status']) . "</code>\n" .
            $this->escape($result['message']),
            Keyboards::tools(),
        );
    }

    /** @param array<string, mixed> $sender */
    private function isRegistered(array $sender): bool
    {
        $id = $sender['id'] ?? null;
        return $id !== null && $this->users->find((string) $id) !== null;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
