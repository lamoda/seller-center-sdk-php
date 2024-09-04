<?php

namespace RocketLabs\SellerCenterSdk\Core\Response;

use Psr\Http\Message\ResponseInterface as HttpResponseInterface;
use RocketLabs\SellerCenterSdk\Core\Exception\ApiException;

/**
 * Class Factory
 */
class Factory
{
    const HTTP_CODE_200 = 200;

    /**
     * @param HttpResponseInterface $httpResponse
     * @param string $class class name
     * @return AbstractResponse
     */
    public function buildResponse(HttpResponseInterface $httpResponse, $class = GenericResponse::class)
    {
        if ($httpResponse->getStatusCode() !== self::HTTP_CODE_200) {
            throw new ApiException(ApiException::UNEXPECTED_RESPONSE.sprintf(
                    ": [code %s] %s",
                    $httpResponse->getStatusCode(),
                    $httpResponse->getBody()
                ),
                $httpResponse->getStatusCode()
            );
        }

        $decodedResponse = $this->decodeJsonResponse($httpResponse);

        $envelope = key($decodedResponse);

        if ($envelope == ResponseInterface::RESPONSE_TYPE_ERROR) {
            return new ErrorResponse($decodedResponse[$envelope]);
        }

        return new $class($decodedResponse[$envelope]);
    }

    /**
     * @param HttpResponseInterface $response
     *
     * @return array
     */
    protected function decodeJsonResponse($response)
    {
        $body = (string)$response->getBody();
        $jsonDecoded = json_decode($body, true);

        // Only array could be valid api response
        if (is_array($jsonDecoded)) {
            return $jsonDecoded;
        }

        throw new ApiException(sprintf("%s: %s", ApiException::INVALID_RESPONSE_BODY, $body), $response->getStatusCode());
    }
}
