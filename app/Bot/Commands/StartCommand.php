<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands;

use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;
use CcCheckerBot\UI\Keyboards;

final class StartCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $name = htmlspecialchars((string) ($sender['first_name'] ?? 'there'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $telegram->sendMessage(
            $chatId,
            "👋 <b>Welcome to FlintBot, {$name}!</b>\n\n" .
            "A modular Telegram sandbox built with PHP and FlintPHP. Explore commands, account tools, and simulated demo credits.\n\n" .
            "<i>No real card data or payment processing is supported.</i>",
            Keyboards::home(),
        );
    }
}
