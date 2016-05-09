<?php

namespace Omnipay\FirstData\Message;


use Omnipay\Common\Exception\InvalidRequestException;

class PayeezyTokenPurchaseRequest extends PayeezyAbstractRequest
{
    protected $action = self::TRAN_PURCHASE;

    /** API version to use. See the note about the hashing requirements for v12 or higher. */
    const API_VERSION = 'v1';

    /** @var string live endpoint URL base */
    protected $liveEndpoint = 'https://api.payeezy.com/%s/transactions';

    /** @var string test endpoint URL base */
    protected $testEndpoint = 'https://api-cert.payeezy.com/%s/transactions';

    /**
     * @return string
     */
    protected function getEndpoint()
    {
        return sprintf(($this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint), self::API_VERSION);
    }

    /**
     * @return array
     */
    protected function getHeaders()
    {
        $headers = [];

        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';
        $headers['apiKey'] = $this->getApiKey();
        $headers['token'] = $this->getMerchantToken();
        $headers['nonce'] = hexdec(bin2hex(openssl_random_pseudo_bytes(4, $cstrong)));
        $headers['timestamp'] = (time() * 1000);

        return $headers;
    }

    /**
     * @param $data
     * @param $nonce
     * @param $timestamp
     *
     * @return string
     */
    protected function buildAuthString($data, $nonce, $timestamp)
    {
        $dataString = sprintf(
            '%s%s%s%s%s',
            $this->getApiKey(),
            $nonce,
            $timestamp,
            $this->getMerchantToken(),
            json_encode($data)
        );

        return base64_encode(hash_hmac("sha256", $dataString, $this->getApiSecret(), false));
    }

    /**
     * @return array
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $data = $this->getBaseData();

        $this->validate('amount', 'token');

        $data['method'] = 'token';
        $data['transaction_type'] = 'purchase';
        $data['amount'] = $this->getAmountInteger();
        $data['currency_code'] = $this->getCurrency();
        $data['token'] = $this->getToken();

        return $data;
    }

    /**
     * Get the base transaction data.
     *
     * @return array
     */
    protected function getBaseData()
    {
        return array();
    }

    /**
     * @param mixed $data
     *
     * @return PayeezyResponse
     */
    public function sendData($data)
    {
        $endpoint = $this->getEndpoint();

        $headers = $this->getHeaders();
        $headers['Authorization'] = $this->buildAuthString($data, $headers['nonce'], $headers['timestamp']);

        $client = $this->httpClient->post(
            $endpoint,
            $headers
        );

        $client->setBody(json_encode($data), $headers['Content-Type']);

        $client->getCurlOptions()->set(CURLOPT_PORT, 443);
        $httpResponse = $client->send();

        return $this->createResponse($httpResponse->getBody());
    }

    /**
     * Create the response object.
     *
     * @param $data
     *
     * @return PayeezyResponse
     */
    protected function createResponse($data)
    {
        $responseData = json_decode((string)$data, true);

        // Convert the amount back to dollars.
        if (isset($responseData['amount'])) {
            $responseData['amount'] = $this->getAmount();
        }

        return $this->response = new PayeezyTokenResponse($this, $responseData);
    }
}
