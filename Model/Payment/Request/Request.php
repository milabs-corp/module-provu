<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Milabs\Provu\Model\Payment\Request;

use Magento\Framework\Event\ManagerInterface;

class Request extends \Milabs\Provu\Model\Request
{

    protected $_prefixDispatch = 'after_prepare_request_params_provu';

    public function __construct(
        Customer $customer,
        Items $itens,
        ManagerInterface $eventManager,
        array $data = []
    ) {
        parent::__construct($customer, $itens,  $eventManager, $data);
    }

}