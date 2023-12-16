<?php declare(strict_types=1);

namespace BotMan\Middleware\Dialogflow\V2;

interface DialogflowClientInterface
{
    /**
     * @param string|null $sessionId
     * @param string $text
     * @return DialogflowClientResponse
     */
    public function detectIntentText(?string $sessionId, string $text): DialogflowClientResponse;
}
