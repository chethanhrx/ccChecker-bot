<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands\General;

use CcCheckerBot\Bot\Commands\CommandHandler;
use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;

final class SupportCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $telegram->sendMessage(
            $chatId,
            "<b>🛟 Help & support</b>\n\n" .
            "Use /cmd for the command list, /ping to check that the worker responds, and /privacy to review stored demo data.\n\n" .
            "If a command fails, note the command name and check the worker terminal logs. Never include your bot token or private credentials in a bug report.",
        );
    }
}
