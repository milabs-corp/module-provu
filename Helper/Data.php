<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Milabs\Provu\Helper;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const CHANGE_TYPE = 1;

    const CARD_TOKEN = 'new';

    const ID_DENY = 'deny_payment';

    const ID_ACCEPT = 'accept_payment';

    const COUNTRY_CODE_BRL = 'BR';

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_asset;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollection;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $_session;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory
     */
    protected $_transaction;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var null
     */
    protected $_item = null;

    /**
     * Magento Invoice Service
     *
     * @var \Magento\Sales\Model\Service\InvoiceService
     */

    /** @var \Magento\Sales\Model\Service\InvoiceService  */
    protected $invoiceService;

    /** @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender  */
    protected $invoiceSender;


    /** @var \Magento\Sales\Model\Order */
    protected $orderData;

    /**
     * Magento transaction Factory
     *
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transactionFactory;

    /**
     * @var OrderCommentSender
     */
    protected $orderCommentSender;

    protected $orderHistoryFactory;

    protected $orderRepository;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory $transaction,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\View\Asset\Repository $asset,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\DB\Transaction $transactionFactory,
        \Magento\Sales\Model\Order $orderData,
        \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender,
        \Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager

    ) {
        $this->_asset = $asset;
        $this->_orderCollection = $orderCollection;
        $this->_order = $order;
        $this->_session = $session;
        $this->_transaction = $transaction;
        $this->_objectManager = $objectManager;
        $this->curl = $curl;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->transactionFactory = $transactionFactory;
        $this->orderData = $orderData;
        $this->orderCommentSender = $orderCommentSender;
        $this->orderHistoryFactory = $orderHistoryFactory;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function getClientId()
    {
        $config = $this->scopeConfig->getValue(
            'payment/provu/client_id'
        );
        return (string)$config;
    }


    /**
     * @return string
     */
    public function getCallBackUrl()
    {
        return  $this->_storeManager->getStore()->getBaseUrl() . 'rest/V1/postback/notifications';
    }


    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return   $this->_storeManager->getStore()->getBaseUrl() . 'provu/store/index';
    }


    /**
     * @return mixed
     */
    public function getMerchantToken()
    {
        $config = $this->scopeConfig->getValue(
            'payment/provu/api_token'
        );

        return $config;
    }

    /**
     * @return mixed
     */
    public function getMerchantKey()
    {
        $config = $this->scopeConfig->getValue(
            'payment/provu/api_key'
        );

        return $config;
    }


    /**
     * @return mixed
     */
    public function isEnabled()
    {
        $config = $this->scopeConfig->getValue(
            'payment/provu/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $config;
    }



    /**
     * @return mixed
     */
    public function getInstructions()
    {
        $config = $this->scopeConfig->getValue(
            'payment/provu/instructions',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $config;
    }


    /**
     * @return mixed
     */
    public function geTitle()
    {
        $config = $this->scopeConfig->getValue(
            'payment/provu/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $config;
    }


    /**
     * @return mixed
     */
    public function getConfigValue()
    {
        $config = $this->scopeConfig->getValue(
            'payment/provu/installments',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $config;
    }


    /**
     * @return string
     */
    public function getUriRequest()
    {
        $config = $this->scopeConfig->getValue(
            'payment/provu/uri'
        );
        return (string)$config;
    }

    /**
     * Remove placeholders of uri
     *
     * @param $uri
     *
     * @return mixed
     */
    public function sanitizeUri($uri)
    {
        $uri = str_replace('//', '/', $uri);
        $uri = str_replace(':/', '://', $uri);
        $uri = str_replace(
            [
                '-capture',
                '-refund',
                '-order',
                '-offline',
                '-authorize'
            ],
            '',
            $uri
        );

        return $uri;
    }


    /**
     * Sanitize string
     *
     * @param $value
     * @param $maxlength
     * @param null $init
     *
     * @return bool|string
     */
    public function prepareString($value, $maxlength, $init = null)
    {
        if (!is_null($init)) {
            return substr(trim($value), (int)$init, $maxlength);
        }

        return substr(trim($value), $maxlength);
    }


    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }


    /**
     * @return \Magento\Framework\Message\ManagerInterface
     */
    public function getMessage()
    {
        return $this->_objectManager->get(\Magento\Framework\Message\ManagerInterface::class);
    }

    /**
     * @param $order
     *
     * @return bool
     */
    public function canAuthorizeOffline($order)
    {
        if (
            $order instanceof \Magento\Sales\Model\Order
            && in_array($order->getPayment()->getMethod(), $this->getCodesPayment())
            && ($order->isPaymentReview())
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $order
     *
     * @return bool
     */
    public function canCancelOffline($order)
    {
        if (
            $order instanceof \Magento\Sales\Model\Order
            && in_array($order->getPayment()->getMethod(), $this->getCodesPayment())
            && !in_array(
                $order->getState(),
                [
                    \Magento\Sales\Model\Order::STATE_CLOSED,
                    \Magento\Sales\Model\Order::STATE_COMPLETE,
                    \Magento\Sales\Model\Order::STATE_CANCELED
                ]
            )
            && ($this->hasInvoiceOpen($order) || !$order->hasInvoices())
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return bool
     */
    public function hasInvoiceOpen(\Magento\Sales\Model\Order $order)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        foreach ($order->getInvoiceCollection() as $invoice) {
            if ($invoice->getState() == $invoice::STATE_OPEN) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $order
     *
     * @return bool
     */
    public function canUpdate($order)
    {
        return $this->canCancelOffline($order);
    }


    /**
     * @param $date
     *
     * @return bool|\DateTime
     */
    public function createDate($date)
    {
        try {
            $object = new \DateTime($date);
            return $object;
        } catch (\Exception $e) {
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        $config = $this->scopeConfig->getValue(
            'payment/provu/active'
        );
        return boolval($config);
    }


    /**
     * Convert real to cents
     *
     * @param $amount
     *
     * @return string
     */
    public function formatNumber($amount)
    {
        return number_format($amount, 2, '', '');
    }

    public function convertToPrice($amount)
    {
        return number_format($amount / 100, 2, '.', '');
    }

    /**
     * Formt
     *
     * @param $price
     *
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->_order->getOrderCurrency()->formatPrecision($price, 2, [], false);
    }


    public function getOrderStatus($transactionId, $order)
    {

        $url =  $this->getUriRequest() . "/simple/$transactionId/status";
        $token =  $this->getMerchantToken();
        $key =  $this->getMerchantKey();

        try {
            $this->curl->setHeaders(["Content-Type" => "application/json", "API-Appkey" => $key, "API-APPToken" => $token]);
            $this->curl->get($url);
            $response = $this->curl->getBody();
            $response = json_decode($response, true);
            if ($response['status'] == 'approved') {
                $this->createInvoice($order);
                return 'approved';
            } elseif ($response['status'] == 'denied') {
                $this->cancelOrder($order);
                return 'canceled';
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->warning('Provu Update Order' . $e->getMessage());
            return false;
        }
    }

    private function createInvoice($order)
    {
        if (!$order->getId()) {
            throw new \Exception((string)__('Order %1 Not Found', $order->getId()));
        }

        $invoice = $this->invoiceService->prepareInvoice($order);
        $msg = sprintf('Pedido Faturado. Transação ID %s', (string)$order->getData('provu_transaction_id'));
        $invoice->addComment($msg);
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
        $invoice->register();


        try {
            $this->invoiceSender->send($invoice);
        } catch (\Exception $e) {
            $this->logger->debug(__('We can\'t send the invoice email right now.'));
        }

        $this->transactionFactory->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();
        $order->addStatusHistoryComment(
            sprintf('Invoice #%s successfully created.', $invoice->getIncrementId())
        );

        $order->save();
    }

    public function cancellOrderApi($order)
    {

        $id = $order->getIncrementId();
        $url =  $this->getUriRequest() . "/simple/$id/cancellations";
        $token =  $this->getMerchantToken();
        $key =  $this->getMerchantKey();

        try {

            $this->curl->setHeaders(["Content-Type" => "application/json", "API-Appkey" => $key, "API-APPToken" => $token]);
            $this->curl->post($url , '');
           
            $response = $this->curl->getBody();
            $response = json_decode($response, true);

            // sends the cancellation e-mail to the customer
            if ($order->getState() == 'canceled' && $this->curl->getStatus() == 200) {

                $this->orderCommentSender->send($order, true);

                $this->orderHistoryFactory->create();
                $history = $this->orderHistoryFactory->create()
                    ->setStatus($order->getStatus())
                    ->setEntityName(\Magento\Sales\Model\Order::ENTITY)
                    ->setComment('Pedido cancelado Lendíco');

                $history->setIsCustomerNotified(false)
                    ->setIsVisibleOnFront(false);

                $order->addStatusHistory($history);
                $this->orderRepository->save($order);
            } 

            if($this->curl->getStatus() != 200)
              return false;


            return true;
        } catch (\Exception $e) {
            $this->logger->warning('Provu Cancel Order' . $e->getMessage());
             return false;
        }
    }


    private function cancelOrder($order)
    {

        try {

            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);

            $cancelOrder = $this->orderData->load($order->getId());
            $cancelOrder->cancel()->save();

            $order->addStatusHistoryComment(
                sprintf('Pedido #%s cancelado ', (string)$order->getData('provu_transaction_id'))
            );

            $order->save();
        } catch (\Exception $e) {
            $this->logger->warning('Provu Cancel Order' . $e->getMessage());
        }
    }
}
