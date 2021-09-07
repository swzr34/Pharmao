<?php

namespace Pharmao\Delivery\Model\Api;

use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Webhook
{
    protected $logger;

    public function __construct(
        LoggerInterface $logger,
        Order $order,
        Curl $curl,
        ScopeConfigInterface $scopeConfig
    )
    {

        $this->logger = $logger;
        $this->order = $order;
        $this->_curl = $curl;
        $this->scopeConfig = $scopeConfig;
    }

    /**
    * @inheritdoc
    */

    public function getPost($data)
    {
        $returnArray = json_encode($data);
         $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/changed-status.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('res : ' . $returnArray);
        return $returnArray;
    }
}