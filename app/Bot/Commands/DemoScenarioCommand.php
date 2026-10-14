<?php

declare(strict_types=1);

namespace CcCheckerBot\Bot\Commands;

use CcCheckerBot\Demo\MockScenario;
use CcCheckerBot\Storage\UserStore;
use CcCheckerBot\Telegram\TelegramClientInterface;
use CcCheckerBot\UI\Keyboards;

final class DemoScenarioCommand implements CommandHandler
{
    public function __construct(private readonly string $scenario, private readonly MockScenario $scenarios = new MockScenario())
    {
    }

    public function handle(TelegramClientInterface $telegram, UserStore $users, int|string $chatId, array $sender, string $arguments): void
    {
        $id = $sender['id'] ?? null;
        if ($id === null || $users->find((string) $id) === null) {
            $telegram->sendMessage($chatId, 'Please register first to unlock the demo tools.', Keyboards::home());
            return;
        }

        $result = $this->scenarios->result($this->scenario);
        $escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $telegram->sendMessage(
            $chatId,
            '🧪 <b>' . $escape($result['title']) . "</b>\n\nStatus: <code>" . $escape($result['status']) . "</code>\n" . $escape($result['message']),
            Keyboards::tools(),
        );
    }
}
