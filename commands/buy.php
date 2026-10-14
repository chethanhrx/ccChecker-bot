<?php
declare(strict_types=1);
return ['command'=>'buy','description'=>'View sandbox plan examples','handler'=>static function(array $c):void{bot_send($c['chat_id'],"<b>🛍️ Sandbox plans</b>\n\n🟢 <b>Starter</b> — 10 demo credits\n🟣 <b>Lab</b> — 25 demo credits\n\nPreview only: no checkout or payment is connected. Use /redeem DEMO-STARTER or /redeem DEMO-LAB.",bot_keyboard_dashboard());}];