<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Milabs\Provu\Cron;

use DateInterval;
use Magento\Framework\Exception\LocalizedException;

class OrdersRemove
{

    const CODE = \Milabs\Provu\Model\Payment\Provu::CODE;

    protected $logger;
    protected $orderPayment;
    protected $orderFactory;
    protected $dateTime;
    protected $helper;
    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order\Payment\Collection $paymentCollection,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Milabs\Provu\Helper\Data $helper
        )
    {
        $this->orderPayment = $paymentCollection;
        $this->orderFactory = $orderFactory;
        $this->logger = $logger;
        $this->dateTime = $dateTime;
        $this->helper = $helper;
    }

    public function run()
    {
        $retorna = [];
        foreach( $this->getParentIds() as $orders )
        {   
            $order = $this->getOrder($orders);
            $dataAt = explode(" ",$order->getData('created_at'))[0];
            if ($order->getData('state') == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT){
                
                if($this->compareDate($dataAt))
                {
                    $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
                    $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
                    $order->save();
                }

                $retorna[$orders] = ['state'=>$order->getData('state'), 'date'=>$this->getDateAt($dataAt),'dateAt'=>$this->getDateAt(),'expired'=>$this->compareDate($dataAt)];
            }

        }
            
            $file = fopen('/var/www/html/teste.txt', 'w+');
            fwrite($file, json_encode((array)$retorna, JSON_PRETTY_PRINT));
            fclose($file); 
    }

    protected function getParentIds()
    {
        try{
            $return = [];
            foreach( $this->getPaymentColection()->getItems() as $key => $value ){
                $paymentOrder = $this->getPaymentColection()->getItemById($key);
                if( $paymentOrder->getData('method') === self::CODE ){
                    array_push($return, $paymentOrder->getData('parent_id') ) ;
                }
            }
            return $return;
        }
        catch(LocalizedException $e)
        {
            $this->logger->warning(__(
                "[M!labs Updater] - ",
                $e->getMessage()
            ));
        }
    }

    protected function compareDate($date)
    {
        $dateCreate = strtotime(date('Y-m-d',strtotime('+'.$this->helper->getDaysCron().' day', strtotime($date))));
        $dateNow = strtotime($this->getDateAt());
        if($dateNow > $dateCreate)
        {
            return true;
        }
        return false;
    }

    protected function getDateAt($date = null)
    {
        return $this->dateTime->gmtDate('Y-m-d',$date);
    }

    public function getOrder($condition)
    {
        return $this->orderFactory->create()->getItemById($condition);
    }
    
    protected function getPaymentColection()
    {
        return $this->orderPayment->loadData();
    }


}

