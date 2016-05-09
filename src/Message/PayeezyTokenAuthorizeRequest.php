<?php

namespace Omnipay\FirstData\Message;

class PayeezyTokenAuthorizeRequest extends PayeezyTokenPurchaseRequest
{
    protected $action = self::TRAN_PREAUTH;

    /**
     * @return array
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $data = parent::getData();

        $data['transaction_type'] = 'authorize';

        return $data;
    }
}
