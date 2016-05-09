<?php

namespace Omnipay\FirstData\Message;

use Omnipay\Tests\TestCase;
use Omnipay\FirstData\Message\PayeezyTokenResponse;

class PayeezyTokenResponseTest extends TestCase
{
    public function testPurchaseSuccess()
    {
        $response = new PayeezyTokenResponse($this->getMockRequest(), array(
            'correlation_id' => '228.1462807906589',
            'transaction_status' => 'approved',
            'validation_status' => 'success',
            'transaction_type' => 'authorize',
            'transaction_id' => 'ET177882',
            'transaction_tag' => '82579730',
            'method' => 'token',
            'amount' => '1000',
            'currency' => 'USD',
            'cvv2' => 'I',
            'token' => array(
                'token_type' => 'FDToken',
                'token_data' => array(
                    'type' => 'visa',
                    'cardholder_name' => 'JohnSmith',
                    'exp_date' => '1030',
                    'value' => '2537446225198291'
                )
            ),
            'bank_resp_code' => '100',
            'bank_message' => 'Approved',
            'gateway_resp_code' => '00',
            'gateway_message' => 'Transaction Normal'
        ));

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('ET177882::82579730', $response->getTransactionReference());
        $this->assertSame('Transaction Normal', $response->getMessage());
        $this->assertEquals('00', $response->getCode());
    }

    public function testBankError()
    {
        $response = new PayeezyTokenResponse($this->getMockRequest(), array(
            'correlation_id' => '228.1462811099639',
            'transaction_status' => 'declined',
            'validation_status' => 'success',
            'transaction_type' => 'authorize',
            'transaction_tag' => '82589207',
            'method' => 'token',
            'amount' => '530200',
            'currency' => 'USD',
            'cvv2' => 'I',
            'token' => array(
                'token_type' => 'FDToken',
                'token_data' => array(
                    'type' => 'visa',
                    'cardholder_name' => 'JohnSmith',
                    'exp_date' => '1030',
                    'value' => '2537446225198291'
                )
            ),
            'bank_resp_code' => '302',
            'bank_message' => 'Credit Floor',
            'gateway_resp_code' => '00',
            'gateway_message' => 'Transaction Normal'
        ));

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('::82589207', $response->getTransactionReference());
        $this->assertEquals('00', $response->getCode());
        $this->assertEquals('302', $response->getDataItem('bank_resp_code'));
        $this->assertEquals('Credit Floor', $response->getDataItem('bank_message'));
    }
}
