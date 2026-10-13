<?php

declare(strict_types=1);

namespace CcCheckerBot\Telegram;

interface TelegramClientInterface
{
    /** @param array<string, mixed> $keyboard
     *  @return array<string, mixed>
     */
    public function sendMessage(int|string $chatId, string $text, array $keyboard = []): array;

    public function answerCallback(string $callbackId, string $text = ''): void;
}
