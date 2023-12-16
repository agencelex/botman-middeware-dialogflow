<?php declare(strict_types=1);

namespace BotMan\Middleware\Dialogflow\V2\Message;

use Google\Cloud\Dialogflow\V2\Intent\Message;

class PayloadMessage extends AbstractMessage
{
    public readonly array $payload;

    public function reveal(Message $dialogflowMessage) : ?AbstractMessage
    {
        if($dialogflowMessage->hasPayload()) {

            $payload = [];

            foreach ($dialogflowMessage->getPayload()->getFields() as $name => $value) {
                $payload[$name] = json_decode($value->serializeToJsonString(), true);
            }
            $this->payload = $payload;
            $this->setJson($dialogflowMessage->serializeToJsonString());

            return $this;
        }

        return parent::reveal($dialogflowMessage);
    }

}
