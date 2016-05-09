<?php

namespace Omnipay\FirstData\Message;

use Omnipay\Common\Message\AbstractResponse;

class PayeezyTokenizeResponse extends AbstractResponse
{
    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return (isset($this->data['status']) && $this->data['status'] === 'success');
    }
}
