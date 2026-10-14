<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands\General;

use CcCheckerBot\Bot\Commands\CommandHandler;
use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;

final class InfoCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $registered = isset($sender['id']) && $users->find((string) $sender['id']) !== null;
        $telegram->sendMessage(
            $chatId,
            "<b>ℹ️ FlintBot sandbox</b>\n\n" .
            "Runtime: PHP " . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . "\n" .
            "Framework: FlintPHP\nMode: simulated demo\nProfile: " . ($registered ? 'registered' : 'guest') . "\n\n" .
            "No real payment authorization, card verification, or financial-data storage is performed.",
        );
    }
}
