<?php

namespace Omnipay\FirstData\Message;


class PayeezyTokenizeRequest extends PayeezyAbstractRequest
{
    /** API version to use. See the note about the hashing requirements for v12 or higher. */
    const API_VERSION = 'v1';

    /** @var string live endpoint URL base */
    protected $liveEndpoint = 'https://api-cert.payeezy.com/'.self::API_VERSION.'/transactions/tokens';

    /** @var string test endpoint URL base */
    protected $testEndpoint = 'https://api-cert.payeezy.com/'.self::API_VERSION.'/transactions/tokens';

    /**
     * Set Type
     *
     * Calls to the Payeezy Gateway API are secured with a gateway ID and
     * password.
     *
     * @return PayeezyAbstractRequest provides a fluent interface.
     */
    public function setType($value)
    {
        return $this->setParameter('type', $value);
    }

    /**
     * Get Type
     *
     * Calls to the Payeezy Gateway API are secured with a gateway ID and
     * password.
     *
     * @return string
     */
    public function getType()
    {
        return $this->getParameter('type');
    }

    /**
     * Set Auth
     *
     * Calls to the Payeezy Gateway API are secured with a gateway ID and
     * password.
     *
     * @return PayeezyAbstractRequest provides a fluent interface.
     */
    public function setAuth($value)
    {
        return $this->setParameter('auth', $value);
    }

    /**
     * Get Auth
     *
     * Calls to the Payeezy Gateway API are secured with a gateway ID and
     * password.
     *
     * @return string
     */
    public function getAuth()
    {
        return $this->getParameter('auth');
    }

    /**
     * Set Type
     *
     * Calls to the Payeezy Gateway API are secured with a gateway ID and
     * password.
     *
     * @return PayeezyAbstractRequest provides a fluent interface.
     */
    public function setTaToken($value)
    {
        return $this->setParameter('taToken', $value);
    }

    /**
     * Get Type
     *
     * Calls to the Payeezy Gateway API are secured with a gateway ID and
     * password.
     *
     * @return string
     */
    public function getTaToken()
    {
        return $this->getParameter('taToken');
    }

    /**
     * Set Merchant Token
     *
     * Calls to the Payeezy Gateway API are secured with a gateway ID and
     * password.
     *
     * @return PayeezyAbstractRequest provides a fluent interface.
     */
    public function setMerchantToken($value)
    {
        return $this->setParameter('merchantToken', $value);
    }


    /**
     * @return string
     */
    protected function getEndpoint()
    {
        return ($this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = [];

        $data['type'] = $this->getType();
        $data['auth'] = $this->getAuth();
        $data['ta_token'] = $this->getTaToken();

        // add credit card details
        $data['credit_card'] = [
            'type' => self::getCardType($this->getCard()->getBrand()),
            'cardholder_name' => $this->getCard()->getName(),
            'card_number' => $this->getCard()->getNumber(),
            'exp_date' => $this->getCard()->getExpiryDate('my'),
            'cvv' => $this->getCard()->getCvv()
        ];

        return $data;
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
        return $this->response = new PayeezyTokenizeResponse($this, json_decode((string)$data, true));
    }
}
