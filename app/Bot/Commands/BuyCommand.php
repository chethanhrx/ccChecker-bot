<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands;

use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;
use CcCheckerBot\UI\Keyboards;

final class BuyCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $telegram->sendMessage(
            $chatId,
            "<b>🛍️ Sandbox plans</b>\n\n" .
            "🟢 <b>Starter</b> — 10 demo credits\n" .
            "🟣 <b>Lab</b> — 25 demo credits\n\n" .
            "This is a preview of the future plan catalogue. Checkout is not connected, no payment is collected, and these plans do not grant entitlements.\n\n" .
            "For a working sandbox flow, register with /register and redeem /redeem DEMO-STARTER or /redeem DEMO-LAB.",
            Keyboards::dashboard(),
        );
    }
}
