<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands\Gates;

use CcCheckerBot\Bot\Commands\CommandHandler;
use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;

final class ChkCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $input = trim($arguments);
        if (str_contains($input, '|') || preg_match('/\b\d{12,19}\b/', $input) === 1) {
            $telegram->sendMessage(
                $chatId,
                "🛡️ This sandbox does not accept payment-card numbers, expiry dates, or CVVs. Please do not send financial details.\n\nUse <code>/chk DEMO-1234</code> for a synthetic scenario.",
            );
            return;
        }
        if (preg_match('/^DEMO-[A-Z0-9]{4,12}$/i', $input) !== 1) {
            $telegram->sendMessage($chatId, "Usage: <code>/chk DEMO-1234</code>\nOnly synthetic demo IDs are accepted; real card checks are not available.");
            return;
        }
        $telegram->sendMessage(
            $chatId,
            "🧪 <b>Sandbox check result</b>\nReference: <code>" . htmlspecialchars(strtoupper($input), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</code>\n" .
            "Result: <code>SIMULATED_ONLY</code>\nNo card, bank, BIN, or payment gateway was contacted.",
        );
    }
}
