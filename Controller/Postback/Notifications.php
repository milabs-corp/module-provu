<?php

namespace Milabs\Provu\Controller\Postback;

use Magento\Framework\Phrase;
use Magento\Framework\Controller\ResultFactory;

class Notifications extends \Magento\Framework\App\Action\Action
{
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

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $session,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Psr\Log\LoggerInterface $logger,
		\Milabs\Provu\Helper\Data $helper,
		\Magento\Framework\Registry $registry,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\Magento\Framework\Message\Manager $messageManager,
		ResultFactory $result

	) {
		$this->registry = $registry;
		$this->_session = $session;
		$this->checkoutSession = $checkoutSession;
		$this->logger = $logger;
		$this->helper = $helper;
		$this->_orderCollectionFactory = $orderCollectionFactory;
		$this->messageManager = $messageManager;
		$this->result = $result;
		parent::__construct($context);
	}

	public function execute()
	{

		$post = $this->getRequest()->getPostValue();

		

		//$request = $this->getRequest();
		$result = $this->result->create(ResultFactory::TYPE_REDIRECT);


		/** @var Page $page */
		$page = $this->result->create(ResultFactory::TYPE_PAGE);

		/** @var Template $block */
		$block = $page->getLayout()->getBlock('provu.notifications.page');


		if (!$this->getOrder() && !$this->checkoutSession->loadCustomerQuote()) {
			return '200';
			//$block->setData('state', 'success');
			//return $page;

		} elseif (!$this->getOrder() && $this->checkoutSession->loadCustomerQuote()) {
			return '200';
			$block->setData('state', 'error');
			return $page;

		} else {
			return '200';
			return $result->setUrl('*');
		}

	}

	private function getOrder()
	{
		$request = $this->getRequest();

		if (!$request->getParam('transactionId'))
			return false;

		$order = $this->_orderCollectionFactory->create()
			->addAttributeToSelect("*")
			->addFieldToFilter('provu_transaction_id', (string)$request->getParam('transactionId'))->getFirstItem();

		$this->helper->getOrderStatus( $request->getParam('transactionId'), $order);
	}
}
