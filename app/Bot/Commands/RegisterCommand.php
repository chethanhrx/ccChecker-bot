<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands;

use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;
use CcCheckerBot\UI\Keyboards;

final class RegisterCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        if (!isset($sender['id'])) {
            $telegram->sendMessage($chatId, 'Telegram profile details are unavailable. Please try again.');
            return;
        }

        $users->register($sender);
        $telegram->sendMessage($chatId, '✅ <b>Registration complete.</b> Your local demo profile is ready.', Keyboards::dashboard());
    }
}
