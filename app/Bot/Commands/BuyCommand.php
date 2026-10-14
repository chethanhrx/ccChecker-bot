<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands;

use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;

final class BuyCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $telegram->sendMessage(
            $chatId,
            "<b>🛍️ Demo plans</b>\n\n" .
            "🟢 Starter — 10 sandbox runs\n🔵 Developer — 50 sandbox runs\n🟣 Lab — 100 sandbox runs\n\n" .
            "These are display-only plans: no payment is taken and no subscription is created.\n" .
            "Try /redeem DEMO-STARTER to test the demo-code flow.",
        );
    }
}
