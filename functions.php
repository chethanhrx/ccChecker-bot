<?php
declare(strict_types=1);

/*
 * ccChecker-bot procedural core.
 * No namespaces, application classes, Composer, or autoloader.
 */

function bot_load_env(string $path): void {
    if (!is_readable($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key === '' || getenv($key) !== false) continue;
        if (strlen($value) >= 2 && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) $value = substr($value, 1, -1);
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}
function bot_env(string $key, string $fallback = ''): string { $v = getenv($key); return $v === false ? $fallback : (string)$v; }
function bot_api(string $method, array $payload = [], int $timeout = 20): array {
    $token = bot_env('TELEGRAM_BOT_TOKEN');
    if ($token === '') throw new RuntimeException('Missing TELEGRAM_BOT_TOKEN.');
    if (!function_exists('curl_init')) throw new RuntimeException('PHP cURL extension is required.');
    $curl = curl_init("https://api.telegram.org/bot{$token}/{$method}");
    if ($curl === false) throw new RuntimeException('Could not initialize Telegram transport.');
    curl_setopt_array($curl, [CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($payload,JSON_THROW_ON_ERROR),CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>$timeout]);
    $body=curl_exec($curl); $status=(int)curl_getinfo($curl,CURLINFO_RESPONSE_CODE); $error=curl_error($curl); curl_close($curl);
    if (!is_string($body)) throw new RuntimeException('Telegram transport failed: '.($error ?: 'unknown error'));
    $data=json_decode($body,true);
    if (!is_array($data)||($data['ok']??false)!==true) throw new RuntimeException("Telegram API {$method} failed (HTTP {$status}): ".(is_array($data)?($data['description']??'invalid response'):'invalid JSON'));
    return is_array($data['result']??null)?$data['result']:[];
}
function bot_send(int|string $chatId,string $text,array $keyboard=[]): array {
    $p=['chat_id'=>$chatId,'text'=>$text,'parse_mode'=>'HTML','disable_web_page_preview'=>true];
    if ($keyboard!==[]) $p['reply_markup']=$keyboard;
    return bot_api('sendMessage',$p);
}
function bot_answer_callback(string $id): void { if ($id!=='') bot_api('answerCallbackQuery',['callback_query_id'=>$id]); }
function bot_keyboard_home(): array { return ['inline_keyboard'=>[[['text'=>'📝 Register','callback_data'=>'register'],['text'=>'👤 Account','callback_data'=>'account']],[['text'=>'🧰 Tools','callback_data'=>'tools'],['text'=>'❔ Help','callback_data'=>'help']]]]; }
function bot_keyboard_dashboard(): array { return ['inline_keyboard'=>[[['text'=>'👤 Account','callback_data'=>'account'],['text'=>'🪙 Balance','callback_data'=>'balance']],[['text'=>'🧰 Tools','callback_data'=>'tools'],['text'=>'🏠 Home','callback_data'=>'home']],[['text'=>'❔ Help','callback_data'=>'help']]]]; }
function bot_keyboard_tools(): array { return ['inline_keyboard'=>[[['text'=>'🧪 AUTH demo','callback_data'=>'auth_demo'],['text'=>'🧪 CHARGE demo','callback_data'=>'charge_demo']],[['text'=>'🪙 Balance','callback_data'=>'balance'],['text'=>'👤 Account','callback_data'=>'account']],[['text'=>'🏠 Home','callback_data'=>'home']]]]; }
function bot_profiles_path(): string { return (string)($GLOBALS['bot_test_storage_path'] ?? (__DIR__.'/storage/users.json')); }
function bot_profiles_read(): array {
    $path=bot_profiles_path(); if (!is_file($path)) return [];
    $raw=file_get_contents($path); if (!is_string($raw)||trim($raw)==='') return [];
    $data=json_decode($raw,true); if (!is_array($data)) throw new RuntimeException('Profile store contains invalid JSON.');
    return $data;
}
function bot_profiles_write(array $data): void {
    $path=bot_profiles_path(); if (!is_dir(dirname($path))&&!mkdir(dirname($path),0775,true)&&!is_dir(dirname($path))) throw new RuntimeException('Cannot create storage directory.');
    $tmp=$path.'.tmp'; $json=json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
    if (file_put_contents($tmp,$json,LOCK_EX)===false||!rename($tmp,$path)) { @unlink($tmp); throw new RuntimeException('Cannot save profiles.'); }
}
function bot_user_find(int|string $id): ?array { $v=bot_profiles_read()[(string)$id]??null; return is_array($v)?$v:null; }
function bot_user_register(array $sender): array {
    $id=(string)($sender['id']??''); if ($id==='') throw new RuntimeException('Telegram profile ID is missing.');
    $all=bot_profiles_read(); $old=is_array($all[$id]??null)?$all[$id]:[];
    $profile=['telegram_id'=>$id,'username'=>(string)($sender['username']??''),'first_name'=>(string)($sender['first_name']??''),'last_name'=>(string)($sender['last_name']??''),'registered_at'=>(string)($old['registered_at']??gmdate(DATE_ATOM)),'demo_credits'=>max(0,(int)($old['demo_credits']??0)),'redeemed_codes'=>is_array($old['redeemed_codes']??null)?array_values($old['redeemed_codes']):[]];
    $all[$id]=$profile; bot_profiles_write($all); return $profile;
}
function bot_user_delete(int|string $id): bool { $all=bot_profiles_read(); if (!array_key_exists((string)$id,$all)) return false; unset($all[(string)$id]); bot_profiles_write($all); return true; }
function bot_user_redeem(int|string $id,string $code): array {
    $all=bot_profiles_read(); $key=(string)$id; if (!isset($all[$key])||!is_array($all[$key])) return ['status'=>'not_registered','credits'=>0];
    $rewards=['DEMO-STARTER'=>10,'DEMO-LAB'=>25]; $code=strtoupper(trim($code));
    if (!isset($rewards[$code])) return ['status'=>'invalid_code','credits'=>0];
    $p=$all[$key]; $redeemed=is_array($p['redeemed_codes']??null)?$p['redeemed_codes']:[];
    if (in_array($code,$redeemed,true)) return ['status'=>'already_redeemed','credits'=>0,'balance'=>max(0,(int)($p['demo_credits']??0))];
    $balance=max(0,(int)($p['demo_credits']??0))+$rewards[$code]; $p['demo_credits']=$balance; $p['redeemed_codes']=[...$redeemed,$code]; $all[$key]=$p; bot_profiles_write($all);
    return ['status'=>'redeemed','credits'=>$rewards[$code],'balance'=>$balance];
}
function bot_commands(): array {
    static $commands=null; if ($commands!==null) return $commands;
    $commands=[];
    foreach (glob(__DIR__.'/commands/*.php')?:[] as $file) {
        try { $def=require $file; } catch (Throwable $e) { error_log('Command load failed: '.$e->getMessage()); continue; }
        if (!is_array($def)||!preg_match('/^[a-z0-9_]{1,32}$/',(string)($def['command']??''))||!is_callable($def['handler']??null)) continue;
        $commands[strtolower($def['command'])]=['description'=>trim((string)($def['description']??'')),'handler'=>$def['handler']];
    }
    ksort($commands); return $commands;
}
function bot_command_menu(): array { $out=[]; foreach(bot_commands() as $name=>$d) $out[]=['command'=>$name,'description'=>substr($d['description']?:'Run '.$name,0,256)]; return $out; }
function bot_context(int|string $chatId,array $sender,string $arguments,array $message): array { return ['chat_id'=>$chatId,'sender'=>$sender,'arguments'=>$arguments,'message'=>$message,'commands'=>bot_commands()]; }
function bot_dispatch_command(string $name,int|string $chatId,array $sender,string $arguments,array $message): void {
    $commands=bot_commands(); if (!isset($commands[$name])) { bot_send($chatId,'Unknown command. Use /cmd to see available commands.',bot_keyboard_home()); return; }
    try { ($commands[$name]['handler'])(bot_context($chatId,$sender,$arguments,$message)); }
    catch(Throwable $e) { error_log("[bot] Command {$name} failed: ".$e->getMessage()); bot_send($chatId,'Sorry, that command failed safely. Please try again in a moment.'); }
}
function bot_dispatch(array $update): void {
    if (is_array($update['message']??null)) {
        $m=$update['message']; $chat=$m['chat']['id']??null; $sender=is_array($m['from']??null)?$m['from']:[]; $text=trim((string)($m['text']??''));
        if ($chat===null||!str_starts_with($text,'/')) return;
        $parts=preg_split('/\s+/', $text,2)?:[]; $name=strtolower(explode('@',(string)($parts[0]??''),2)[0]); bot_dispatch_command(ltrim($name,'/'),$chat,$sender,trim((string)($parts[1]??'')),$m); return;
    }
    if (!is_array($update['callback_query']??null)) return;
    $c=$update['callback_query']; try { bot_answer_callback((string)($c['id']??'')); } catch(Throwable $e) { error_log('Callback acknowledgement failed: '.$e->getMessage()); }
    $m=is_array($c['message']??null)?$c['message']:[]; $chat=$m['chat']['id']??null; if ($chat===null) return;
    $map=['home'=>'start','register'=>'register','account'=>'account','balance'=>'balance','delete_account'=>'deleteaccount','tools'=>'tools','help'=>'cmd','auth_demo'=>'authdemo','charge_demo'=>'chargedemo'];
    $name=$map[(string)($c['data']??'')]??null; if ($name===null) { bot_send($chat,'That button is no longer available.',bot_keyboard_home()); return; }
    bot_dispatch_command($name,$chat,is_array($c['from']??null)?$c['from']:[],'',$m);
}
function bot_demo_result(string $scenario): array {
    return match($scenario) {'auth'=>['title'=>'AUTH DEMO','status'=>'SIMULATED_OK','message'=>'This is a fixed local demonstration result. No external service was contacted.'],'charge'=>['title'=>'CHARGE DEMO','status'=>'SIMULATED_OK','message'=>'This is a fixed local demonstration result. No transaction was performed.'],default=>['title'=>'DEMO','status'=>'SIMULATED_UNKNOWN','message'=>'The selected demo is not available.']};
}
