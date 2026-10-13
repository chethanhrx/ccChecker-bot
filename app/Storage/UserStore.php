<?php

declare(strict_types=1);

namespace CcCheckerBot\Storage;

use RuntimeException;

final class UserStore
{
    public function __construct(private readonly string $path)
    {
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Cannot create data directory.');
        }
        if (!is_file($path) && file_put_contents($path, '{}') === false) {
            throw new RuntimeException('Cannot initialize profile store.');
        }
    }

    /** @param array<string, mixed> $profile
     *  @return array<string, mixed>
     */
    public function register(array $profile): array
    {
        $id = (string) ($profile['id'] ?? '');
        if ($id === '') {
            throw new RuntimeException('Telegram profile ID is missing.');
        }

        $all = $this->read();
        $old = $all[$id] ?? [];
        $record = [
            'telegram_id' => $id,
            'username' => (string) ($profile['username'] ?? ''),
            'first_name' => (string) ($profile['first_name'] ?? ''),
            'last_name' => (string) ($profile['last_name'] ?? ''),
            'registered_at' => (string) ($old['registered_at'] ?? gmdate(DATE_ATOM)),
        ];
        $all[$id] = $record;
        $this->write($all);

        return $record;
    }

    public function delete(int|string $id): bool
    {
        $all = $this->read();
        $key = (string) $id;

        if (!array_key_exists($key, $all)) {
            return false;
        }

        unset($all[$key]);
        $this->write($all);

        return true;
    }

    /** @return array<string, mixed>|null */
    public function find(int|string $id): ?array
    {
        $record = $this->read()[(string) $id] ?? null;
        return is_array($record) ? $record : null;
    }

    /** @return array<string, array<string, mixed>> */
    private function read(): array
    {
        $raw = file_get_contents($this->path);
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /** @param array<string, array<string, mixed>> $data */
    private function write(array $data): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if (file_put_contents($this->path, $json, LOCK_EX) === false) {
            throw new RuntimeException('Cannot save profile data.');
        }
    }
}
