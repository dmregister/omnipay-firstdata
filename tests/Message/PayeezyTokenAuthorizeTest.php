<?php

namespace Omnipay\FirstData\Message;

use Omnipay\Tests\TestCase;
use Omnipay\FirstData\Message\PayeezyPurchaseRequest;

class PayeezyTokenAuthorizeRequestTest extends TestCase
{
    public function testPurchaseSuccess()
    {
        $request = new PayeezyTokenAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize(
            array(
                'amount' => '12.00',
                'token' => [
                    'token_type' => 'FDToken',
                    'token_data' => [
                        'type'            => 'visa',
                        'value'           => '2537446225198291',
                        'cardholder_name' => 'JohnSmith',
                        'exp_date'        => '1030'
                    ]
                ],
            )
        );

        $data = $request->getData();
        $this->assertEquals('authorize', $data['transaction_type']);
        $this->assertEquals('2537446225198291', $data['token']['token_data']['value']);
        $this->assertEquals('visa', $data['token']['token_data']['type']);
        $this->assertEquals('1200', $data['amount']);
        $this->assertEquals('FDToken', $data['token']['token_type']);
    }

    public function testPurchaseSuccessMaestroType()
    {
        $options = array(
            'amount' => '12.00',
            'token' => [
                'token_type' => 'FDToken',
                'token_data' => [
                    'type'            => 'maestro',
                    'value'           => '2537446225198291',
                    'cardholder_name' => 'JohnSmith',
                    'exp_date'        => '1030'
                ]
            ]
        );

        $request = new PayeezyTokenAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($options);

        $data = $request->getData();
        $this->assertEquals('authorize', $data['transaction_type']);
        $this->assertEquals('maestro', $data['token']['token_data']['type']);
    }
}
