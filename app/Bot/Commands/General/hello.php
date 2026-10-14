<?php

declare(strict_types=1);

/*
 * Standalone command example.
 * Add a file like this anywhere under Commands/ and the dispatcher discovers it.
 * No command class or dispatcher registration is required.
 */

return [
    'command' => 'hello',
    'description' => 'Say hello',
    'handler' => static function (\\CcCheckerBot\\Bot\\BotContext $bot, array $message): void {
        $name = htmlspecialchars(
            (string) ($message['from']['first_name'] ?? 'there'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );

        // The current private/group chat ID is resolved automatically.
        $bot->sendMsg("👋 Hello, <b>{$name}</b>! This command was auto-discovered.");
    },
];
