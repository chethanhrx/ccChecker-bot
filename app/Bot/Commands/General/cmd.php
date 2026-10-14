<?php
declare(strict_types=1);
return ['command'=>'cmd','description'=>'List available commands','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$commands = $GLOBALS['__unused_commands'] ?? [];
$bot->sendMsg("<b>🧭 Command centre</b>\n\n/start — Main menu\n/cmd — Command list\n/help — Command list\n/hello — Say hello\n/id — Your Telegram ID\n/info — Bot information\n/register — Create a local demo profile\n/account — View your profile\n/deleteaccount — Delete your profile\n/buy — View sandbox plans\n/balance — View demo credit balance\n/redeem DEMO-STARTER — Redeem a demo code\n/chk DEMO-1234 — Run a synthetic sandbox scenario\n/bin — BIN lookup status (disabled)\n/ping — Worker response check\n/status — Worker diagnostics\n/privacy — Privacy notes\n/support — Troubleshooting help\n/tools — Demo menu\n/authdemo and /chargedemo — Fixed mock scenarios", \CcCheckerBot\UI\Keyboards::dashboard());
}];