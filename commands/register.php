<?php
declare(strict_types=1);
return ['command'=>'register','description'=>'Create a local demo profile','handler'=>static function(array $ctx): void {
    if(!isset($ctx['sender']['id'])) { bot_send($ctx['chat_id'],'Telegram profile details are unavailable. Please try again.'); return; }
    bot_user_register($ctx['sender']); bot_send($ctx['chat_id'],'✅ <b>Registration complete.</b> Your local demo profile is ready.',bot_keyboard_dashboard());
}];
