<?php
declare(strict_types=1);
return ['command'=>'redeem','description'=>'Redeem a fixed demo code','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$id=$bot->sender()['id']??null;if($id===null){$bot->sendMsg('Telegram profile details are unavailable. Please try again.');return;}
$code=strtoupper(trim($bot->arguments()));if($code===''){$bot->sendMsg("Usage: <code>/redeem DEMO-STARTER</code>\nAvailable sandbox codes: DEMO-STARTER (10 credits), DEMO-LAB (25 credits).");return;}
$r=$bot->users()->redeemDemoCode((string)$id,$code);
switch($r['status']){
case 'not_registered':$bot->sendMsg('Register first with /register. Demo credits are attached to your local profile.',\CcCheckerBot\UI\Keyboards::home());return;
case 'invalid_code':$bot->sendMsg('❌ Unknown demo code. No credits were added.');return;
case 'already_redeemed':$bot->sendMsg('ℹ️ That code has already been redeemed on your profile. Current demo balance: <b>'.(string)$r['balance'].'</b> credits.');return;
case 'redeemed':$bot->sendMsg("✅ <b>Demo code redeemed</b>\n\nAdded: <b>".(string)$r['credits']."</b> sandbox credits\nBalance: <b>".(string)$r['balance']."</b> sandbox credits\n\nThese credits are local test counters only; they have no monetary value.",\CcCheckerBot\UI\Keyboards::dashboard());return;
}
}];