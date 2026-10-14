<?php
declare(strict_types=1);
return ['command'=>'start','description'=>'Open the main menu','handler'=>static function(array $ctx): void {
    $name=htmlspecialchars((string)($ctx['sender']['first_name']??'there'),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
    bot_send($ctx['chat_id'],"👋 <b>Welcome to ccChecker Bot, {$name}!</b>\n\nA native PHP sandbox with local demo profiles and simulated credits.\n\n<i>No real card data or payment processing is supported.</i>",bot_keyboard_home());
}];
