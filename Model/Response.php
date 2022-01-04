<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace  Milabs\Provu\Model;

use Magento\Framework\Exception\LocalizedException;

class Response extends \Magento\Framework\DataObject
{

    /**
     * @var array
     */
    protected $_requestStatusAllowed = [201, 200];

    public function process()
    {
        $this->hasError();
    }

    /**
     * Check if request occured an error
     *
     * @return $this
     *
     * @throws LocalizedException
     * @throws \Exception
     */
    public function hasError()
    {
        if ($message = $this->getRequestError()) {
            throw new \Exception(__($message));
        }

        // if (!in_array($this->getResponse()->getStatus(),
        //     $this->_requestStatusAllowed)) {
        //     throw new \Exception(__($this->getMessage()));
        // }

        return $this;
    }

    /**
     * Get headers of request
     *
     * @return \Zend\Http\Headers
     */
    public function getHeaders()
    {
        return $this->getResponse()->getHeaders();
    }

    /**
     * Get message of request
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->getResponse()->getMessage();
    }

    /**
     * Get body of request
     *
     * @return mixed|string
     */
    public function getBody($type = \Zend\Json\Json::TYPE_OBJECT)
    {  
        $body = $this->getResponse()->getBody();
        $body = \Zend\Json\Json::decode($body, $type);

        return $body;
    }

    /**
     * @return \Zend_Http_Response
     *
     * @throws Exception
     */
    public function getResponse()
    {
        $response = $this->getData('response');

       // $response->getStatus() == \Zend\Http\Response::STATUS_CODE_400;

       $file = fopen('/var/www/html/var/log/ResponseFullProvu.txt', 'w+');
       fwrite($file, json_encode((array)$response, JSON_PRETTY_PRINT));
       fclose($file);

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/provu_log.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('get response ----'. $response );

        // if (!($response instanceof \Zend_Http_Response)) {
        //     throw new \Exception(__('invalid response'));
        // }

        return $response;
    }

}