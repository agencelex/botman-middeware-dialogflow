<?php declare(strict_types=1);

namespace BotMan\Middleware\Dialogflow\V2;

use BotMan\Middleware\Dialogflow\V2\Message\PayloadMessage;
use BotMan\Middleware\Dialogflow\V2\Message\TextMessage;
use Google\Cloud\Dialogflow\V2\DetectIntentResponse;
use Google\Cloud\Dialogflow\V2\QueryResult;
use Google\Protobuf\Internal\RepeatedField;

class DialogflowClientResponse
{
    public readonly array $replies;

    public readonly string $queryText;

    public readonly ?string $action;

    public readonly bool $isComplete;

    public readonly ?string $intentName;

    public readonly float $confidence;

    public readonly array $parameters;

    public readonly array $contexts;

    public function __construct(?DetectIntentResponse $detectIntentResponse, protected array $additionalSupportedReplyClasses = [])
    {
        $queryResult = $detectIntentResponse?->getQueryResult();

        if($queryResult) {

            $this->queryText = $queryResult->getQueryText() ?? ''; // The query that triggers the intent
            $this->action = $queryResult->getAction(); // Key that can be used to trigger service logic
            $this->isComplete = $queryResult->getAllRequiredParamsPresent() ?? false;
            $this->replies = $this->buildReplies($queryResult->getFulfillmentMessages());

            $this->intentName = $queryResult->getIntent()?->getName();

            $this->confidence = $queryResult->getIntentDetectionConfidence();

            $this->parameters = $this->getParameters($queryResult);
            $this->contexts = $this->getContexts($queryResult);

        } else {

            $this->replies = [];
            $this->queryText = '';
            $this->action = null;
            $this->isComplete = false;
            $this->intentName = null;

            $this->confidence = 0.0;

            $this->parameters = [];
            $this->contexts = [];
        }
    }

    protected function buildReplies(RepeatedField $fulfillmentMessages): array
    {
        $head = new TextMessage;

        $replyClasses = array_merge([
            PayloadMessage::class
        ], $this->additionalSupportedReplyClasses);

        $pointer = $head;
        foreach ($replyClasses as $class) {
            $pointer = $pointer->setNext(new $class);
        }
        unset($pointer);

        $replies = [];

        foreach ($fulfillmentMessages as $fulfillmentMessage) {

            $replies[] = $head->reveal($fulfillmentMessage);
        }

        return array_filter($replies);
    }

    /**
     * @param QueryResult $queryResult
     * @return array
     */
    protected function getParameters(QueryResult $queryResult): array
    {
        $parameters = [];

        foreach ($queryResult->getParameters()?->getFields() as $name => $value) {
            $parameters[$name] = json_decode($value->serializeToJsonString(), true);
        }

        return $parameters;
    }

    /**
     * @param QueryResult $queryResult
     * @return array
     */
    protected function getContexts(QueryResult $queryResult): array
    {
        $contexts = [];

        foreach ($queryResult->getOutputContexts() as $context) {
            $contextParams = [];
            foreach ($context->getParameters()?->getFields() as $name => $field) {
                $contextParams[$name] = $field->getStringValue();
            }

            $contexts[] = [
                'name' => substr(strrchr($context->getName(), '/'), 1),
                'parameters' => $contextParams,
                'lifespan' => $context->getLifespanCount(),
            ];
        }

        return $contexts;
    }
}
