# FlintPHP Telegram Bot

A modular, local-first Telegram bot starter using **FlintPHP 1.1+**, PHP 8.2+, and Telegram's Bot API. It uses long polling, so you can run it on your Linux laptop without exposing a public webhook URL.

> **Demo scope:** The AUTH and CHARGE menu items are fixed mock scenarios for testing bot menus and command routing. They do not accept card details, validate payment cards, call payment gateways, authorize payments, or move money.

## Features

- CLI long polling with persisted update offset and bounded retry backoff
- FlintPHP `Application` and PSR-11 container for dependency composition
- `/start`, `/help`, `/account`, `/tools`, `/authdemo`, and `/chargedemo`
- Inline keyboard navigation and callback-query handling
- User registration and profile persistence in a local JSON file
- Telegram command menu configured on startup
- Environment-based token configuration
- Unit tests and GitHub Actions CI for PHP 8.2, 8.3, and 8.4

## Requirements

- PHP 8.2 or newer
- Composer 2
- PHP extensions `curl` and `json`
- A Telegram bot token created with [@BotFather](https://t.me/BotFather)

## Run locally

```bash
git clone https://github.com/chethanhrx/ccChecker-bot.git
cd ccChecker-bot
composer install
cp .env.example .env
```

Open `.env` and set your token:

```dotenv
TELEGRAM_BOT_TOKEN=put_your_bot_token_here
TELEGRAM_POLL_TIMEOUT=25
```

Then start the worker:

```bash
composer start
```

Keep that terminal open while using the bot. Press **Ctrl+C** to stop it. The token is secret: do not post it in chat, commit it, or include it in screenshots.

### Long polling details

On startup, the worker verifies the token, registers the command menu, and removes any existing webhook configuration because Telegram does not allow polling and webhooks to receive updates simultaneously. Existing pending updates are not intentionally discarded. The process persists the update offset across restarts and retries transient polling or dispatch failures with bounded backoff.

## Commands

| Command | Purpose |
|---|---|
| `/start` | Open the main menu |
| `/help` | Show command guide |
| `/account` | View registration status and profile |
| `/deleteaccount` | Delete your local demo profile |
| `/tools` | Open the demo tools menu |
| `/authdemo` | Return a fixed simulated result |
| `/chargedemo` | Return a fixed simulated result |

The main menu also provides **Register**, **Account**, **Tools**, and **Help** inline buttons. User records store Telegram ID, username, first name, last name, and registration timestamp in `storage/users.json`. Use `/deleteaccount` to remove your local demo profile.

## Project structure

```text
app/
  Bot/UpdateDispatcher.php
  Demo/MockScenario.php
  Storage/UserStore.php
  Support/Env.php
  Telegram/TelegramApi.php
  Telegram/TelegramClientInterface.php
  UI/Keyboards.php
tests/
  Unit/MockScenarioTest.php
  Unit/UpdateDispatcherTest.php
  Unit/UserStoreTest.php
  Support/FakeTelegramClient.php
bot.php
composer.json
phpunit.xml
.env.example
```

## Tests and quality checks

```bash
composer lint
composer test
composer validate --no-check-publish --no-check-lock
```

GitHub Actions runs the lint and unit-test commands on PHP 8.2, 8.3, and 8.4. A green workflow is the verification target; do not assume the project is tested until the workflow has actually completed successfully.

## Security and privacy

- Keep `.env` private. Rotate the token immediately if it is exposed.
- Only Telegram profile fields needed for registration are stored.
- Do not send card numbers, CVVs, passwords, banking credentials, or other sensitive financial data to this demo.
- AUTH/CHARGE are names for simulated menu examples only. They have no real processor integration.
- JSON storage is intended for a local starter. Before a public deployment, replace it with a database designed for concurrent access, add retention/deletion controls, access controls, rate limiting, monitoring, and a privacy notice.

## How FlintPHP is used

The CLI worker creates a FlintPHP `Application`, registers the Telegram API client and profile store in its container, and resolves the update dispatcher through that container. Telegram transport and update handling remain separate classes so they can be tested and changed independently. This project does not pretend that Telegram's Bot API is an HTTP route in FlintPHP: it is a long-running CLI worker using the framework's dependency-composition facilities.
