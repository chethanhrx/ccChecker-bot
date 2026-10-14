<?php
declare(strict_types=1);
return ['command'=>'account','description'=>'View your demo profile','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$id=$bot->sender()['id']??null; $user=$id!==null?$bot->users()->find((string)$id):null;
if($user===null){$bot->sendMsg('You have not registered yet. Use /register to create a demo profile.', \CcCheckerBot\UI\Keyboards::home());return;}
$e=static fn(string $v):string=>htmlspecialchars($v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); $username=(string)($user['username']??''); $username=$username===''?'Not set':'@'.$e($username); $name=trim((string)($user['first_name']??'').' '.(string)($user['last_name']??''));
$bot->sendMsg("👤 <b>Demo account</b>\n\nName: ".$e($name)."\nUsername: ".$username."\nTelegram ID: <code>".$e((string)($user['telegram_id']??$id))."</code>\nSandbox credits: <b>".max(0,(int)($user['demo_credits']??0))."</b>\nStatus: <b>Registered</b>", \CcCheckerBot\UI\Keyboards::dashboard());
}];