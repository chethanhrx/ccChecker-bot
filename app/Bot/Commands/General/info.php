<?php
declare(strict_types=1);
return ['command'=>'info','description'=>'About this demo bot','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$registered = isset($bot->sender()['id']) && $bot->users()->find((string)$bot->sender()['id']) !== null;
$bot->sendMsg("<b>ℹ️ FlintBot sandbox</b>\n\nRuntime: PHP ".PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION."\nFramework: FlintPHP\nMode: simulated demo\nProfile: ".($registered?'registered':'guest')."\n\nNo real payment authorization, card verification, or financial-data storage is performed.");
}];