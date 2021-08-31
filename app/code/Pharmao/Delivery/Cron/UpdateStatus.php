<?php

namespace Pharmao\Delivery\Cron;

class UpdateStatus
{

	public function execute()
	{
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron1.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info('cron ran');

		return $this;
	}
}
?>