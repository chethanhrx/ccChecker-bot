<?php
declare(strict_types=1);
return ['command'=>'id','description'=>'Show your Telegram ID','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$id = isset($bot->sender()['id']) ? (string)$bot->sender()['id'] : 'unavailable';
$bot->sendMsg("🪪 <b>Your Telegram ID</b>\n<code>".htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')."</code>");
}];