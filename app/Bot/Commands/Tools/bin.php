<?php
declare(strict_types=1);
return ['command'=>'bin','description'=>'BIN lookup is disabled in this demo','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$bot->sendMsg("🔒 <b>BIN lookup is disabled</b>\n\nThis demo does not identify card issuers from payment-card prefixes or perform card-enrichment lookups. Use /info to see the sandbox scope.");
}];