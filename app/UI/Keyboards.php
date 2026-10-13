<?php

declare(strict_types=1);

namespace CcCheckerBot\UI;

final class Keyboards
{
    /** @return array<string, mixed> */
    public static function home(): array
    {
        return ['inline_keyboard' => [
            [
                ['text' => '📝 Register', 'callback_data' => 'register'],
                ['text' => '👤 Account', 'callback_data' => 'account'],
            ],
            [
                ['text' => '🧰 Tools', 'callback_data' => 'tools'],
                ['text' => '❔ Help', 'callback_data' => 'help'],
            ],
        ]];
    }

    /** @return array<string, mixed> */
    public static function dashboard(): array
    {
        return ['inline_keyboard' => [
            [
                ['text' => '🧰 Tools', 'callback_data' => 'tools'],
                ['text' => '👤 Account', 'callback_data' => 'account'],
            ],
            [
                ['text' => '🏠 Home', 'callback_data' => 'home'],
                ['text' => '❔ Help', 'callback_data' => 'help'],
            ],
        ]];
    }

    /** @return array<string, mixed> */
    public static function tools(): array
    {
        return ['inline_keyboard' => [
            [
                ['text' => '🧪 AUTH demo', 'callback_data' => 'auth_demo'],
                ['text' => '🧪 CHARGE demo', 'callback_data' => 'charge_demo'],
            ],
            [
                ['text' => '👤 Account', 'callback_data' => 'account'],
                ['text' => '🏠 Home', 'callback_data' => 'home'],
            ],
        ]];
    }
}
