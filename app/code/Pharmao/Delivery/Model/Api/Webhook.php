<?php

namespace Pharmao\Delivery\Model\Api;

use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Webhook
{
    protected $logger;
    
    protected $_jobFactory;

    public function __construct(
        LoggerInterface $logger,
        Order $order,
        Curl $curl,
        ScopeConfigInterface $scopeConfig,
        \Pharmao\Delivery\Model\JobFactory $jobFactory
    )
    {

        $this->logger = $logger;
        $this->order = $order;
        $this->_curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->_jobFactory = $jobFactory;
    }

    /**
    * @inheritdoc
    */

    public function getPost($data)
    {
        // $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/changed-status.log');
        // $logger = new \Zend\Log\Logger();
        // $logger->addWriter($writer);
        // $returnArray = json_encode($data);
        // $logger->info('res : ' . $returnArray);
        // $logger->info('job_id : ' . $data['id']);
        // $logger->info('status : ' . $data['status']);
        // $logger->info('status : ' .  date("Y-m-d H:i:s"));
        // die;

        $model = $this->_jobFactory->create();
        $collection = $model->getCollection()->addFieldToFilter('job_id', trim($data['id']));
        $job_id = $data['id'];
        $status = $data['status'];
        $jobData = $collection->getData();
        $returnArray = json_encode($data);
        if (!empty($jobData)) {
            $address = $jobData[0]['address'];
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/changed-status.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            
            $jobUpdate = $model->load($jobData[0]['id']);
            $jobUpdate->setStatus($status);
            $jobUpdate->setAdded(date("Y-m-d H:i:s"));
            $saveData = $jobUpdate->save();
            
            $logger->info('res : ' . $returnArray);
            $logger->info('job_id : ' . $data['id']);
            $logger->info('status : ' . $data['status']);
            $logger->info('status : ' .  date("Y-m-d H:i:s"));
        }
        return $returnArray;
    }
}