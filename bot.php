<?php
declare(strict_types=1);

use CcCheckerBot\Bot\UpdateDispatcher;
use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Support\Env;
use CcCheckerBot\Telegram\TelegramApi;

require __DIR__ . '/bootstrap.php';
Env::load(__DIR__ . '/.env');

if (!extension_loaded('curl')) {
    fwrite(STDERR, "The PHP cURL extension is required. Install/enable ext-curl and retry.\n");
    exit(1);
}
$token = Env::get('TELEGRAM_BOT_TOKEN');
if ($token === '') {
    fwrite(STDERR, "Missing TELEGRAM_BOT_TOKEN. Copy .env.example to .env and configure it.\n");
    exit(1);
}
$pollTimeout = max(1, min(50, (int) Env::get('TELEGRAM_POLL_TIMEOUT', '25')));
$storageDirectory = __DIR__ . '/storage';

try {
    $users = new UserStore($storageDirectory . '/users.json');
    $api = new TelegramApi($token);
    $dispatcher = new UpdateDispatcher($api, $users);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Bot initialization failed: ' . $exception->getMessage() . "\n");
    exit(1);
}
try {
    $api->deleteWebhook();
    $identity = $api->getMe();
    $api->setMyCommands($dispatcher->telegramCommands());
} catch (Throwable $exception) {
    fwrite(STDERR, 'Telegram startup check failed: ' . $exception->getMessage() . "\n");
    exit(1);
}
fwrite(STDOUT, "Native PHP Telegram bot started as @" . (string) ($identity['username'] ?? 'unknown') . ". Press Ctrl+C to stop.\n");

$offsetPath = $storageDirectory . '/offset.txt';
$storedOffset = is_file($offsetPath) ? file_get_contents($offsetPath) : false;
$offset = is_string($storedOffset) && ctype_digit(trim($storedOffset)) ? (int) trim($storedOffset) : 0;
$backoffSeconds = 1;
while (true) {
    try {
        $updates = $api->getUpdates($offset, $pollTimeout);
        if ($updates === []) $backoffSeconds = 1;
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
            $backoffSeconds = 1;
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
