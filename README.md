# FlintPHP Telegram Bot

A modular Telegram bot starter built with **PHP 8.2+**, **FlintPHP**, and Telegram's Bot API. It runs as a CLI long-polling worker with automatically discovered file-based commands.

> **Sandbox boundary:** `/chk` accepts synthetic demo references only. It does not accept or validate payment-card numbers, expiry dates, or CVVs. `/bin` does not perform issuer lookups. Purchase/redeem commands are simulations only; no payment is taken, no paid entitlement is granted, and no payment gateway is contacted.

## Features

- CLI long polling with persisted update offset and bounded retry backoff
- FlintPHP application/container for dependency composition
- Every command is a standalone PHP file discovered automatically at startup
- New file commands automatically appear in the dispatcher, `/cmd` help, and Telegram command menu
- Telegram command menu, inline keyboards, and callback handling
- Local demo profile registration and deletion
- Sandbox plans, one-time demo-code redemption with persistent demo-credit balances, synthetic scenario command, worker diagnostics, privacy notes, and support command
- PHPUnit tests and GitHub Actions CI for PHP 8.2, 8.3, and 8.4

## Commands

| Command | Purpose |
|---|---|
| `/start` | Welcome screen and menu |
| `/cmd` or `/help` | Command guide |
| `/id` | Show your Telegram user ID |
| `/info` | Bot runtime and sandbox status |
| `/register` | Create a local demo profile |
| `/account` | View your local demo profile |
| `/deleteaccount` | Delete your local demo profile |
| `/buy` | View simulated plans; no checkout |
| `/balance` | View demo credits and redeemed codes |
| `/redeem DEMO-STARTER` | Redeem 10 sandbox credits once per profile |
| `/redeem DEMO-LAB` | Redeem 25 sandbox credits once per profile |
| `/chk DEMO-1234` | Run a synthetic sandbox scenario |
| `/bin` | Explain that BIN lookup is disabled |
| `/ping` | Check that the worker responds |
| `/status` | Show PHP process and memory diagnostics |
| `/privacy` | Explain local profile data and privacy boundaries |
| `/support` | Show troubleshooting guidance |
| `/tools` | Open the registration-gated demo menu |
| `/authdemo`, `/chargedemo` | Fixed simulated outcomes |

The `/chk` command deliberately rejects pipe-delimited values and long numeric strings to reduce accidental submission of payment-card data. It is not a card checker and does not perform Luhn checks or payment authorization. Demo credits are local counters only and have no monetary value. Redeem codes are fixed, one-time-per-profile sandbox examples.

## Project structure

Commands are grouped by responsibility so the dispatcher stays easy to scan:

```text
app/
  Bot/
    UpdateDispatcher.php
    Commands/
      General/
        start.php
        cmd.php
        help.php
        hello.php
        info.php
        ping.php
        privacy.php
        status.php
        support.php
      Gates/
        chk.php
        authdemo.php
        chargedemo.php
      Tools/
        id.php
        register.php
        account.php
        deleteaccount.php
        buy.php
        balance.php
        redeem.php
        bin.php
        tools.php
  Demo/MockScenario.php
  Storage/UserStore.php
  Support/Env.php
  Telegram/TelegramApi.php
  Telegram/TelegramClientInterface.php
  UI/Keyboards.php
tests/
bot.php
composer.json
phpunit.xml
.env.example
```

## Add a command (no dispatcher edits)

Create a normal PHP file anywhere under `app/Bot/Commands/`, for example
`app/Bot/Commands/General/hello.php`. The file returns its own command name,
description, and handler. The dispatcher discovers it automatically at startup.

```php
<?php

declare(strict_types=1);

return [
    'command' => 'hello',
    'description' => 'Say hello',
    'handler' => static function (
        \CcCheckerBot\Bot\BotContext $bot,
        array $message,
    ): void {
        $bot->sendMsg('Hello from my new command!');
    },
];
```

That's all: no class, no command-map edit, no separate handler registration, and
no edits to the Telegram command menu. The command name and menu description
live in the same file. The `$bot->sendMsg(...)` helper automatically uses the
current update's chat ID, so replies go to the correct private chat or group.
The context also provides `$bot->arguments()`, `$bot->sender()`,
`$bot->users()`, `$bot->telegram()`, and `$bot->chatId()`.

All active commands are standalone files loaded from `General/`, `Tools/`, `Gates/`, and nested folders. There is no command-class map to update. Each file owns its command name, help description, and handler.

## Run locally

Requirements: PHP 8.2+, Composer 2, `ext-curl`, `ext-json`, and a Telegram bot token from [@BotFather](https://t.me/BotFather).

```bash
git clone https://github.com/chethanhrx/ccChecker-bot.git
cd ccChecker-bot
composer install
cp .env.example .env
```

Set the token in `.env`:

```dotenv
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_POLL_TIMEOUT=25
```

Start the worker:

```bash
composer start
```

Keep the terminal open; press Ctrl+C to stop the bot. Never commit or share your bot token.

## Quality checks

```bash
composer validate --no-check-publish --no-check-lock
composer lint
composer test
```

The repository includes a PHPUnit suite and a PHP-version matrix for later verification. During this development pass, the focus is implementation and code review; run the checks locally before deploying.

## How FlintPHP is used

The worker boots a FlintPHP `Application` and uses its container to compose the Telegram client, profile store, and dispatcher. Telegram long polling and Bot API transport are kept as separate CLI-oriented components rather than misrepresented as framework HTTP routes.

## Security notes

- Store the Telegram token only in the private `.env` file.
- The profile JSON file is for local development, not concurrent production use.
- Do not send real payment data, passwords, or banking credentials to the bot.
- For a public production bot, add database transactions, rate limiting, structured logging, privacy/retention controls, and deployment monitoring.
