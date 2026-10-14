<?php
declare(strict_types=1);
return ['command'=>'privacy','description'=>'Review demo privacy and stored data','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$bot->sendMsg("<b>🔐 Demo privacy</b>\n\n• The demo profile store keeps your Telegram ID, display name, username, and registration timestamp locally.\n• Do not send card numbers, expiry dates, CVVs, passwords, or banking details.\n• /deleteaccount removes your local demo profile.\n• /info explains what this bot does.\n\nThis is a starter project, not a certified privacy or payment service.");
}];