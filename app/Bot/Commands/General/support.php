<?php
declare(strict_types=1);
return ['command'=>'support','description'=>'Get troubleshooting help','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$bot->sendMsg("<b>🛟 Help & support</b>\n\nUse /cmd for the command list, /ping to check that the worker responds, and /privacy to review stored demo data.\n\nIf a command fails, note the command name and check the worker terminal logs. Never include your bot token or private credentials in a bug report.");
}];