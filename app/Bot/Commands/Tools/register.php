<?php
declare(strict_types=1);
return ['command'=>'register','description'=>'Create a local demo profile','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$sender=$bot->sender(); if (!isset($sender['id'])) { $bot->sendMsg('Telegram profile details are unavailable. Please try again.'); return; }
$bot->users()->register($sender); $bot->sendMsg('✅ <b>Registration complete.</b> Your local demo profile is ready.', \CcCheckerBot\UI\Keyboards::dashboard());
}];