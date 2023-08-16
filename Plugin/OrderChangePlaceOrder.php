<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Pharmao\Delivery\Helper\Data;
use Pharmao\Delivery\Model\Configuration;
use Pharmao\Delivery\Model\JobFactory;
use Pharmao\Delivery\Model\ResourceModel\Job as JobResource;

/**
 * Class OrderChangePlaceOrder.
 */
class OrderChangePlaceOrder
{
    /**
     * @var string
     */
    protected string $_within_hour_code = 'dropoffs_dropoffs';

    /**
     * @var string
     */
    protected string $_within_day_code = 'dropoffsday_dropoffsday';

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

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
     * @param ScopeConfigInterface  $scopeConfig
     * @param JobFactory            $jobFactory
     * @param JobResource           $jobResource
     * @param Configuration         $configuration
     * @param Data                  $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        JobFactory $jobFactory,
        JobResource $jobResource,
        Configuration $configuration,
        Data $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->jobFactory = $jobFactory;
        $this->jobResource = $jobResource;
        $this->configuration = $configuration;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param OrderManagementInterface $orderManagementInterface
     * @param OrderInterface           $result
     * @param Order                    $order
     *
     * @return Order
     *
     * @throws \JsonException
     * @throws \Zend_Log_Exception
     */
    public function afterPlace(
        OrderManagementInterface $orderManagementInterface,
        OrderInterface $result,
        OrderInterface $order
    ): OrderInterface {
        if ($this->configuration->isEnabled()) {
            $orderId = $order->getId();
            $storeId = $order->getStore()->getId();

            if ($order->getShippingAddress()) {
                $assignmentCode = $this->helper->generateRandomNumber();
                $addressData = $this->helper->getFullAddress($order->getShippingAddress());
                $fullAddress = $addressData['full_address'];
                $configStatus = $this->configuration->getConfigData('pharmao_delivery_active_status', 'general', (int) $storeId);
                $configState = $this->configuration->getConfigData('pharmao_delivery_active_stat', 'general', (int) $storeId);
                $configIsWithinOneHour = $this->configuration->getConfigData('delivery_type', 'general', (int) $storeId);
                $isPharmaoOrder = false;

                if (2 == $configIsWithinOneHour) {
                    if ($order->getShippingMethod() == $this->_within_day_code || $order->getShippingMethod() == $this->_within_hour_code) {
                        $isPharmaoOrder = true;
                        if ($order->getShippingMethod() == $this->_within_day_code) {
                            $configIsWithinOneHour = 0;
                        } else {
                            $configIsWithinOneHour = 1;
                        }
                    }
                }

                if ($isPharmaoOrder) {
                    if ($order->getStatus() == $configStatus && $order->getState() == $configState) {
                        $pharmaoDeliveryJobInstance = $this->helper->getPharmaoDeliveryJobInstance();

                        $response = $pharmaoDeliveryJobInstance->validateAndCreateJob([
                            'order_amount' => $order->getGrandTotal(),
                            'assignment_code' => $assignmentCode,
                            'order_id' => $order->getEntityId(),
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
                }
            }
        }

        return $result;
    }
}
