<?php declare(strict_types=1);

namespace BotMan\Middleware\Dialogflow\V2\Message\Trait;

use BotMan\Middleware\Dialogflow\V2\Message\AbstractMessage;
use Google\Cloud\Dialogflow\V2\Intent\Message;

trait CanRevealMessageFromRepeatedField
{
    protected ?AbstractMessage $next = null;

    public function setNext(AbstractMessage $message): AbstractMessage
    {
        $this->next = $message;
        // Returning a handler from here will let us link handlers in a
        // convenient way like this:
        // $message1->setNext($message2)->setNext($message3);
        return $message;
    }

    public function reveal(Message $dialogflowMessage) : ?AbstractMessage
    {
        return $this->next?->reveal($dialogflowMessage);
    }
}
