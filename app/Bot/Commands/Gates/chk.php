<?php
declare(strict_types=1);
return ['command'=>'chk','description'=>'Run a synthetic sandbox scenario','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$input=trim($bot->arguments());
if(str_contains($input,'|')||preg_match('/\b\d{12,19}\b/',$input)===1){$bot->sendMsg("🛡️ This sandbox does not accept payment-card numbers, expiry dates, or CVVs. Please do not send financial details.\n\nUse <code>/chk DEMO-1234</code> for a synthetic scenario.");return;}
if(preg_match('/^DEMO-[A-Z0-9]{4,12}$/i',$input)!==1){$bot->sendMsg("Usage: <code>/chk DEMO-1234</code>\nOnly synthetic demo IDs are accepted; real card checks are not available.");return;}
$bot->sendMsg("🧪 <b>Sandbox check result</b>\nReference: <code>".htmlspecialchars(strtoupper($input),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8')."</code>\nResult: <code>SIMULATED_ONLY</code>\nNo card, bank, BIN, or payment gateway was contacted.");
}];