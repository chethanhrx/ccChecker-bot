<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands;

use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;

final class IdCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $id = isset($sender['id']) ? (string) $sender['id'] : 'unavailable';
        $telegram->sendMessage($chatId, "🪪 <b>Your Telegram ID</b>\n<code>" . htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code>');
    }
}
