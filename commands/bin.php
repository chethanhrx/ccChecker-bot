<?php
declare(strict_types=1);
return ['command'=>'bin','description'=>'BIN lookup is disabled','handler'=>static function(array $c):void{bot_send($c['chat_id'],"🔒 <b>BIN lookup is disabled</b>\n\nThis demo does not identify issuers from payment-card prefixes or perform card-enrichment lookups.");}];