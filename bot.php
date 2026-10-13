<?php

declare(strict_types=1);

use CcCheckerBot\Bot\UpdateDispatcher;
use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Support\Env;
use CcCheckerBot\Telegram\TelegramApi;
use CcCheckerBot\Telegram\TelegramClientInterface;
use FlintPHP\Framework\Foundation\Application;

require __DIR__ . '/vendor/autoload.php';

Env::load(__DIR__ . '/.env');

$token = Env::get('TELEGRAM_BOT_TOKEN');
if ($token === '') {
    fwrite(STDERR, "Missing TELEGRAM_BOT_TOKEN. Copy .env.example to .env and configure it.\n");
    exit(1);
}

$pollTimeout = max(1, min(50, (int) Env::get('TELEGRAM_POLL_TIMEOUT', '25')));
$storageDirectory = __DIR__ . '/storage';

if (!is_dir($storageDirectory) && !mkdir($storageDirectory, 0770, true) && !is_dir($storageDirectory)) {
    fwrite(STDERR, "Cannot create storage directory.\n");
    exit(1);
}

/*
 * Use FlintPHP's Application and PSR-11 container for explicit dependency
 * composition. The Telegram worker is CLI long polling, not an HTTP webhook.
 */
$app = new Application(__DIR__);
$container = $app->container();
$container->singleton(TelegramApi::class, static fn () => new TelegramApi($token));
$container->singleton(TelegramClientInterface::class, static fn () => $container->get(TelegramApi::class));
$container->singleton(UserStore::class, static fn () => new UserStore($storageDirectory . '/users.json'));

$api = $container->get(TelegramApi::class);
$dispatcher = $container->get(UpdateDispatcher::class);

try {
    // Long polling and webhooks cannot be active at the same time.
    $api->deleteWebhook();
    $identity = $api->getMe();
    $api->setMyCommands();
} catch (Throwable $exception) {
    fwrite(STDERR, 'Telegram startup check failed: ' . $exception->getMessage() . "\n");
    exit(1);
}

$botName = (string) ($identity['username'] ?? 'unknown');
fwrite(STDOUT, "FlintPHP Telegram bot started as @" . $botName . ". Press Ctrl+C to stop.\n");

$offsetPath = $storageDirectory . '/offset.txt';
$storedOffset = is_file($offsetPath) ? file_get_contents($offsetPath) : false;
$offset = is_string($storedOffset) && ctype_digit(trim($storedOffset)) ? (int) trim($storedOffset) : 0;
$backoffSeconds = 1;
while (true) {
    try {
        $updates = $api->getUpdates($offset, $pollTimeout);
        $backoffSeconds = 1;

        foreach ($updates as $update) {
            $updateId = (int) ($update['update_id'] ?? 0);

            try {
                $dispatcher->dispatch($update);
            } catch (Throwable $exception) {
                error_log('[telegram-bot] Update failed: ' . $exception->getMessage());
                sleep($backoffSeconds);
                $backoffSeconds = min(30, $backoffSeconds * 2);
                break;
            }

            $offset = max($offset, $updateId + 1);
            if (file_put_contents($offsetPath, (string) $offset, LOCK_EX) === false) {
                error_log('[telegram-bot] Could not persist the update offset.');
            }
        }
    } catch (Throwable $exception) {
        error_log('[telegram-bot] Polling failed: ' . $exception->getMessage());
        sleep($backoffSeconds);
        $backoffSeconds = min(30, $backoffSeconds * 2);
    }
}
