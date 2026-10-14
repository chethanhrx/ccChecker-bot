<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands;

use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;
use CcCheckerBot\UI\Keyboards;

final class ToolsCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $id = $sender['id'] ?? null;
        if ($id === null || $users->find((string) $id) === null) {
            $telegram->sendMessage($chatId, 'Please register first to unlock the demo tools.', Keyboards::home());
            return;
        }

        $telegram->sendMessage(
            $chatId,
            "🧰 <b>Sandbox tools</b>\n\nChoose a synthetic scenario below. These buttons return fixed demo outcomes only; they do not perform real payment authorizations or charges.",
            Keyboards::tools(),
        );
    }
}
