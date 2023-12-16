<?php declare(strict_types=1);

namespace BotMan\Middleware\Dialogflow\V2\Message;

use Google\Cloud\Dialogflow\V2\Intent\Message;

class TextMessage extends AbstractMessage
{
    public readonly string $text;

    public function reveal(Message $dialogflowMessage) : ?AbstractMessage
    {
        if($dialogflowMessage->hasText()) {

            $this->text = $dialogflowMessage->getText()->getText()[0];
            $this->setJson($dialogflowMessage->serializeToJsonString());
            return $this;
        }

        return parent::reveal($dialogflowMessage);
    }
}
