<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Milabs\Provu\Model\Payment\Request;

class Items extends \Magento\Framework\DataObject
{

    /**
     * @var \Milabs\Provu\Helper\Data
     */
    protected $helper;


    /**
     *
     * @var mixed $itens
     */
    protected $itens;

    public function __construct(
        \Milabs\Provu\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getRequest()
    {
    
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/provu_log.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('itens = ');

        return $this->setData(
            [
                'Items' =>  $this->getITems()
            ]
        )->toArray();
    }

    protected function  getITems()
    {
        $order = $this->getOrder();

        foreach ($order->getAllVisibleItems() as $item) {

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/provu_log.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('item = '.json_encode($item->debug()));
            
            $this->itens[] = [
                "Id" =>  $item->getProductId(),
                "Name" => $item->getName(),
                "Price" =>  (int) $this->helper->formatNumber($item->getPrice()),
                "Quantity" => $item->getQtyOrdered(),
                "Discount" => 0,
                "Total" =>  $this->helper->formatNumber($item->getPrice()) * $item->getQtyOrdered()
            ];
           
        }

        return $this->itens;

    }
}
