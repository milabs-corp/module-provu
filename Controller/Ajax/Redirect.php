<?php
namespace Milabs\Provu\Controller\Ajax;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Phrase;



class Redirect extends Action implements HttpGetActionInterface
{
     /**
     * Checkout Session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var ResultFactory
     */
    protected $result;

    /** @var \Magento\Framework\Message\Manager  */
    protected $messageManager;

    /** @var \Milabs\Provu\Helper\Data */
    protected $provuHelper;
    
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $context;
    /**
     * @var \Magento\Sales\Api\Data\OrderInterfaceFactory
     */
    private $orderFactory;
    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    private $urlHelper;

    /**
     * @param \Magento\Checkout\Model\Session                  $checkoutSession
     * @param \Magento\Framework\App\Action\Context            $context
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param ResultFactory                                    $result
     * @param \Magento\Sales\Api\Data\OrderInterfaceFactory    $orderFactory
     * @param \Magento\Framework\Message\Manager               $messageManager
     * @param \RicardoMartins\PagSeguro\Helper\Data            $pagSeguroHelper
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\ResultFactory $result,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Magento\Framework\Message\Manager $messageManager,
        \Milabs\Provu\Helper\Data $provuHelper,
        \Magento\Framework\UrlInterface $urlHelper
     ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->result = $result;
        $this->orderFactory = $orderFactory;
        $this->messageManager = $messageManager;
        $this->provuHelper = $provuHelper;
        $this->context = $context;
        $this->urlHelper = $urlHelper;
    }

    public function execute()
    {
        $result = $this->result->create(ResultFactory::TYPE_REDIRECT);
      
        $lastorderId = $this->checkoutSession->getLastRealOrderId();
        $order = $this->orderFactory->create()->loadByIncrementId($lastorderId);
        if (!$order->getPayment()) {
            $this->messageManager->addErrorMessage(
                new Phrase('Something went wrong when placing the order with Provu. Please try again.')
            );
            $result = $this->result->create(ResultFactory::TYPE_REDIRECT);
            return $result->setUrl($this->_redirect->getRefererUrl());
        }

        $url = $order->getPayment()->getAdditionalInformation('authenticationURL');

        $result->setUrl($url);

        return $result;
    }
}
