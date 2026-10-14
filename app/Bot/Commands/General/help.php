<?php
declare(strict_types=1);
return ['command'=>'help','description'=>'List available commands','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot): void {
$bot->sendMsg("Use /cmd to view the available commands.\n\nNew commands are discovered automatically from PHP files under app/Bot/Commands.", \CcCheckerBot\UI\Keyboards::dashboard());
}];