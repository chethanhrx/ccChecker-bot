<?php
declare(strict_types=1);
return ['command'=>'help','description'=>'Show command help','handler'=>static function(array $c):void{bot_send($c['chat_id'],"Use /cmd to view all available commands. Add a new command by creating one PHP file in /commands.",bot_keyboard_dashboard());}];