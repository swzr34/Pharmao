<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Pharmao\Delivery\Helper\Data;
use Pharmao\Delivery\Model\Configuration;
use Pharmao\Delivery\Model\JobFactory;
use Pharmao\Delivery\Model\ResourceModel\Job as JobResource;

/**
 * Class OrderChange.
 */
class OrderChange implements ObserverInterface
{
    protected string $_within_hour_code = 'dropoffs_dropoffs';

    protected string $_within_day_code = 'dropoffsday_dropoffsday';

    /**
     * @var JobFactory
     */
    protected JobFactory $jobFactory;

    /**
     * @var JobResource
     */
    protected JobResource $jobResource;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var Configuration
     */
    protected Configuration $configuration;

    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * @param JobFactory            $jobFactory
     * @param JobResource           $jobResource
     * @param Configuration         $configuration
     * @param Data                  $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        JobFactory $jobFactory,
        JobResource $jobResource,
        Configuration $configuration,
        Data $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->jobFactory = $jobFactory;
        $this->jobResource = $jobResource;
        $this->configuration = $configuration;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     *
     * @return bool
     *
     * @throws \JsonException
     * @throws \Zend_Log_Exception
     */
    public function execute(Observer $observer): bool
    {
        $order = $observer->getEvent()->getData('order');
        $storeId = $order->getStore()->getId();

        if (!$this->configuration->isEnabled((int) $storeId)) {
            return false;
        }

        $assignmentCode = $this->helper->generateRandomNumber();

        $configStatus = $this->configuration->getConfigData('pharmao_delivery_active_status', 'general', (int) $storeId);
        $configState = $this->configuration->getConfigData('pharmao_delivery_active_stat', 'general', (int) $storeId);
        $configIsWithinOneHour = $this->configuration->getConfigData('delivery_type', 'general', (int) $storeId) ?? 0;

        $isPharmaoOrder = false;
        if ($order->getShippingMethod() == $this->_within_day_code || $order->getShippingMethod() == $this->_within_hour_code) {
            $isPharmaoOrder = true;
            if ($order->getShippingMethod() == $this->_within_day_code) {
                $configIsWithinOneHour = 0;
            } else {
                $configIsWithinOneHour = 1;
            }
        }

        if (!$isPharmaoOrder) {
            return false;
        }

        if ($order->getStatus() == $configStatus && $order->getState() == $configState) {
            $addressData = $this->helper->getFullAddress($order->getShippingAddress());
            $fullAddress = $addressData['full_address'];

            $pharmaoDeliveryJobInstance = $this->helper->getPharmaoDeliveryJobInstance();

            $response = $pharmaoDeliveryJobInstance->validateAndCreateJob([
                'order_amount' => $order->getGrandTotal(),
                'assignment_code' => $assignmentCode,
                'order_id' => $order->getIncrementId(),
                'is_within_one_hour' => $configIsWithinOneHour,
                'customer_firstname' => $order->getCustomerFirstname(),
                'customer_lastname' => $order->getCustomerLastname(),
                'customer_comment' => $addressData['street_1'],
                'customer_address' => $fullAddress,
                'customer_phone' => $order->getShippingAddress()->getTelephone(),
                'customer_email' => $order->getCustomerEmail(),
            ]);

            if ($response && isset($response['code']) && 200 == $response['code']) {
                $model = $this->jobFactory->create();
                $model->addData([
                    'order_id' => $order->getEntityId(),
                    'job_id' => $response['data']['job_id'] ?? '',
                    'store_id' => $order->getStore()->getStoreId(),
                    'status' => $response['data']['status'] ?? '',
                    'address' => $fullAddress,
                    'added' => date('Y-m-d H:i:s'),
                ]);

                $this->jobResource->save($model);
            }
        }

        return true;
    }
}
