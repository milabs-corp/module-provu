<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace  Milabs\Provu\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Event\ManagerInterface;

abstract class Request extends \Magento\Framework\DataObject
{

    /**
     * @var string
     */
    protected $_prefixDispatch = 'after_prepare_request_params_provu_default';

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $customer;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $itens;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    public function __construct(
        \Magento\Framework\DataObject $customer,
        \Magento\Framework\DataObject $itens,
        ManagerInterface $eventManager,
        array $data = []
    ) {
        $this->customer = $customer;
        $this->itens = $itens;
        $this->_eventManager = $eventManager;
        parent::__construct($data);
    }

    /**
     * @return string
     *
     * @throws LocalizedException
     */
    public function buildRequest()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->getPaymentData()->getOrder();

        if (
            !($this->getPaymentData() instanceof \Magento\Sales\Model\Order\Payment)
            || !(($order = $this->getPaymentData()->getOrder())
                instanceof \Magento\Sales\Model\Order)
        ) {
            throw new LocalizedException(__('Instance Invalid'));
        }

        $this->setOrder($order);
        $request = $this->setData($this->merge());
        $this->_eventManager->dispatch(
            $this->_prefixDispatch,
            ['data_object' => $request]
        );

        $file = fopen('/var/www/html/var/log/RequestProvu.txt', 'w+');
        fwrite($file, json_encode((array)$request->toJson(), JSON_PRETTY_PRINT));
        fclose($file);

        

        return $request->toJson();
    }

    /**
     * Merge all params
     *
     * @return array
     */
    public function merge()
    {
        $merchantOrderId = $this->getInfo();
        $customer =  $this->getCustomer();
        $itens = $this->getItens();
        return array_merge($merchantOrderId, $customer, $itens);
    }

    /**
     * @return array
     */
    public function getCustomer()
    {
        return $this->customer
            ->setOrder($this->getOrder())
            ->getRequest();
    }

    /**
     * @return array
     */
    public function getItens()
    {
        return $this->itens
            ->setOrder($this->getOrder())
            ->getRequest();
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('Milabs\Provu\Helper\Data');

        return  [
            "ClientId" => $helper->getClientId(),
            'MerchantOrderId' => $this->getOrder()->getIncrementId(),
            "OrderNumber" => $this->getOrder()->getIncrementId(),
            "TransactionId" => $this->getOrder()->getIncrementId(),
            "Currency"  => $this->getOrder()->getStoreCurrencyCode(),
            "Installments"  => 1,
            "TotalAmount"  => (int)$this->numberValue($this->getOrder()->getBaseGrandTotal() * 100),
            "TotalDiscountAmount"  => (int)$this->numberValue($this->getOrder()->getBaseDiscountAmount() * 100),
            "TotalShippingAmount" => (int) $this->numberValue($this->getOrder()->getBaseShippingAmount() * 100),
            "CallbackUrl" => $helper->getCallBackUrl(),
            "ReturnUrl" =>  $helper->getReturnUrl()
        ];
    }

    /**
     * numberValue
     *
     * @param  mixed $value
     * @return void
     */
    private function numberValue($value)
    {
        return preg_replace("/[^0-9]/", "", $value);
    }
}
