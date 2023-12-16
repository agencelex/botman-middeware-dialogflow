<?php declare(strict_types=1);

namespace BotMan\Middleware\Dialogflow\V2\Message;

use Google\Cloud\Dialogflow\V2\Intent\Message;

abstract class AbstractMessage
{
    use Trait\CanRevealMessageFromRepeatedField;

    public readonly string $json;

    protected function setJson(string $json): void
    {
        $this->json = $json;
    }
}
