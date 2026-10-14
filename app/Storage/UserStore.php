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
        $old = is_array($all[$id] ?? null) ? $all[$id] : [];
        $record = [
            'telegram_id' => $id,
            'username' => (string) ($profile['username'] ?? ''),
            'first_name' => (string) ($profile['first_name'] ?? ''),
            'last_name' => (string) ($profile['last_name'] ?? ''),
            'registered_at' => (string) ($old['registered_at'] ?? gmdate(DATE_ATOM)),
            'demo_credits' => max(0, (int) ($old['demo_credits'] ?? 0)),
            'redeemed_codes' => is_array($old['redeemed_codes'] ?? null) ? array_values($old['redeemed_codes']) : [],
        ];
        $all[$id] = $record;
        $this->write($all);

        return $record;
    }

    /**
     * Redeem a fixed sandbox code exactly once per profile.
     *
     * @return array{status:string,credits:int,balance?:int}
     */
    public function redeemDemoCode(int|string $id, string $code): array
    {
        $all = $this->read();
        $key = (string) $id;
        if (!isset($all[$key]) || !is_array($all[$key])) {
            return ['status' => 'not_registered', 'credits' => 0];
        }

        $rewards = [
            'DEMO-STARTER' => 10,
            'DEMO-LAB' => 25,
        ];
        $code = strtoupper(trim($code));
        if (!isset($rewards[$code])) {
            return ['status' => 'invalid_code', 'credits' => 0];
        }

        $profile = $all[$key];
        $redeemed = is_array($profile['redeemed_codes'] ?? null) ? $profile['redeemed_codes'] : [];
        if (in_array($code, $redeemed, true)) {
            return ['status' => 'already_redeemed', 'credits' => 0, 'balance' => max(0, (int) ($profile['demo_credits'] ?? 0))];
        }

        $reward = $rewards[$code];
        $balance = max(0, (int) ($profile['demo_credits'] ?? 0)) + $reward;
        $profile['demo_credits'] = $balance;
        $profile['redeemed_codes'] = [...$redeemed, $code];
        $all[$key] = $profile;
        $this->write($all);

        return ['status' => 'redeemed', 'credits' => $reward, 'balance' => $balance];
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
        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new RuntimeException('Profile store contains invalid JSON.', 0, $exception);
        }
        if (!is_array($data)) {
            throw new RuntimeException('Profile store has an invalid structure.');
        }
        return $data;
    }

    /** @param array<string, array<string, mixed>> $data */
    private function write(array $data): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $temporaryPath = $this->path . '.tmp';
        if (file_put_contents($temporaryPath, $json, LOCK_EX) === false) {
            throw new RuntimeException('Cannot write temporary profile data.');
        }
        if (!rename($temporaryPath, $this->path)) {
            @unlink($temporaryPath);
            throw new RuntimeException('Cannot atomically replace profile data.');
        }
    }
}
