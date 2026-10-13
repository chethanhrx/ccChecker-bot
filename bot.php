<?php

declare(strict_types=1);

use CcCheckerBot\Bot\UpdateDispatcher;
use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Support\Env;
use CcCheckerBot\Telegram\TelegramApi;

require __DIR__ . '/vendor/autoload.php';

Env::load(__DIR__ . '/.env');

$token = Env::get('TELEGRAM_BOT_TOKEN');
if ($token === '') {
    fwrite(STDERR, "Missing TELEGRAM_BOT_TOKEN. Copy .env.example to .env and configure your bot token.\n");
    exit(1);
}

$pollTimeout = (int) Env::get('TELEGRAM_POLL_TIMEOUT', '25');
$pollTimeout = max(1, min(50, $pollTimeout));

$api = new TelegramApi($token);
$users = new UserStore(__DIR__ . '/storage/users.json');
$dispatcher = new UpdateDispatcher($api, $users);

if (!is_dir(__DIR__ . '/storage') && !mkdir(__DIR__ . '/storage', 0775, true) && !is_dir(__DIR__ . '/storage')) {
    fwrite(STDERR, "Unable to create storage directory.\n");
    exit(1);
}

fwrite(STDOUT, "FlintPHP Telegram demo bot is running. Press Ctrl+C to stop.\n");

$offset = 0;
while (true) {
    try {
        $updates = $api->getUpdates($offset, $pollTimeout);

        foreach ($updates as $update) {
            $updateId = (int) ($update['update_id'] ?? 0);
            $offset = max($offset, $updateId + 1);

            try {
                $dispatcher->dispatch($update);
            } catch (Throwable $exception) {
                error_log('[bot] Update dispatch failed: ' . $exception->getMessage());
            }
        }
    } catch (Throwable $exception) {
        error_log('[bot] Poll failed: ' . $exception->getMessage());
        sleep(2);
    }
}
