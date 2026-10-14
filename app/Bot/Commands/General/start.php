<?php
declare(strict_types=1);
return ['command'=>'start','description'=>'Open the main menu','handler'=>static function (\CcCheckerBot\Bot\BotContext $bot, array $message): void {
$name = htmlspecialchars((string)($message['from']['first_name'] ?? 'there'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$bot->sendMsg("👋 <b>Welcome to FlintBot, {$name}!</b>\n\nA modular Telegram sandbox built with PHP and FlintPHP. Explore commands, account tools, and simulated demo credits.\n\n<i>No real card data or payment processing is supported.</i>", \CcCheckerBot\UI\Keyboards::home());
}];