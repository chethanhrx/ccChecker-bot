<?php
declare(strict_types=1);
return ['command'=>'support','description'=>'Troubleshooting help','handler'=>static function(array $c):void{bot_send($c['chat_id'],"<b>🛟 Help & support</b>\n\nUse /cmd for commands, /ping to check the worker, and /privacy for stored-data details. Never include your bot token or private credentials in bug reports.");}];