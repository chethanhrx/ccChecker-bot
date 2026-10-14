<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands;

use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;

final class RedeemCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $code = strtoupper(trim($arguments));
        $knownCodes = ['DEMO-STARTER' => '10 sandbox runs', 'DEMO-LAB' => '25 sandbox runs'];
        if ($code === '') {
            $telegram->sendMessage($chatId, "Usage: <code>/redeem DEMO-STARTER</code>\nDemo codes: DEMO-STARTER, DEMO-LAB");
            return;
        }
        if (!isset($knownCodes[$code])) {
            $telegram->sendMessage($chatId, "❌ Unknown demo code. No account or payment changes were made.");
            return;
        }
        $telegram->sendMessage($chatId, "✅ <b>Demo code accepted</b>\nReward: " . $knownCodes[$code] . "\n\nThis is a non-persistent simulation; it does not grant paid access.");
    }
}
