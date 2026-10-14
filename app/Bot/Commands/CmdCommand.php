<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands;

use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;
use CcCheckerBot\UI\Keyboards;

final class CmdCommand implements CommandHandler
{
    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $telegram->sendMessage(
            $chatId,
            "<b>🧭 Command centre</b>\n\n" .
            "/start — Main menu\n/cmd — Command list\n/id — Your Telegram ID\n/info — Bot information\n" .
            "/register — Create a local demo profile\n/account — View your profile\n/deleteaccount — Delete your profile\n" .
            "/buy — View simulated plans\n/redeem DEMO-STARTER — Redeem a demo code\n" .
            "/chk DEMO-1234 — Run a sandbox scenario\n/bin — BIN lookup status\n/ping — Worker response check\n" .
            "/tools — Demo menu\n/authdemo and /chargedemo — Fixed mock scenarios",
            Keyboards::dashboard(),
        );
    }
}
