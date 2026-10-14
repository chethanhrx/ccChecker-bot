<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot;

use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;

/** Per-update helper for file-based handlers; sendMsg always targets the current update's chat. */
final class BotContext
{
    /** @param array<string, mixed> $sender
     *  @param array<string, string> $commands
     */
    public function __construct(
        private readonly TelegramClientInterface $telegram,
        private readonly UserStore $users,
        private readonly int|string $chatId,
        private readonly array $sender,
        private readonly string $arguments,
        private readonly array $commands = [],
    ) {
    }

    public function sendMsg(string $message, array $keyboard = []): array
    {
        return $this->telegram->sendMessage($this->chatId, $message, $keyboard);
    }

    public function chatId(): int|string { return $this->chatId; }

    /** @return array<string, mixed> */
    public function sender(): array { return $this->sender; }

    public function arguments(): string { return $this->arguments; }

    public function users(): UserStore { return $this->users; }

    public function telegram(): TelegramClientInterface { return $this->telegram; }

    /** @return array<string, string> */
    public function commands(): array { return $this->commands; }
}
