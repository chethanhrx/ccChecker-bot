# ccChecker Bot — plain PHP

A lightweight Telegram long-polling bot written in ordinary PHP functions. No Composer, namespaces, application classes, interfaces, framework, or autoloader.

**Sandbox boundary:** `/chk` accepts synthetic demo references only. It rejects card-like values and does not verify payment cards. `/bin` is disabled. Plans, authorization, and charge commands are simulations; no payment is taken and no payment service is contacted.

## Requirements

- PHP 8.2+
- PHP cURL and JSON extensions
- Telegram bot token from [@BotFather](https://t.me/BotFather)

## Run

```bash
git clone https://github.com/chethanhrx/ccChecker-bot.git
cd ccChecker-bot
cp .env.example .env
```

Set `TELEGRAM_BOT_TOKEN` in `.env`, then run `php bot.php`. Keep the terminal open and press Ctrl+C to stop. Never commit the token.

## Add a command — one file

Create `commands/helloagain.php`:

```php
<?php
declare(strict_types=1);

return [
    'command' => 'helloagain',
    'description' => 'Say hello again',
    'handler' => static function (array $ctx): void {
        bot_send($ctx['chat_id'], 'Hello from my new command!');
    },
];
```

That's it. No class, namespace, command registry, dispatcher edit, or dependency installation. Command files return their own name, description, and handler and are discovered automatically at startup. The handler receives an array with `chat_id`, `sender`, `arguments`, `message`, and `commands`.

Useful helpers include `bot_send()`, `bot_keyboard_home()`, `bot_keyboard_dashboard()`, `bot_user_find()`, `bot_user_register()`, `bot_user_delete()`, and `bot_user_redeem()`.

## Commands

- `/start`, `/cmd`, `/help`, `/hello`, `/info`, `/ping`, `/status`
- `/id`, `/privacy`, `/support`
- `/register`, `/account`, `/deleteaccount`, `/balance`, `/redeem DEMO-STARTER`
- `/buy`, `/tools`, `/authdemo`, `/chargedemo`
- `/chk DEMO-1234` (synthetic-only), `/bin` (disabled)

## Quality checks

```bash
find . -path './.git' -prune -o -name '*.php' -print0 | xargs -0 -n1 php -l
php tests/run.php
```

## Security notes

Keep the bot token in the private `.env` file. JSON profile storage is intended for a small demo, not high-concurrency production. Never send real payment data, passwords, or banking credentials to this bot.
