<?php
declare(strict_types=1);
return ['command'=>'deleteaccount','description'=>'Delete your local demo profile','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$id=$bot->sender()['id']??null; if($id===null){$bot->sendMsg('I could not identify your Telegram profile.');return;}
if(!$bot->users()->delete((string)$id)){$bot->sendMsg('No registered demo profile was found.',\CcCheckerBot\UI\Keyboards::home());return;}
$bot->sendMsg('Your local demo profile has been deleted.',\CcCheckerBot\UI\Keyboards::home());
}];