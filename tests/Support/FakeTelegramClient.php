<?php

declare(strict_types=1);

namespace CcCheckerBot\Tests\Support;

use CcCheckerBot\Telegram\TelegramClientInterface;

final class FakeTelegramClient implements TelegramClientInterface
{
    /** @var list<array{chat_id:int|string,text:string,keyboard:array<string,mixed>}> */
    public array $messages = [];

    /** @var list<string> */
    public array $answeredCallbacks = [];

    public function sendMessage(int|string $chatId, string $text, array $keyboard = []): array
    {
        $this->messages[] = ['chat_id' => $chatId, 'text' => $text, 'keyboard' => $keyboard];
        return ['message_id' => count($this->messages)];
    }

    public function answerCallback(string $callbackId, string $text = ''): void
    {
        $this->answeredCallbacks[] = $callbackId;
    }
}
