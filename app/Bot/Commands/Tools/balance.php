<?php
declare(strict_types=1);
return ['command'=>'balance','description'=>'View your sandbox balance','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$id=$bot->sender()['id']??null;$p=$id!==null?$bot->users()->find((string)$id):null;
if($p===null){$bot->sendMsg('Register with /register before checking your sandbox balance.',\CcCheckerBot\UI\Keyboards::home());return;}
$redeemed=is_array($p['redeemed_codes']??null)?$p['redeemed_codes']:[];$codes=$redeemed===[]?'None yet':implode(', ',array_map(static fn($c)=>htmlspecialchars((string)$c,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'),$redeemed));
$bot->sendMsg("<b>🪙 Sandbox balance</b>\n\nAvailable demo credits: <b>".max(0,(int)($p['demo_credits']??0))."</b>\nRedeemed codes: ".$codes."\n\nCredits are local testing counters and have no monetary value.",\CcCheckerBot\UI\Keyboards::dashboard());
}];