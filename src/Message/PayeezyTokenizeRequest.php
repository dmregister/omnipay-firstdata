<?php

namespace Omnipay\FirstData\Message;

class PayeezyTokenizeRequest extends PayeezyAbstractRequest
{
    protected $action = self::TRAN_PREAUTH;

    /**
     * @return array
     */
    public function getData()
    {
        $data = parent::getData();

        $this->validate('card');

        $data['amount'] = 0.00;
        $data['currency_code'] = $this->getCurrency();
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
}
