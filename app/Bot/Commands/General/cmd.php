<?php
declare(strict_types=1);
return ['command'=>'cmd','description'=>'List available commands','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$lines = [];
foreach ($bot->commands() as $name => $description) {
    $lines[] = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ' — ' . htmlspecialchars($description !== '' ? $description : 'No description', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
$bot->sendMsg("<b>🧭 Command centre</b>\n\n" . implode("\n", $lines), \CcCheckerBot\UI\Keyboards::dashboard());
}];
