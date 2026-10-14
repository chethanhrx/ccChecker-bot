<?php
declare(strict_types=1);
require __DIR__.'/functions.php';
bot_load_env(__DIR__.'/.env');
if (!function_exists('curl_init')) { fwrite(STDERR,"PHP cURL extension is required.\n"); exit(1); }
if (bot_env('TELEGRAM_BOT_TOKEN')==='') { fwrite(STDERR,"Missing TELEGRAM_BOT_TOKEN. Copy .env.example to .env and configure it.\n"); exit(1); }
$timeout=max(1,min(50,(int)bot_env('TELEGRAM_POLL_TIMEOUT','25')));
$storage=__DIR__.'/storage';
try {
    if (!is_dir($storage)&&!mkdir($storage,0775,true)&&!is_dir($storage)) throw new RuntimeException('Cannot create storage directory.');
    bot_api('deleteWebhook',['drop_pending_updates'=>false]);
    $me=bot_api('getMe');
    bot_api('setMyCommands',['commands'=>bot_command_menu()]);
} catch(Throwable $e) { fwrite(STDERR,'Bot startup failed: '.$e->getMessage()."\n"); exit(1); }
fwrite(STDOUT,'Native PHP bot started as @'.($me['username']??'unknown').". Press Ctrl+C to stop.\n");
$offsetFile=$storage.'/offset.txt'; $saved=is_file($offsetFile)?file_get_contents($offsetFile):false;
$offset=is_string($saved)&&ctype_digit(trim($saved))?(int)trim($saved):0; $backoff=1;
while(true) {
    try {
        $updates=bot_api('getUpdates',['offset'=>$offset,'timeout'=>$timeout,'allowed_updates'=>['message','callback_query']],max(65,$timeout+15));
        if($updates===[])$backoff=1;
        foreach($updates as $update) {
            try { bot_dispatch($update); }
            catch(Throwable $e) { error_log('[bot] Update failed: '.$e->getMessage()); sleep($backoff); $backoff=min(30,$backoff*2); break; }
            $backoff=1; $offset=max($offset,(int)($update['update_id']??0)+1);
            if(file_put_contents($offsetFile,(string)$offset,LOCK_EX)===false)error_log('[bot] Could not persist update offset.');
        }
    } catch(Throwable $e) { error_log('[bot] Polling failed: '.$e->getMessage()); sleep($backoff); $backoff=min(30,$backoff*2); }
}
