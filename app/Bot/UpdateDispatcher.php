<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot;

use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;
use CcCheckerBot\UI\Keyboards;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

final class UpdateDispatcher
{
    /** @var array<string, array{description:string, handler:callable}> */
    private array $fileCommands = [];

    private readonly string $commandsDirectory;

    /** @var array<string, string> */
    private array $callbacks = [
        'home' => '/start',
        'register' => '/register',
        'account' => '/account',
        'balance' => '/balance',
        'delete_account' => '/deleteaccount',
        'tools' => '/tools',
        'help' => '/cmd',
        'auth_demo' => '/authdemo',
        'charge_demo' => '/chargedemo',
    ];

    public function __construct(
        private readonly TelegramClientInterface $telegram,
        private readonly UserStore $users,
        ?string $commandsDirectory = null,
    ) {
        $this->commandsDirectory = $commandsDirectory ?? __DIR__ . '/Commands';
        $this->fileCommands = $this->discoverFileCommands();
    }

    /** @return list<array{command:string, description:string}> */
    public function telegramCommands(): array
    {
        $commands = [];
        foreach ($this->fileCommands as $name => $definition) {
            $commands[] = [
                'command' => ltrim($name, '/'),
                'description' => substr($definition['description'] !== '' ? $definition['description'] : 'Run ' . $name, 0, 256),
            ];
        }
        return $commands;
    }

    /** @return array<string, string> */
    public function commandDefinitions(): array
    {
        $definitions = [];
        foreach ($this->fileCommands as $name => $definition) {
            $definitions[$name] = $definition['description'];
        }
        return $definitions;
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

        $this->invokeCommand($command, $chatId, $sender, $arguments, $message);
    }

    /** @param array<string, mixed> $message */
    private function invokeCommand(string $command, int|string $chatId, array $sender, string $arguments, array $message): void
    {
        $definition = $this->fileCommands[$command] ?? null;
        if ($definition === null) {
            $this->telegram->sendMessage($chatId, 'Unknown command. Use /cmd to see available commands.', Keyboards::home());
            return;
        }

        try {
            $context = new BotContext(
                $this->telegram,
                $this->users,
                $chatId,
                $sender,
                $arguments,
                $this->commandDefinitions(),
            );
            ($definition['handler'])($context, $message);
        } catch (Throwable $exception) {
            error_log(sprintf('[telegram-bot] File command %s failed: %s', $command, $exception->getMessage()));
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
        $command = $this->callbacks[$data] ?? null;
        if ($command === null) {
            $this->telegram->sendMessage($chatId, 'That button is no longer available.', Keyboards::home());
            return;
        }

        $this->invokeCommand($command, $chatId, $sender, '', $message);
    }

    /**
     * Standalone command files return ['command' => 'name', 'description' => '...', 'handler' => callable].
     *
     * @return array<string, array{description:string, handler:callable}>
     */
    private function discoverFileCommands(): array
    {
        if (!is_dir($this->commandsDirectory)) {
            return [];
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->commandsDirectory, \FilesystemIterator::SKIP_DOTS),
        );
        $commands = [];

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php'
                || preg_match('/Command(?:Handler)?\.php$/', $file->getFilename()) === 1) {
                continue;
            }

            try {
                $definition = require $file->getPathname();
            } catch (Throwable $exception) {
                error_log('[telegram-bot] Could not load command file ' . $file->getPathname() . ': ' . $exception->getMessage());
                continue;
            }

            if (!is_array($definition)) {
                continue;
            }

            $name = strtolower(trim((string) ($definition['command'] ?? '')));
            $handler = $definition['handler'] ?? null;
            if (preg_match('/^[a-z0-9_]{1,32}$/', $name) !== 1 || !is_callable($handler)) {
                error_log('[telegram-bot] Ignoring invalid command file: ' . $file->getPathname());
                continue;
            }

            $commands['/' . $name] = [
                'description' => trim((string) ($definition['description'] ?? '')),
                'handler' => $handler,
            ];
        }

        ksort($commands);
        return $commands;
    }
}
