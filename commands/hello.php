<?php
declare(strict_types=1);
return ['command'=>'hello','description'=>'Say hello','handler'=>static function(array $ctx): void {
    $name=htmlspecialchars((string)($ctx['sender']['first_name']??'there'),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
    bot_send($ctx['chat_id'],"👋 Hello, <b>{$name}</b>! This command was auto-discovered.");
}];
