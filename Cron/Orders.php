<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Milabs\Provu\Cron;

class Orders
{

    protected $logger;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
       // $this->logger->addInfo("Cronjob Orders is executed.");
    }
}

