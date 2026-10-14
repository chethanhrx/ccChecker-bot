<?php
declare(strict_types=1);
return ['command'=>'chargedemo','description'=>'Run a fixed charge mock','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$id=$bot->sender()['id']??null;if($id===null||$bot->users()->find((string)$id)===null){$bot->sendMsg('Please register first to unlock the demo tools.',\CcCheckerBot\UI\Keyboards::home());return;}
$r=(new \CcCheckerBot\Demo\MockScenario())->result('charge');$e=static fn(string $v):string=>htmlspecialchars($v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
$bot->sendMsg('🧪 <b>'.$e($r['title'])."</b>\n\nStatus: <code>".$e($r['status'])."</code>\n".$e($r['message']),\CcCheckerBot\UI\Keyboards::tools());
}];