<?php
declare(strict_types=1);
return ['command'=>'status','description'=>'Show worker diagnostics','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$memory = memory_get_usage(true);
$memoryText = $memory >= 1048576 ? number_format($memory/1048576,1).' MiB' : number_format($memory/1024,1).' KiB';
$bot->sendMsg("<b>📊 Worker status</b>\n\nProcess: <code>".getmypid()."</code>\nPHP: <code>".PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION."</code>\nMemory allocated: <code>{$memoryText}</code>\nMode: <code>long-polling</code>\nSandbox: <code>enabled</code>\n\nThis reports the current worker process; it is not a full infrastructure health check.");
}];