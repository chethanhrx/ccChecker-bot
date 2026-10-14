<?php
declare(strict_types=1);
return ['command'=>'id','description'=>'Show your Telegram ID','handler'=>static function(array $c):void{$id=htmlspecialchars((string)($c['sender']['id']??'unavailable'),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');bot_send($c['chat_id'],"🪪 <b>Your Telegram ID</b>\n<code>{$id}</code>");}];