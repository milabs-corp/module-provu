<?php

namespace Milabs\Provu\Controller\Adminhtml\Update;

use Magento\Framework\Phrase;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;


class Index   extends \Magento\Backend\App\Action
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
		\Magento\Backend\App\Action\Context $context,
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
		$request = $this->getRequest();

		if (!$request->getParam('transactionId'))
			return false;

		$order = $this->_orderCollectionFactory->create()
			->addAttributeToSelect("*")
			->addFieldToFilter('provu_transaction_id', (string)$request->getParam('transactionId'))->getFirstItem();

		 if($this->helper->getOrderStatus( $request->getParam('transactionId'), $order)){
			$this->messageManager->addSuccessMessage('Pedido atualizado com sucesso. Veja o último comentário do pedido para maiores detalhes.');
		 }else {
		 	$this->messageManager->addSuccessMessage('Não encontramos atualização recente para este pedido.');
		 }
		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;

	}

	
}

