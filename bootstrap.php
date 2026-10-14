<?php
declare(strict_types=1);

/* Explicit native-PHP bootstrap; command files are discovered by UpdateDispatcher. */
$sharedFiles = [
    __DIR__ . '/app/Telegram/TelegramClientInterface.php',
    __DIR__ . '/app/Support/Env.php',
    __DIR__ . '/app/Storage/UserStore.php',
    __DIR__ . '/app/Demo/MockScenario.php',
    __DIR__ . '/app/UI/Keyboards.php',
    __DIR__ . '/app/Telegram/TelegramApi.php',
    __DIR__ . '/app/Bot/BotContext.php',
    __DIR__ . '/app/Bot/UpdateDispatcher.php',
];
foreach ($sharedFiles as $file) {
    if (!is_file($file)) {
        throw new RuntimeException('Required application file is missing: ' . $file);
    }
    require_once $file;
}
