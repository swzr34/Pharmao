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
        \Pharmao\Delivery\Model\JobFactory $jobFactory,
        \Pharmao\Delivery\Helper\Data $helper
    ) {

        $this->logger = $logger;
        $this->order = $order;
        $this->_curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->_jobFactory = $jobFactory;
         $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */

    public function getPost($data)
    {

        $model = $this->_jobFactory->create();
        $collection = $model->getCollection()->addFieldToFilter('job_id', trim($data['id']));
        $job_id = $data['id'];
        $status = $data['status'];
        $jobData = $collection->getData();
        $returnArray = json_encode($data);
        if (!empty($jobData)) {
            $address = $jobData[0]['address'];
            $jobUpdate = $model->load($jobData[0]['id']);
            $jobUpdate->setStatus($status);
            $jobUpdate->setAdded(date("Y-m-d H:i:s"));
            $saveData = $jobUpdate->save();
        }
        return true;
    }
}
