<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands;

use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;
use CcCheckerBot\UI\Keyboards;

final class RedeemCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $id = $sender['id'] ?? null;
        if ($id === null) {
            $telegram->sendMessage($chatId, 'Telegram profile details are unavailable. Please try again.');
            return;
        }

        $code = strtoupper(trim($arguments));
        if ($code === '') {
            $telegram->sendMessage($chatId, "Usage: <code>/redeem DEMO-STARTER</code>\nAvailable sandbox codes: DEMO-STARTER (10 credits), DEMO-LAB (25 credits).");
            return;
        }

        $result = $users->redeemDemoCode((string) $id, $code);
        switch ($result['status']) {
            case 'not_registered':
                $telegram->sendMessage($chatId, 'Register first with /register. Demo credits are attached to your local profile.', Keyboards::home());
                return;
            case 'invalid_code':
                $telegram->sendMessage($chatId, '❌ Unknown demo code. No credits were added.');
                return;
            case 'already_redeemed':
                $telegram->sendMessage($chatId, 'ℹ️ That code has already been redeemed on your profile. Current demo balance: <b>' . (string) $result['balance'] . '</b> credits.');
                return;
            case 'redeemed':
                $telegram->sendMessage(
                    $chatId,
                    "✅ <b>Demo code redeemed</b>\n\nAdded: <b>" . (string) $result['credits'] . "</b> sandbox credits\nBalance: <b>" . (string) $result['balance'] . "</b> sandbox credits\n\nThese credits are local test counters only; they have no monetary value.",
                    Keyboards::dashboard(),
                );
                return;
        }
    }
}
