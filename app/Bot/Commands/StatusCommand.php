<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands;

use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;

final class StatusCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $memory = memory_get_usage(true);
        $memoryText = $memory >= 1048576
            ? number_format($memory / 1048576, 1) . ' MiB'
            : number_format($memory / 1024, 1) . ' KiB';

        $telegram->sendMessage(
            $chatId,
            "<b>📊 Worker status</b>\n\n" .
            "Process: <code>" . (string) getmypid() . "</code>\n" .
            "PHP: <code>" . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION . "</code>\n" .
            "Memory allocated: <code>" . $memoryText . "</code>\n" .
            "Mode: <code>long-polling</code>\n" .
            "Sandbox: <code>enabled</code>\n\n" .
            "This reports the current worker process; it is not a full infrastructure health check.",
        );
    }
}
