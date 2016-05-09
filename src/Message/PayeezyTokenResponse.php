<?php

namespace Omnipay\FirstData\Message;

use Omnipay\Common\Message\RequestInterface;

class PayeezyTokenResponse extends PayeezyResponse
{
    /**
     * PayeezyTokenResponse constructor.
     *
     * @param RequestInterface $request
     * @param array            $data
     */
    public function __construct(RequestInterface $request, array $data)
    {
        $this->request = $request;
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return (isset($this->data['transaction_status']) && $this->data['transaction_status'] === 'approved');
    }

    /**
     * Get the transaction id
     *
     * This is the authorization number returned by the cardholder’s financial
     * institution when a transaction has been approved. This value overrides any
     * value sent for the Request Property of the same name.
     *
     * @return integer
     */
    public function getTransactionId()
    {
        return $this->getDataItem('transaction_id');
    }

    /**
     * Get the transaction reference
     *
     * Because refunding or voiding a transaction requires both the authorization number
     * and the transaction tag, we concatenate them together to make the transaction
     * reference.
     *
     * @return string
     */
    public function getTransactionReference()
    {
        return $this->getTransactionId() . '::' . $this->getTransactionTag();
    }

    /**
     * @return mixed|null
     */
    public function getMessage()
    {
        return $this->getDataItem('gateway_message');
    }

    /**
     * Get the error code.
     *
     * This property indicates the processing status of the transaction. Please refer
     * to the section on Exception Handling for further information. The Transaction_Error
     * property will return True if this property is not “00”.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getDataItem('gateway_resp_code');
    }
}
