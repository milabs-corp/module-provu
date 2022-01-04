<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Milabs\Provu\Observer\Backend\Order;


class CancelAfter implements \Magento\Framework\Event\ObserverInterface
{


    public function __construct(
		\Milabs\Provu\Helper\Data $helper,
		\Magento\Framework\Message\Manager $messageManager

	) {
	
		$this->helper = $helper;
		$this->messageManager = $messageManager;
	}


    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getData('order');
        $payment = $order->getPayment();

        if ($payment->getMethod() == \Milabs\Provu\Model\Payment\Provu::CODE) {
            if(!$this->helper->cancellOrderApi($order))
               $this->_messageManager->addError(__('Error ao cancelar pedido junto a Provu'));
        }

    }
}

