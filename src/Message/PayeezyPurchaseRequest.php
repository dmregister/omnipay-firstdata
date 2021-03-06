<?php
/**
 * First Data Payeezy Purchase Request
 */
namespace Omnipay\FirstData\Message;

/**
 * First Data Payeezy Purchase Request
 *
 * ### Example
 *
 * <code>
 * // Create a gateway for the First Data Payeezy Gateway
 * // (routes to GatewayFactory::create)
 * $gateway = Omnipay::create('FirstData_Payeezy');
 *
 * // Initialise the gateway
 * $gateway->initialize(array(
 *     'gatewayId' => '12341234',
 *     'password'  => 'thisISmyPASSWORD',
 *     'testMode'  => true, // Or false when you are ready for live transactions
 * ));
 *
 * // Create a credit card object
 * $card = new CreditCard(array(
 *     'firstName'            => 'Example',
 *     'lastName'             => 'Customer',
 *     'number'               => '4222222222222222',
 *     'expiryMonth'          => '01',
 *     'expiryYear'           => '2020',
 *     'cvv'                  => '123',
 *     'email'                => 'customer@example.com',
 *     'billingAddress1'      => '1 Scrubby Creek Road',
 *     'billingCountry'       => 'AU',
 *     'billingCity'          => 'Scrubby Creek',
 *     'billingPostcode'      => '4999',
 *     'billingState'         => 'QLD',
 * ));
 *
 * // Do a purchase transaction on the gateway
 * $transaction = $gateway->purchase(array(
 *     'description'              => 'Your order for widgets',
 *     'amount'                   => '10.00',
 *     'transactionId'            => 12345,
 *     'clientIp'                 => $_SERVER['REMOTE_ADDR'],
 *     'card'                     => $card,
 * ));
 * $response = $transaction->send();
 * if ($response->isSuccessful()) {
 *     echo "Purchase transaction was successful!\n";
 *     $sale_id = $response->getTransactionReference();
 *     echo "Transaction reference = " . $sale_id . "\n";
 * }
 * </code>
 */
class PayeezyPurchaseRequest extends PayeezyAbstractRequest
{
    protected $action = self::TRAN_PURCHASE;

    public function getData()
    {
        $params = $this->getParameters();

        if (isset($params['token'])) {
            return $this->getTokenData();
        } elseif (isset($params['accountNumber']) && !empty($params['routingNumber'])) {
            return $this->getAchData();
        } else {
            return $this->getCreditCardData();
        }
    }

    /**
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    protected function getCreditCardData()
    {
        $data = parent::getData();

        $this->validate('amount', 'card');

        $data['amount'] = $this->getAmount();
        $data['currency_code'] = $this->getCurrency();
        $data['customer_ref'] = $this->getCustomerReference();
        $data['reference_no'] = $this->getTransactionId();

        // add credit card details
        $data['credit_card_type'] = self::getCardType($this->getCard()->getBrand());
        $data['cc_number'] = $this->getCard()->getNumber();
        $data['cardholder_name'] = $this->getCard()->getName();
        $data['cc_expiry'] = $this->getCard()->getExpiryDate('my');
        $data['cc_verification_str2'] = $this->getCard()->getCvv();
        $data['cc_verification_str1'] = $this->getAVSHash();
        $data['cvd_presence_ind'] = 1;
        $data['cvd_code'] = $this->getCard()->getCvv();

        $data['client_ip'] = $this->getClientIp();
        $data['client_email'] = $this->getCard()->getEmail();
        $data['language'] = strtoupper($this->getCard()->getCountry());

        return $data;
    }

    /**
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    protected function getTokenData()
    {
        $data = parent::getData();

        $this->validate('amount', 'token');

        $tokenData = $this->getParameter('token');

        $data['amount'] = $this->getAmount();
        $data['currency_code'] = $this->getCurrency();
        $data['customer_ref'] = $this->getCustomerReference();
        $data['reference_no'] = $this->getTransactionId();

        $data['transarmor_token'] = $tokenData['value'];

        // add credit card details
        $data['credit_card_type'] = $tokenData['credit_card_type'];
        $data['cardholder_name'] = $tokenData['cardholder_name'];
        $data['cc_expiry'] = $tokenData['cc_expiry'];

        $data['client_ip'] = $this->getClientIp();

        return $data;
    }

    /**
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    protected function getAchData()
    {

        $data = parent::getData();

        $this->validate('amount', 'card', 'accountNumber', 'routingNumber');

        $data['amount'] = $this->getAmount();
        $data['currency_code'] = $this->getCurrency();
        $data['customer_ref'] = $this->getCustomerReference();
        $data['reference_no'] = $this->getTransactionId();

        $data['account_number'] = $this->getAccountNumber();
        $data['bank_id'] = $this->getRoutingNumber();
        $data['cardholder_name'] = $this->getCard()->getName();

        return $data;
    }
}
