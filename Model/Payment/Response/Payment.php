<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Milabs\Provu\Model\Payment\Response;


class Payment extends \Milabs\Provu\Model\Response
{

    const STATUS_AUTHORIZED = '1';

    const STATUS_CANCELED = '10';

    const STATUS_CANCELED_ABORTED = '13';

    const STATUS_CANCELED_DENIED = '3';

    const STATUS_CANCELED_AFTER = '11';

    const STATUS_CANCELED_PARTIAL = '2';

    const STATUS_CAPTURED = '2';

    const STATUS_PENDING = '12';

    const STATUS_PAYMENT_REVIEW = '0';

    
    protected $_authorize;

   
    protected $_capture;

    
    protected $_pending;

   
    protected $_unauthorized;

    /**
     * @var \Az2009\Cielo\Model\Method\Cc\Transaction\Cancel
     */
    protected $_cancel;

    public function __construct(
        array $data = []
    ) {

        parent::__construct($data);
    }

    public function process()
    {
        parent::process();

        switch ($this->getStatus()) {
            case Payment::STATUS_AUTHORIZED:
                // $this->_authorize
                //      ->setPayment($this->getPayment())
                //      ->setResponse($this->getResponse())
                //      ->process();
                $this->_pending
                     ->setPayment($this->getPayment())
                     ->setResponse($this->getResponse())
                     ->process();
            break;
            case Payment::STATUS_CAPTURED:
                $this->_capture
                     ->setPayment($this->getPayment())
                     ->setResponse($this->getResponse())
                     ->process();
            break;
            case Payment::STATUS_CANCELED_DENIED:
            case Payment::STATUS_CANCELED_ABORTED:
            case Payment::STATUS_CANCELED_AFTER:
            case Payment::STATUS_CANCELED:
                $this->_cancel
                     ->setPayment($this->getPayment())
                     ->setResponse($this->getResponse())
                     ->process();
                break;
            case Payment::STATUS_PAYMENT_REVIEW:
            case Payment::STATUS_PENDING:
                $this->_pending
                     ->setPayment($this->getPayment())
                     ->setResponse($this->getResponse())
                     ->process();
            break;
            default:
                $this->_unauthorized
                     ->setPayment($this->getPayment())
                     ->setResponse($this->getResponse())
                     ->process();
            break;
        }
    }

    /**
     * get status payment
     * @return mixed
     * @throws \Exception
     */
    public function getStatus()
    {
        $body = $this->getBody();
        if (property_exists($body, 'Payment')) {
            $status = $body->Payment->Status;
            return $this->isStatusCanceled($status);
        } elseif (property_exists($body, 'Status')) {
            $status = $body->Status;
            return $this->isStatusCanceled($status);
        }

        throw new \Exception(__('Invalid payment status'));
    }

    /**
     * check status canceled when partial
     * @param $status
     * @return string
     */
    public function isStatusCanceled($status)
    {
        $payment = $this->getPayment();
        if ($payment->getActionCancel()
            && $status == Payment::STATUS_CANCELED_PARTIAL) {
            $status = self::STATUS_CANCELED;
        }

        return $status;
    }

}