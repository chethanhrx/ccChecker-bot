<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot;

use CcCheckerBot\Demo\MockScenario;
use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;
use CcCheckerBot\UI\Keyboards;

final class UpdateDispatcher
{
    public function __construct(
        private readonly TelegramClientInterface $telegram,
        private readonly UserStore $users,
        private readonly MockScenario $scenarios = new MockScenario(),
    ) {
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

        $command = strtolower((string) strtok($text, " \t\n"));
        $command = explode('@', $command, 2)[0];

        switch ($command) {
            case '/start':
                $this->home($chatId, (string) ($sender['first_name'] ?? 'there'));
                break;
            case '/help':
                $this->help($chatId);
                break;
            case '/account':
                $this->account($chatId, $sender);
                break;
            case '/deleteaccount':
                $this->deleteAccount($chatId, $sender);
                break;
            case '/tools':
                $this->tools($chatId, $sender);
                break;
            case '/authdemo':
                $this->demo($chatId, 'auth', $sender);
                break;
            case '/chargedemo':
                $this->demo($chatId, 'charge', $sender);
                break;
            default:
                $this->telegram->sendMessage($chatId, 'Unknown command. Use /help to see the command list.', Keyboards::home());
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
                // A stale callback acknowledgement must not block the action itself.
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
                $this->home($chatId, (string) ($sender['first_name'] ?? 'there'));
                break;
            case 'register':
                $this->register($chatId, $sender);
                break;
            case 'account':
                $this->account($chatId, $sender);
                break;
            case 'tools':
                $this->tools($chatId, $sender);
                break;
            case 'help':
                $this->help($chatId);
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

    private function home(int|string $chatId, string $name): void
    {
        $this->telegram->sendMessage(
            $chatId,
            '👋 <b>Welcome to FlintBot, ' . $this->escape($name) . '!</b>' . "\n\n" .
            'A modular Telegram bot starter built with PHP and FlintPHP. Register to create your local demo profile.' . "\n\n" .
            '<i>The AUTH/CHARGE menu items are simulated demonstrations only.</i>',
            Keyboards::home(),
        );
    }

    /** @param array<string, mixed> $sender */
    private function register(int|string $chatId, array $sender): void
    {
        if (!isset($sender['id'])) {
            $this->telegram->sendMessage($chatId, 'Telegram profile details were unavailable. Please try /start again.');
            return;
        }

        $this->users->register($sender);
        $this->telegram->sendMessage($chatId, '✅ <b>Registration complete.</b> Your local demo profile is ready.', Keyboards::dashboard());
    }

    /** @param array<string, mixed> $sender */
    private function account(int|string $chatId, array $sender): void
    {
        $id = $sender['id'] ?? '';
        $user = $id !== '' ? $this->users->find((string) $id) : null;
        if ($user === null) {
            $this->telegram->sendMessage($chatId, 'You have not registered yet. Tap Register to create a demo profile.', Keyboards::home());
            return;
        }

        $username = (string) ($user['username'] ?? '');
        $username = $username === '' ? 'Not set' : '@' . $this->escape($username);
        $name = trim((string) ($user['first_name'] ?? '') . ' ' . (string) ($user['last_name'] ?? ''));
        $this->telegram->sendMessage(
            $chatId,
            '👤 <b>Demo account</b>' . "\n\n" .
            'Name: ' . $this->escape($name) . "\n" .
            'Username: ' . $username . "\n" .
            'Telegram ID: <code>' . $this->escape((string) $user['telegram_id']) . '</code>' . "\n" .
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

    private function tools(int|string $chatId, array $sender): void
    {
        if (!$this->isRegistered($sender)) {
            $this->telegram->sendMessage($chatId, 'Please register first to unlock the demo tools.', Keyboards::home());
            return;
        }

        $this->telegram->sendMessage(
            $chatId,
            '🧰 <b>Demo tools</b>' . "\n\n" .
            'Choose a simulated result below. These buttons do not accept sensitive financial details or contact an external processor.',
            Keyboards::tools(),
        );
    }

    private function help(int|string $chatId): void
    {
        $this->telegram->sendMessage(
            $chatId,
            '<b>Commands</b>' . "\n" .
            '/start — Welcome menu' . "\n" .
            '/help — Command guide' . "\n" .
            '/account — Account information' . "\n" .
            '/deleteaccount — Delete your local profile' . "\n" .
            '/tools — Demo tools' . "\n" .
            '/authdemo — Simulated AUTH result' . "\n" .
            '/chargedemo — Simulated CHARGE result',
            Keyboards::dashboard(),
        );
    }

    private function demo(int|string $chatId, string $scenario, array $sender): void
    {
        if (!$this->isRegistered($sender)) {
            $this->telegram->sendMessage($chatId, 'Please register first to unlock the demo tools.', Keyboards::home());
            return;
        }

        $result = $this->scenarios->result($scenario);
        $this->telegram->sendMessage(
            $chatId,
            '🧪 <b>' . $this->escape($result['title']) . '</b>' . "\n\n" .
            'Status: <code>' . $this->escape($result['status']) . '</code>' . "\n" .
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
