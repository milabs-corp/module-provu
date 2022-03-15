<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Milabs\Provu\Model\Payment\Request;

class Customer extends \Magento\Framework\DataObject
{
    const CPF = 'CFP';

    const CNPJ = 'CNPJ';

    /**
     * @var \Magento\Sales\Model\Order\Address
     */
    protected $billingAddress;

    /**
     * @var \Magento\Sales\Model\Order\Shipping
     */
    protected $shippingAddress;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Milabs\Provu\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Milabs\Provu\Helper\Data $helper,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->helper = $helper;
        $this->regionFactory = $regionFactory;
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getRequest()
    {

        /* $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/provu_log.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('customers = ' ); */


        $this->order = $this->getOrder();
        $this->billingAddress = $this->order->getBillingAddress();
        $this->shippingAddress = $this->order->getShippingAddress();

        $payment = $this->order
                        ->getPayment()
                        ->getMethodInstance();

        $this->setPayment($payment);

        return $this->setData(
                        [
                            'Customer' => [
                                'Name' => $this->billingAddress->getFirstname() .' ' .
									$this->billingAddress->getLastName(),
                                'DocumentNumber' => $this->numberValue($this->helper->prepareString($this->getIdentity(), 14, 0)),
                                'DocumentType' => $this->isCpfCnpj($this->getIdentity()),
                                'Phone' => $this->billingAddress->getTelephone(),
                                'CellPhone'=> (!empty($this->billingAddress->getTelephone())) ? $this->billingAddress->getTelephone() : $this->billingAddress->getFax(),
                                'Email' => $this->helper->prepareString($this->order->getCustomerEmail(), -255),
                                'ShippingAddress' => $this->getShippingAddress(),
                                'BillingAddress' => $this->getBillingAddress()
                            ]
                        ]
                    )->toArray();

    }

    /**
     * @return array
     */
    public function getBillingAddress()
    {
        $regionCode = $this->regionFactory->create()->load($this->billingAddress->getRegionId());

        return  [
                    'Street' => $this->helper->prepareString($this->billingAddress->getStreetLine(1), 255, 0),
                    'Number' => $this->helper->prepareString($this->billingAddress->getStreetLine(2), 15, 0),
                    'Complement' => $this->helper->prepareString($this->getComplement(), 50, 0),
                    'ZipCode' => $this->helper->prepareString($this->billingAddress->getPostcode(), 9, 0),
                    'City' => $this->helper->prepareString($this->billingAddress->getCity(), 50, 0),
                    'Country' => $this->helper->prepareString($this->billingAddress->getCountryId(), 35, 0),
                    'State' =>  $regionCode->getCode(),
                    'District' => $regionCode->getCode(),
                ];
    }

    /**
     * @return array
     */
    public function getShippingAddress()
    {
        $regionCode = $this->regionFactory->create()->load($this->billingAddress->getRegionId());

        return [
                   'Street' => $this->helper->prepareString($this->shippingAddress->getStreetLine(1), 255, 0),
                   'Number' => $this->helper->prepareString($this->shippingAddress->getStreetLine(2), 15, 0),
                   'Complement' => $this->helper->prepareString($this->getComplement(), 50, 0),
                   'ZipCode' => $this->helper->prepareString($this->shippingAddress->getPostcode(), 9, 0),
                   'Country' => $this->helper->prepareString($this->shippingAddress->getCountryId(), 35, 0),
                   'City' => $this->helper->prepareString($this->shippingAddress->getCity(), 50, 0),
                   'State' =>  $regionCode->getCode(),
                   'District' => $regionCode->getCode(),
               ];
    }

    /**
     * prepare complement
     * @param \Magento\Sales\Model\Order\Address $address
     * @return string
     */
    public function getComplement()
    {
        $complement = array(
            $this->billingAddress->getStreetLine(3),
            $this->billingAddress->getStreetLine(4)
        );

        return implode(',', $complement);
    }

    /**
     * check if doc is cpf or cnpj
     * @param $doc
     * @return string
     */
    protected function isCpfCnpj($doc)
    {
        if (!($identity = $this->getIdentity())) {
            return '';
        }

        $doc = preg_replace('/[^0-9]/', '', $doc);
        if (strlen($doc) > 11) {
            return Customer::CNPJ;
        }

        return Customer::CPF;
    }

    /**
     * get identity of customer
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->getOrder()->getBillingAddress()->getVatId();
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