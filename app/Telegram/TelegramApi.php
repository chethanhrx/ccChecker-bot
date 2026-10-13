<?php

declare(strict_types=1);

namespace CcCheckerBot\Telegram;

use RuntimeException;

final class TelegramApi
{
    public function __construct(private readonly string $token)
    {
        if ($token === '') {
            throw new RuntimeException('Bot token is required.');
        }
    }

    /** Remove any existing webhook so long polling can be used. */
    public function deleteWebhook(): void
    {
        $this->call('deleteWebhook', ['drop_pending_updates' => false]);
    }

    /** @return array<string, mixed> */
    public function getMe(): array
    {
        return $this->call('getMe', []);
    }

    /** @return list<array<string, mixed>> */
    public function getUpdates(int $offset, int $timeout): array
    {
        $result = $this->call('getUpdates', [
            'offset' => $offset,
            'timeout' => $timeout,
            'allowed_updates' => ['message', 'callback_query'],
        ], max(65, $timeout + 15));

        return array_values(array_filter($result, 'is_array'));
    }

    /** @param array<string, mixed> $keyboard
     *  @return array<string, mixed>
     */
    public function sendMessage(int|string $chatId, string $text, array $keyboard = []): array
    {
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if ($keyboard !== []) {
            $payload['reply_markup'] = $keyboard;
        }

        return $this->call('sendMessage', $payload);
    }

    public function answerCallback(string $callbackId, string $text = ''): void
    {
        $payload = ['callback_query_id' => $callbackId];
        if ($text !== '') {
            $payload['text'] = $text;
        }

        $this->call('answerCallbackQuery', $payload);
    }

    /** @param array<string, mixed> $payload
     *  @return array<string, mixed>|list<array<string, mixed>>
     */
    private function call(string $method, array $payload, int $timeout = 20): array
    {
        $url = sprintf('https://api.telegram.org/bot%s/%s', $this->token, $method);
        $handle = curl_init($url);

        if ($handle === false) {
            throw new RuntimeException('Could not initialize HTTP transport.');
        }

        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => $timeout,
        ]);

        $body = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if (!is_string($body)) {
            throw new RuntimeException('Telegram transport failed: ' . ($error !== '' ? $error : 'unknown error'));
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded) || ($decoded['ok'] ?? false) !== true) {
            $description = is_array($decoded) ? (string) ($decoded['description'] ?? 'Invalid response') : 'Invalid JSON response';
            throw new RuntimeException(sprintf('Telegram method %s failed (HTTP %d): %s', $method, $status, $description));
        }

        $result = $decoded['result'] ?? [];
        return is_array($result) ? $result : [];
    }
}
