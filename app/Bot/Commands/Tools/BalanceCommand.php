<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands\Tools;

use CcCheckerBot\Bot\Commands\CommandHandler;
use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;
use CcCheckerBot\UI\Keyboards;

final class BalanceCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $id = $sender['id'] ?? null;
        $profile = $id !== null ? $users->find((string) $id) : null;
        if ($profile === null) {
            $telegram->sendMessage($chatId, 'Register with /register before checking your sandbox balance.', Keyboards::home());
            return;
        }

        $credits = max(0, (int) ($profile['demo_credits'] ?? 0));
        $redeemed = is_array($profile['redeemed_codes'] ?? null) ? $profile['redeemed_codes'] : [];
        $codes = $redeemed === [] ? 'None yet' : implode(', ', array_map(
            static fn (mixed $code): string => htmlspecialchars((string) $code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $redeemed,
        ));

        $telegram->sendMessage(
            $chatId,
            "<b>🪙 Sandbox balance</b>\n\nAvailable demo credits: <b>" . (string) $credits . "</b>\nRedeemed codes: " . $codes . "\n\nCredits are local testing counters and have no monetary value.",
            Keyboards::dashboard(),
        );
    }
}
