<?php
declare(strict_types=1);
return ['command'=>'cmd','description'=>'List available commands','handler'=>static function(array $ctx): void {
    $lines=[]; foreach($ctx['commands'] as $name=>$d) $lines[]=htmlspecialchars('/'.$name,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8').' — '.htmlspecialchars($d['description']?:'No description',ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
    bot_send($ctx['chat_id'],"<b>🧭 Command centre</b>\n\nUsage examples: /redeem DEMO-STARTER and /chk DEMO-1234\n\n".implode("\n",$lines),bot_keyboard_dashboard());
}];
