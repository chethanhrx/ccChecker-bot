<?php
declare(strict_types=1);
return ['command'=>'tools','description'=>'Open the sandbox tools menu','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$id=$bot->sender()['id']??null;if($id===null||$bot->users()->find((string)$id)===null){$bot->sendMsg('Please register first to unlock the demo tools.',\CcCheckerBot\UI\Keyboards::home());return;}
$bot->sendMsg("🧰 <b>Sandbox tools</b>\n\nChoose a synthetic scenario below. These buttons return fixed demo outcomes only; they do not perform real payment authorizations or charges.",\CcCheckerBot\UI\Keyboards::tools());
}];