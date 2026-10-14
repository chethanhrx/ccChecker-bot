<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands;

use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;
use CcCheckerBot\UI\Keyboards;

final class AccountCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $id = $sender['id'] ?? null;
        $user = $id !== null ? $users->find((string) $id) : null;
        if ($user === null) {
            $telegram->sendMessage($chatId, 'You have not registered yet. Use /register to create a demo profile.', Keyboards::home());
            return;
        }

        $escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $username = (string) ($user['username'] ?? '');
        $username = $username === '' ? 'Not set' : '@' . $escape($username);
        $name = trim((string) ($user['first_name'] ?? '') . ' ' . (string) ($user['last_name'] ?? ''));
        $credits = max(0, (int) ($user['demo_credits'] ?? 0));\n        $telegram->sendMessage($chatId, "👤 <b>Demo account</b>\\n\\nName: " . $escape($name) . "\\nUsername: " . $username . "\\nTelegram ID: <code>" . $escape((string) ($user['telegram_id'] ?? $id)) . "</code>\\nSandbox credits: <b>" . (string) $credits . "</b>\\nStatus: <b>Registered</b>", Keyboards::dashboard());
    }
}
