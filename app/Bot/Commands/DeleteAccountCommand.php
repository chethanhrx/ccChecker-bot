<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands;

use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;
use CcCheckerBot\UI\Keyboards;

final class DeleteAccountCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $id = $sender['id'] ?? null;
        if ($id === null) {
            $telegram->sendMessage($chatId, 'I could not identify your Telegram profile.');
            return;
        }
        if (!$users->delete((string) $id)) {
            $telegram->sendMessage($chatId, 'No registered demo profile was found.', Keyboards::home());
            return;
        }
        $telegram->sendMessage($chatId, 'Your local demo profile has been deleted.', Keyboards::home());
    }
}
