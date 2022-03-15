<?php

namespace Milabs\Provu\Model;


use Magento\Framework\Phrase;
use Magento\Framework\Controller\ResultFactory;

class PostManagement {


	/**
	 * @var string
	 */
	protected $_paymentId;

	/**
	 * @var string
	 */
	protected $_changeType;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;


	protected $helper;


	/**
	 * Checkout Session
	 *
	 * @var \Magento\Checkout\Model\Session
	 */
	protected $checkoutSession;


	/** @var \Magento\Framework\Message\Manager  */
	protected $messageManager;

	/**
	 * @var \Magento\Framework\Registry
	 */
	protected $registry;

	/**
	 * @var ResultFactory
	 */
	protected $result;

	/**
	 * @var \Magento\Framework\App\Request\Http
	 */
	protected $_request;



	public function __construct(
		\Magento\Customer\Model\Session $session,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Psr\Log\LoggerInterface $logger,
		\Milabs\Provu\Helper\Data $helper,
		\Magento\Framework\Registry $registry,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\Magento\Framework\Message\Manager $messageManager,
		\Magento\Framework\App\Request\Http $request,
		ResultFactory $result

	) {
		$this->registry = $registry;
		$this->_session = $session;
		$this->checkoutSession = $checkoutSession;
		$this->logger = $logger;
		$this->helper = $helper;
		$this->_orderCollectionFactory = $orderCollectionFactory;
		$this->messageManager = $messageManager;
		$this->_request = $request;
		$this->result = $result;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPost()
	{


		$data = $this->_request->getContent();
		/* $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/post_lend2.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info($data); */

		$trasactionId = json_decode($data, true);

		  if(!isset($trasactionId['transactionId']))
		  	 return false;

		//$request = $this->getRequest();
		$result = $this->result->create(ResultFactory::TYPE_REDIRECT);


		/** @var Page $page */
		$page = $this->result->create(ResultFactory::TYPE_PAGE);

		/** @var Template $block */
		$block = $page->getLayout()->getBlock('provu.notifications.page');

		$orderStatus = $this->getOrder($trasactionId['transactionId']);

		if ($orderStatus == 'approved' && !$this->checkoutSession->loadCustomerQuote()) {
			//$block->setData('state', 'success');
			return $page;

		} elseif ($orderStatus == 'canceled' && $this->checkoutSession->loadCustomerQuote()) {
			//$block->setData('state', 'error');
			return $page;

		} else {
			return $result->setUrl('*');
		}


	}


	private function getOrder($transactionId)
	{

		$order = $this->_orderCollectionFactory->create()
			->addAttributeToSelect("*")
			->addFieldToFilter('provu_transaction_id', $transactionId)->getFirstItem();

		if(!$order)
			 return false;

		return $this->helper->getOrderStatus($transactionId, $order);
	}
}