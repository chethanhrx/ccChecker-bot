<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands;

use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;

interface CommandHandler
{
    /** @param array<string, mixed> $sender */
    public function handle(
        TelegramClientInterface $telegram,
        UserStore $users,
        int|string $chatId,
        array $sender,
        string $arguments,
    ): void;
}
