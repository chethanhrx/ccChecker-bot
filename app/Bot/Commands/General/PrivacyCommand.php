<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands\General;

use CcCheckerBot\Bot\Commands\CommandHandler;
use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;

final class PrivacyCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $telegram->sendMessage(
            $chatId,
            "<b>🔐 Demo privacy</b>\n\n" .
            "• The demo profile store keeps your Telegram ID, display name, username, and registration timestamp locally.\n" .
            "• Do not send card numbers, expiry dates, CVVs, passwords, or banking details.\n" .
            "• /deleteaccount removes your local demo profile.\n" .
            "• /info explains what this bot does.\n\n" .
            "This is a starter project, not a certified privacy or payment service.",
        );
    }
}
