<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace  Milabs\Provu\Model;


abstract class AbstractMethod extends \Magento\Payment\Model\Method\AbstractMethod
{

    const CODE_CURRENCY_REAL_BRL = 'BRL';

    const CODE_COUNTRY_BR = 'BR';

    /**
     * @var DataObject
     */
    protected $request;

    /**
     * @var DataObject
     */
    protected $response;

    /**
     * @var \Milabs\Provu\Helper\Data
     */
    protected $helper;

    /**
     * @var string
     */
    protected $keyRequest;

    /**
     * @var array
     */
    protected $_path = [];
 
    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var string
     */
    protected $_uri;

    /**
     * @var Validate
     */
    protected $validate;

    /**
     * @var bool
     */
    protected $_postback = false;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_update;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\DataObject $request,
        \Magento\Framework\DataObject $response,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Milabs\Provu\Helper\Data $helper,
        \Magento\Framework\DataObject $update,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        // Validate $validate,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->helper = $helper;
        $this->response = $response;
        $this->request = $request;
        $this->curl = $curl;
        $this->_uri = $this->helper->getUriRequest();
        $this->_update = $update;
        // $this->validate = $validate;
    }

    /**
     * @param \Magento\Framework\DataObject $data
     *
     * @return $this
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        $info = $this->getInfoInstance();
        $info->setAdditionalInformation($data->getAdditionalData());

        return $this;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     *
     * @return $this
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
       
        $this->setAmount($payment, $amount);
        $payment->setSkipOrderProcessing(true);
        $payment->setAdditionalInformation('can_capture', false);
        $this->setRunValidate(true);
        $response = $this->post()->request();

        $payment->setAdditionalInformation('transactionId', $response['transactionId']);
        $payment->setAdditionalInformation('tid', $response['tid']);
        $payment->setAdditionalInformation('authenticationURL', $response['authenticationURL']);
        $payment->setAdditionalInformation('acquirer', $response['acquirer']);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->getInfoInstance()->getOrder();
        $order->setData('provu_transaction_id',$response['transactionId'] );

        return $this;
    }

    /**
     * Set amount total to authorize and capture
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param $amount
     *
     * @throws \Milabs\Provu\Exception\Cc
     */
    public function setAmount(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ($amount <= 0) {
            throw new \Milabs\Provu\Exception\Cc(__('Invalid amount for capture.'));
        }

        $payment->setAmount($amount);
    }

    protected function _validate()
    {
        $this->validate
            ->setPayment($this)
            ->validate();
    }

    /**
     * @return DataObject
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return DataObject
     */
    public function getResponse()
    {
        return $this->response;
    }

    
    /**
     * Get instance client
     *
     * @return \Magento\Framework\HTTP\ZendClient
     */
    public function getClient()
    {

        try {
            /* $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/provu_log.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('getClient *******'); */

            $token =  $this->helper->getMerchantToken();
            $key =  $this->helper->getMerchantKey();
        
            $url =  $this->helper->getUriRequest()."/simple/payments";
            $params = $this->getParams();

            $this->curl->setHeaders(["Content-Type" => "application/json", "API-Appkey" => $key, "API-APPToken" => $token]);
            $this->curl->post($url, $params);


            $file2 = fopen('/var/www/html/var/log/RequestHeaderProvu.txt', 'w+');
            fwrite($file2, json_encode((array)["URL"=>$url,"Content-Type" => "application/json", "API-Appkey" => $key, "API-APPToken" => $token,"params"=>$params], JSON_PRETTY_PRINT));
            fclose($file2);

            $response = $this->curl->getBody();

            return  $response;

        } catch (\Exception $e) {
            $this->logger->warning('Provu error' .$e->getMessage());
            return false;
        }

    }

    /**
     * Get uri to request
     *
     * @return mixed|string
     */
    public function getUri()
    {
        $uri = $this->_uri . '/' . $this->getPath();
        $uri = $this->helper->sanitizeUri($uri);
        return $uri;
    }

    /**
     * Send request of type PUT
     *
     * @return $this
     */
    public function put()
    {
        $params = $this->getParams();
        $this->getClient()
            ->setRawData($params)
            ->setMethod(\Magento\Framework\HTTP\ZendClient::PUT);

        return $this;
    }


    /**
     * Send request of type POST
     *
     * @return $this
     */
    public function post()
    {
       /*  $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/provu_log.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('post *******'); */

        // $params = $this->getParams();

        // print_r($params);

        // $this->getClient()
        //     ->setMethod(\Magento\Framework\HTTP\ZendClient::POST)
        //     ->setParameterPost($params);
        //->setRawData($params);

        /* $logger->info('post  retunr *******'); */
        return $this;
    }
    


    /**
     * Send request of type POST
     *
     * @return $this
     */
    public function postOld()
    {

        /* $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/provu_log.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('post *******'); */

        //     $params = $this->getParams();

        //    $return =  $this->getClient()
        //         ->setMethod(\Magento\Framework\HTTP\ZendClient::POST)
        //         ->setRawData($params);

        //  $logger->info('post  return *******'. json_encode($return));
        return $this;
    }

    /**
     * Send request of type GET
     *
     * @return $this
     */
    public function get()
    {
        $params = $this->getParams();
        $this->getClient()
            ->setMethod(\Magento\Framework\HTTP\ZendClient::GET)
            ->setRawData($params);

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        $path = '';
        foreach ($this->_path as $k => $v) {
            if (!empty($v)) {
                $path .= $k . '/' . $v;
            } else {
                $path .= $k;
            }
        }

        return $path;
    }

    /**
     * @param $k
     * @param $v
     *
     * @return $this
     */
    public function setPath($k, $v)
    {
        $this->_path[$k] = $v;

        return $this;
    }

    /**
     * Get params of request
     *
     * @param DataObject $payment
     *
     * @return mixed
     */
    public function getParams()
    {
        $request = $this->getRequest()
            ->setPaymentData($this->getInfoInstance())
            ->buildRequest();

        /* $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/provu_log.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('params = ' . json_encode($request)); */

        return $request;
    }


    /**
     * Execute the request
     *
     * @throws \Exception
     */
    public function request()
    {

      /*   $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/provu_log.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer); */

        try {
           
             $response = $this->getClient();
             return json_decode($response, true);
        } catch (\Zend_Http_Client_Exception $e) {
            /* $logger->info('request ERROR Zend ******* ' . $e); */
            throw $e;
        } catch (\Exception $e) {
            $message = 'Occurred an error during payment process. Try Again.';
          //  $this->isCatchException($response, $message);
            $this->_logger->error($e->getMessage());
            throw new \Exception(__($message));
        }

        return $this;
    }


    /**
     * Check if exception is message to the user
     *
     * @param \Zend_Http_Response $response
     * @param $message
     *
     * @throws \Milabs\Provu\Exception\Cc
     */
    public function isCatchException($response, $message)
    {
        if (!($response instanceof \Zend_Http_Response)) {
            return;
        }

        if (
            $response->getStatus() == \Zend\Http\Response::STATUS_CODE_400
            && isset(\Zend\Json\Decoder::decode($response->getBody())[0])
            && property_exists(\Zend\Json\Decoder::decode($response->getBody())[0], 'Message')
            && $message = (string)\Zend\Json\Decoder::decode($response->getBody())[0]->Message
        ) {
            throw new \Milabs\Provu\Exception\Cc(__($message));
        }
    }

    /**
     * Process response
     *
     * @param $response
     */
    protected function _processResponse()
    {
        $this->getResponse()
            ->setPayment($this->getInfoInstance())
            ->process();

        return $this;
    }

    /**
     * @return bool
     */
    public function getPostbackInstance()
    {
        return $this->_postback;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     *
     * @return bool
     */
    public function denyPayment(\Magento\Payment\Model\InfoInterface $payment)
    {
        return true;
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isActive($storeId = null)
    {
        if (!$this->helper->isActive()) {
            return false;
        }

        return parent::isActive($storeId); // TODO: Change the autogenerated stub
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     *
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {

        if (!$this->helper->isActive()) {
            return false;
        }

        return parent::isAvailable($quote); // TODO: Change the autogenerated stub
    }
}
