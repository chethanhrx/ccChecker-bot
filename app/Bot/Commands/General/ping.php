<?php
declare(strict_types=1);
return ['command'=>'ping','description'=>'Check that the worker responds','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$bot->sendMsg("🏓 <b>Pong!</b>\nThe bot worker handled your command. This is an application-level check, not a network latency measurement.");
}];