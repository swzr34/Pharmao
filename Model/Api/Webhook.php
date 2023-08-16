<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Model\Api;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Pharmao\Delivery\Api\WebhookInterface;
use Pharmao\Delivery\Helper\Data;
use Pharmao\Delivery\Model\JobFactory;
use Pharmao\Delivery\Model\ResourceModel\Job as JobResource;
use Pharmao\Delivery\Model\ResourceModel\Job\CollectionFactory as JobCollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Webhook.
 */
class Webhook implements WebhookInterface
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var JobFactory
     */
    protected JobFactory $jobFactory;

    /**
     * @var JobResource
     */
    protected JobResource $jobResource;

    /**
     * @var JobCollectionFactory
     */
    protected JobCollectionFactory $jobCollectionFactory;

    /**
     * @var Order
     */
    protected Order $order;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * @param LoggerInterface      $logger
     * @param Order                $order
     * @param ScopeConfigInterface $scopeConfig
     * @param JobFactory           $jobFactory
     * @param JobResource          $jobResource
     * @param JobCollectionFactory $jobCollectionFactory
     * @param Data                 $helper
     */
    public function __construct(
        LoggerInterface $logger,
        Order $order,
        ScopeConfigInterface $scopeConfig,
        JobFactory $jobFactory,
        JobResource $jobResource,
        JobCollectionFactory $jobCollectionFactory,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->order = $order;
        $this->scopeConfig = $scopeConfig;
        $this->jobFactory = $jobFactory;
        $this->jobResource = $jobResource;
        $this->jobCollectionFactory = $jobCollectionFactory;
        $this->helper = $helper;
    }

    /**
     * @param mixed $data
     *
     * @return bool
     */
    public function getPost(mixed $data): bool
    {
        $collection = $this->jobCollectionFactory->create();
        $collection->addFieldToFilter('job_id', trim((string) $data['id']));
        $jobData = $collection->getData();
        if (!empty($jobData)) {
            $jobUpdate = $this->jobFactory->create();
            $this->jobResource->load($jobUpdate, $jobData[0]['id']);
            $jobUpdate->setStatus($data['status']);
            $jobUpdate->setAdded(date('Y-m-d H:i:s'));
            $this->jobResource->save($jobUpdate);
        }

        return true;
    }
}
