<?php
declare(strict_types=1);
return ['command'=>'ping','description'=>'Check bot response','handler'=>static function(array $c):void{bot_send($c['chat_id'],"🏓 <b>Pong!</b>\nThe bot handled your command.");}];