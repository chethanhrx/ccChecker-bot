<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands\General;

use CcCheckerBot\Bot\Commands\CommandHandler;
use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;

final class PingCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $telegram->sendMessage($chatId, "🏓 <b>Pong!</b>\nThe bot worker handled your command. This is an application-level check, not a network latency measurement.");
    }
}
