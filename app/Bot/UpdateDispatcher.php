<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot;

use CcCheckerBot\Bot\Commands\AccountCommand;
use CcCheckerBot\Bot\Commands\BalanceCommand;
use CcCheckerBot\Bot\Commands\BinCommand;
use CcCheckerBot\Bot\Commands\BuyCommand;
use CcCheckerBot\Bot\Commands\ChkCommand;
use CcCheckerBot\Bot\Commands\CmdCommand;
use CcCheckerBot\Bot\Commands\CommandHandler;
use CcCheckerBot\Bot\Commands\DemoScenarioCommand;
use CcCheckerBot\Bot\Commands\DeleteAccountCommand;
use CcCheckerBot\Bot\Commands\IdCommand;
use CcCheckerBot\Bot\Commands\InfoCommand;
use CcCheckerBot\Bot\Commands\PingCommand;
use CcCheckerBot\Bot\Commands\PrivacyCommand;
use CcCheckerBot\Bot\Commands\StatusCommand;
use CcCheckerBot\Bot\Commands\SupportCommand;
use CcCheckerBot\Bot\Commands\RedeemCommand;
use CcCheckerBot\Bot\Commands\RegisterCommand;
use CcCheckerBot\Bot\Commands\StartCommand;
use CcCheckerBot\Bot\Commands\ToolsCommand;
use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;
use CcCheckerBot\UI\Keyboards;
use Throwable;

final class UpdateDispatcher
{
    /** @var array<string, CommandHandler> */
    private array $commands;

    /** @var array<string, CommandHandler> */
    private array $callbacks;

    public function __construct(
        private readonly TelegramClientInterface $telegram,
        private readonly UserStore $users,
    ) {
        $this->commands = [
            '/start' => new StartCommand(),
            '/cmd' => new CmdCommand(),
            '/help' => new CmdCommand(),
            '/id' => new IdCommand(),
            '/info' => new InfoCommand(),
            '/register' => new RegisterCommand(),
            '/account' => new AccountCommand(),
            '/deleteaccount' => new DeleteAccountCommand(),
            '/buy' => new BuyCommand(),
            '/balance' => new BalanceCommand(),
            '/redeem' => new RedeemCommand(),
            '/chk' => new ChkCommand(),
            '/bin' => new BinCommand(),
            '/ping' => new PingCommand(),
            '/status' => new StatusCommand(),
            '/privacy' => new PrivacyCommand(),
            '/support' => new SupportCommand(),
            '/tools' => new ToolsCommand(),
            '/authdemo' => new DemoScenarioCommand('auth'),
            '/chargedemo' => new DemoScenarioCommand('charge'),
        ];

        $this->callbacks = [
            'home' => $this->commands['/start'],
            'register' => $this->commands['/register'],
            'account' => $this->commands['/account'],
            'balance' => $this->commands['/balance'],
            'delete_account' => $this->commands['/deleteaccount'],
            'tools' => $this->commands['/tools'],
            'help' => $this->commands['/cmd'],
            'auth_demo' => $this->commands['/authdemo'],
            'charge_demo' => $this->commands['/chargedemo'],
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
        if ($chatId === null || $text === '' || !str_starts_with($text, '/')) {
            return;
        }

        $parts = preg_split('/\s+/', $text, 2) ?: [];
        $command = strtolower((string) ($parts[0] ?? ''));
        $command = explode('@', $command, 2)[0];
        $arguments = trim((string) ($parts[1] ?? ''));

        $handler = $this->commands[$command] ?? null;
        if ($handler === null) {
            $this->telegram->sendMessage($chatId, 'Unknown command. Use /cmd to see available commands.', Keyboards::home());
            return;
        }

        try {
            $handler->handle($this->telegram, $this->users, $chatId, $sender, $arguments);
        } catch (Throwable $exception) {
            error_log(sprintf('[telegram-bot] Command %s failed: %s', $command, $exception->getMessage()));
            $this->telegram->sendMessage($chatId, 'Sorry, that command failed safely. Please try again in a moment.');
        }
    }

    /** @param array<string, mixed> $callback */
    private function dispatchCallback(array $callback): void
    {
        $callbackId = (string) ($callback['id'] ?? '');
        if ($callbackId !== '') {
            try {
                $this->telegram->answerCallback($callbackId);
            } catch (Throwable $exception) {
                error_log('[telegram-bot] Callback acknowledgement failed: ' . $exception->getMessage());
            }
        }

        $sender = is_array($callback['from'] ?? null) ? $callback['from'] : [];
        $message = is_array($callback['message'] ?? null) ? $callback['message'] : [];
        $chatId = $message['chat']['id'] ?? null;
        if ($chatId === null) {
            return;
        }

        $data = (string) ($callback['data'] ?? '');
        $handler = $this->callbacks[$data] ?? null;
        if ($handler === null) {
            $this->telegram->sendMessage($chatId, 'That button is no longer available.', Keyboards::home());
            return;
        }

        try {
            $handler->handle($this->telegram, $this->users, $chatId, $sender, '');
        } catch (Throwable $exception) {
            error_log(sprintf('[telegram-bot] Callback %s failed: %s', $data, $exception->getMessage()));
            $this->telegram->sendMessage($chatId, 'Sorry, that action failed safely. Please try again.');
        }
    }
}
