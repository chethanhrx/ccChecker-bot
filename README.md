# FlintBot — Native PHP Telegram Bot

A lightweight Telegram bot built with **native PHP 8.2+** and the official Telegram Bot API. No web framework, Composer, or third-party runtime packages are required.

> **Sandbox boundary:** `/chk` accepts synthetic demo references only. It does not accept or validate payment-card numbers, expiry dates, or CVVs. `/bin` does not perform issuer lookups. Purchase/redeem commands are simulations only; no payment is taken, no paid entitlement is granted, and no payment gateway is contacted.

## Features
- Native CLI long polling with persisted update offsets and bounded retry backoff
- Direct Telegram Bot API transport using PHP cURL and JSON
- Explicit bootstrap with no Composer autoloader or framework
- Standalone command files recursively discovered at startup
- New commands appear automatically in `/cmd` and Telegram's command menu
- Inline keyboards, callbacks, local demo profiles, simulated credits, privacy/support commands
- Dependency-free PHP tests and GitHub Actions CI for PHP 8.2, 8.3, and 8.4

## Commands
| Command | Purpose |
|---|---|
| `/start` | Welcome screen and menu |
| `/cmd` or `/help` | Command guide |
| `/id` | Show Telegram user ID |
| `/info` | Runtime and sandbox status |
| `/register` | Create local demo profile |
| `/account` | View demo profile |
| `/deleteaccount` | Delete demo profile |
| `/buy` | Simulated plans; no checkout |
| `/balance` | View demo credits |
| `/redeem DEMO-STARTER` | Redeem 10 demo credits once |
| `/redeem DEMO-LAB` | Redeem 25 demo credits once |
| `/chk DEMO-1234` | Synthetic scenario only |
| `/bin` | BIN lookup is disabled |
| `/ping` | Worker response check |
| `/status` | PHP process/memory diagnostics |
| `/privacy` | Local-data and privacy notes |
| `/support` | Troubleshooting guidance |
| `/tools` | Registration-gated demo menu |
| `/authdemo`, `/chargedemo` | Fixed simulated outcomes |

The `/chk` command rejects pipe-delimited values and long numeric strings to reduce accidental submission of payment-card data. It does not perform Luhn checks or payment authorization. Demo credits have no monetary value.

## Project layout
```text
app/
  Bot/BotContext.php
  Bot/UpdateDispatcher.php
  Bot/Commands/{General,Tools,Gates}/
  Demo/MockScenario.php
  Storage/UserStore.php
  Support/Env.php
  Telegram/{TelegramApi,TelegramClientInterface}.php
  UI/Keyboards.php
bootstrap.php
bot.php
tests/Support/
tests/Unit/
tests/run.php
.env.example
```

## Add a command — one PHP file
Create any PHP file under `app/Bot/Commands/`, e.g. `app/Bot/Commands/General/helloagain.php`:

```php
<?php
declare(strict_types=1);

return [
    'command' => 'helloagain',
    'description' => 'Say hello again',
    'handler' => static function (\CcCheckerBot\\Bot\\BotContext $bot, array $message): void {
        $bot->sendMsg('Hello from my new command!');
    },
];
```

That's it: no class, command map, registration step, Composer update, or Telegram-menu edit. The command file owns its name, description, and handler. The dispatcher discovers it at startup. `BotContext` provides `sendMsg()`, `chatId()`, `sender()`, `arguments()`, `users()`, `telegram()`, and `commands()`.

## Requirements
- PHP 8.2+
- PHP cURL extension (`ext-curl`)
- PHP JSON extension (`ext-json`, normally bundled)
- Telegram bot token from [@BotFather](https://t.me/BotFather)

No Composer installation or external PHP package is needed.

## Run locally
```bash
git clone https://github.com/chethanhrx/ccChecker-bot.git
cd ccChecker-bot
cp .env.example .env
```
Set `TELEGRAM_BOT_TOKEN` in `.env`, then run:
```bash
php bot.php
```
Keep the terminal open; press Ctrl+C to stop. Never commit or share the token.

## Quality checks
```bash
find app tests -name '*.php' -print0 | xargs -0 -n1 php -l
php -l bot.php
php -l bootstrap.php
php tests/run.php
```

## Security notes
- Keep the Telegram token in the private `.env` file.
- JSON profile storage is for local development, not high-concurrency production.
- Never send real payment data, passwords, or banking credentials to the bot.
- Public deployments need rate limiting, structured logging, privacy/retention controls, robust storage transactions, and monitoring.
