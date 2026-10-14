<?php
declare(strict_types=1);
return ['command'=>'buy','description'=>'View sandbox plan examples','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$bot->sendMsg("<b>🛍️ Sandbox plans</b>\n\n🟢 <b>Starter</b> — 10 demo credits\n🟣 <b>Lab</b> — 25 demo credits\n\nThis is a preview only. Checkout is not connected, no payment is taken, and these plans do not grant entitlements.\n\nRegister with /register and redeem /redeem DEMO-STARTER or /redeem DEMO-LAB.",\CcCheckerBot\UI\Keyboards::dashboard());
}];