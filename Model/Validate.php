<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace  Milabs\Provu\Model;

use Magento\Framework\DataObject;

abstract class Validate extends DataObject
{

    /**
     * @var array
     */
    protected $_fieldsValidate = [];

    /**
     * Get instance payment
     *
     * @return \Magento\Sales\Model\Order\Payment
     *
     * @throws \Milabs\Provu\Exception\Ticket
     */
    public function getPayment()
    {
        $payment = $this->getData('payment');
        if (!($payment instanceof \Milabs\Provu\Model\AbstractMethod)) {
            throw new \Milabs\Provu\Exception\Ticket(
                __('Occurred an error during payment process. Try Again.')
            );
        }

        return $payment;
    }

    /**
     * Get request before send
     *
     * @return mixed
     */
    public function getRequest()
    {
        $params = $this->getPayment()->getParams();
        $params = json_decode($params, true);
        return $params;
    }

    /**
     * Validate fields required
     *
     * @param $key
     * @param $value
     *
     * @throws \Milabs\Provu\Exception\Ticket
     */
    public function required($key, $value, $prefix = '')
    {
        if (isset($this->_fieldsValidate[$key])
            && $this->_fieldsValidate[$key]['required'] === true
            && empty($value)
        ) {
            throw new \Milabs\Provu\Exception\Ticket(__('%1 Field %2 required', $prefix, $key));
        }
    }

    /**
     * Validate length of the fields
     *
     * @param $key
     * @param $value
     *
     * @throws \Milabs\Provu\Exception\Ticket
     */
    public function maxLength($key, $value, $prefix = '')
    {
        if (isset($this->_fieldsValidate[$key]['maxlength'])
            && strlen($value) > $this->_fieldsValidate[$key]['maxlength']
        ) {
            throw new \Milabs\Provu\Exception\Ticket(
                __(
                    '%1 Field %2 exceed limit of %3 characters',
                    $prefix,
                    $key,
                    $this->_fieldsValidate[$key]['maxlength']
                )
            );
        }
    }

    /**
     * Todo validation
     */
    public abstract function validate();

}