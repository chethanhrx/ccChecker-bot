# FlintPHP Telegram Bot

A local-first Telegram bot template built around **FlintPHP** concepts: long polling, command routing, inline keyboards, callback queries, registration, and modular demo tools.

> **Safety note:** This project is a developer/demo template. Its AUTH and CHARGE examples are simulated UI flows only. It does not accept payment-card numbers, validate real cards, contact payment gateways, authorize transactions, or charge money.

## Goals

- Start locally with `php bot.php` — no webhook or public URL required.
- Use Telegram long polling (`getUpdates`) and callback queries.
- Show a welcome screen with Register, Dashboard, Tools, and Help buttons.
- Persist basic Telegram user registration locally in JSON.
- Demonstrate AUTH/CHARGE menu flows with deterministic mock outcomes only.
- Keep transport, dispatch, storage, and demo logic separated and testable.

## Requirements

- PHP 8.2+
- Composer
- A Telegram bot token from [@BotFather](https://t.me/BotFather)
- The published `flintphp/framework` package (or a local path repository while developing against FlintPHP main)

## Setup

```bash
composer install
cp .env.example .env
# Set TELEGRAM_BOT_TOKEN in .env
php bot.php
```

The bot process must remain running. Press Ctrl+C to stop it. Long polling and webhooks are mutually exclusive for a given Telegram bot; if a webhook is already configured, remove it before using this local polling runner.

## Commands

- `/start` — welcome screen
- `/help` — command guide
- `/account` — registration/account status
- `/tools` — demo tools menu
- `/authdemo` — simulated AUTH result
- `/chargedemo` — simulated CHARGE result

## Project layout

```text
app/
  Bot/UpdateDispatcher.php
  Storage/UserStore.php
  Support/Env.php
  Telegram/TelegramApi.php
  UI/Keyboards.php
  Demo/MockPaymentSimulator.php
storage/                 # generated local JSON data; ignored by Git
tests/
bot.php
composer.json
.env.example
```

## Security

- Never commit `.env` or bot tokens.
- The bot only stores Telegram user ID, username, display name, and registration timestamp.
- Do not send card numbers, CVVs, banking credentials, or real payment data to this template.
- AUTH/CHARGE are labels for mock demo scenarios; they do not perform payment operations.
- For production, use a durable database, explicit privacy notice, access controls, rate limiting, structured logs, and deployment-specific secret management.

## FlintPHP integration

The framework is declared as a Composer dependency. The bot runs as a CLI long-polling worker rather than an HTTP webhook application; the Telegram transport is intentionally kept behind a small adapter so it can be tested and evolved independently. We'll align any deeper framework-specific integration with the exact public APIs available in the selected FlintPHP release.
