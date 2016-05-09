<?php

namespace Omnipay\FirstData\Message;

use Omnipay\Common\Message\AbstractResponse;

class PayeezyTokenPurchaseResponse extends AbstractResponse
{
    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return (isset($this->data['transaction_status']) && $this->data['transaction_status'] === 'approved');
    }
}
