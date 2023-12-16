<?php declare(strict_types=1);

namespace BotMan\Middleware\Dialogflow\V2;

use Google\ApiCore\ApiException;
use Google\Cloud\Dialogflow\V2\DetectIntentResponse;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;

class DialogflowClient implements DialogflowClientInterface
{

    private SessionsClient $sessionsClient;

    /**
     * Client constructor.
     *
     * @param string $languageCode
     * @param array $additionalSupportedReplyClasses
     */
    public function __construct(protected string $languageCode, protected array $additionalSupportedReplyClasses = [])
    {
        $this->sessionsClient = new SessionsClient();
    }

    /**
     * @inheritDoc
     */
    public function detectIntentText(?string $sessionId, string $text): DialogflowClientResponse
    {
        // Create text input
        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode($this->languageCode);

        // Create query input
        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        try {

            $response = $this->getIntentResponse($sessionId, $queryInput);
            return new DialogflowClientResponse($response, $this->additionalSupportedReplyClasses);

        } catch (ApiException) {
            // TODO log the error
            return new DialogflowClientResponse(null);
        }
    }

    /**
     * @param string $sessionId
     * @param QueryInput $queryInput
     * @return DetectIntentResponse
     * @throws ApiException
     */
    protected function getIntentResponse(string $sessionId, QueryInput $queryInput): DetectIntentResponse
    {
        // Create a session
        $session =  $this->openSession($sessionId);

        $response = $this->sessionsClient->detectIntent($session, $queryInput);

        $this->closeSession();

        return $response;
    }

    protected function openSession(?string $sessionId): string
    {
        return $this->sessionsClient::sessionName(
            project: getenv('GOOGLE_CLOUD_PROJECT'),
            session: $sessionId ?: uniqid('', true)
        );
    }

    protected function closeSession(): void
    {
        $this->sessionsClient->close();
    }
}
