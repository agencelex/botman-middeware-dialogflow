<?php declare(strict_types=1);

namespace BotMan\Middleware\Dialogflow\V2;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Interfaces\MiddlewareInterface;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;

/**
 * API : https://github.com/googleapis/google-cloud-php-dialogflow
 * Fulfillment Webhook : https://github.com/eristemena/dialogflow-fulfillment-webhook-php
 */
class Dialogflow implements MiddlewareInterface
{
    protected bool $isIgnoreIntentPattern = false;

    /**
     * constructor.
     *
     * @param DialogflowClientInterface $client
     */
    public function __construct(protected DialogflowClientInterface $client) {}

    /**
     * Create a new Dialogflow middleware instance.
     *
     * @param string $languageCode
     * @return static
     */
    public static function create(string $languageCode = 'en'): static
    {
        $client = new DialogflowClient($languageCode);
        return new static($client);
    }

    /**
     * Allow the middleware to listen all dialogflow actions.
     *
     * @return static
     */
    public function ignoreIntentPattern(): static
    {
        $this->isIgnoreIntentPattern = true;
        return $this;
    }

    /**
     * Handle a captured message.
     *
     * @param IncomingMessage $message
     * @param BotMan $bot
     * @param $next
     *
     * @return mixed
     */
    public function captured(IncomingMessage $message, $next, BotMan $bot)
    {
        return $next($message);
    }

    /**
     * Handle an incoming message.
     *
     * @param IncomingMessage $message
     * @param BotMan $bot
     * @param $next
     *
     * @return mixed
     */
    public function received(IncomingMessage $message, $next, BotMan $bot)
    {
        $conversationId = $message->getConversationIdentifier();
        $sessionId = $conversationId? md5($conversationId) : uniqid('', true);

        $response = $this->client->detectIntentText($sessionId, $message->getText());

        $message->addExtras('queryText', $response->queryText);
        $message->addExtras('replies', $response->replies);
        $message->addExtras('action', $response->action);
        $message->addExtras('isComplete', $response->isComplete);
        $message->addExtras('intentName', $response->intentName);
        $message->addExtras('confidence', $response->confidence);
        $message->addExtras('parameters', $response->parameters);
        $message->addExtras('contexts', $response->contexts);

        return $next($message);
    }

    /**
     * @param IncomingMessage $message
     * @param $pattern
     * @param bool $regexMatched Indicator if the regular expression was matched too
     *
     * @return bool
     */
    public function matching(IncomingMessage $message, $pattern, $regexMatched): bool
    {
        if (empty($message->getExtras()['action'])) {
            return false;
        }

        if ($this->isIgnoreIntentPattern) {
            return true;
        }

        $pattern = '/^' . $pattern . '$/i';
        return (bool)preg_match($pattern, $message->getExtras()['action']);
    }

    /**
     * Handle a message that was successfully heard, but not processed yet.
     *
     * @param IncomingMessage $message
     * @param BotMan $bot
     * @param $next
     *
     * @return mixed
     */
    public function heard(IncomingMessage $message, $next, BotMan $bot)
    {
        return $next($message);
    }

    /**
     * Handle an outgoing message payload before/after it
     * hits the message service.
     *
     * @param mixed $payload
     * @param BotMan $bot
     * @param $next
     *
     * @return mixed
     */
    public function sending($payload, $next, BotMan $bot)
    {
        return $next($payload);
    }
}
