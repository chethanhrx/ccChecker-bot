<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands;

use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;

final class BinCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $telegram->sendMessage(
            $chatId,
            "🔒 <b>BIN lookup is disabled</b>\n\nThis demo does not identify card issuers from payment-card prefixes or perform card-enrichment lookups. Use /info to see the sandbox scope.",
        );
    }
}
