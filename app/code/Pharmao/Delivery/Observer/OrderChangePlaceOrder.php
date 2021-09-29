<?php

namespace Pharmao\Delivery\Observer;

class OrderChangePlaceOrder
{
    protected $scopeConfig;

    protected $_jobFactory;

    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Pharmao\Delivery\Model\JobFactory $jobFactory,
        \Pharmao\Delivery\Model\Delivery $deliveryModel,
        \Pharmao\Delivery\Helper\Data $helper
    ) {
        $this->_curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->_jobFactory = $jobFactory;
        $this->model = $deliveryModel;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagementInterface
     * @param \Magento\Sales\Model\Order\Interceptor $order
     * @return $order
     */
    public function afterPlace(
        \Magento\Sales\Api\OrderManagementInterface $orderManagementInterface,
        $order
    ) {
        $orderId = $order->getId();
        $assignment_code = $this->helper->generateRandomNumber();
        $address_data = $this->helper->getFullAddress($order->getShippingAddress());
        $full_address = $address_data['full_address'];
        $config_status = $this->model->getConfigData('pharmao_delivery_active_status');
        $config_state = $this->model->getConfigData('pharmao_delivery_active_stat');
        $isWithinOneHour = $this->model->getConfigData('pharmao_delivery_within_one_hour');
        $configIsWithinOneHour = $isWithinOneHour ? $isWithinOneHour : 0;
        // Generate Log File
        $logData = array(
            'status' => $order->getStatus(),
            'state' => $order->getState(),
            'status1' => $config_status,
            'state1' => $config_state,
            'order_amount' => $order->getGrandTotal(),
            'is_within_one_hour' => $configIsWithinOneHour
        );
        $this->helper->generateLog('status-updated', $logData);

        if ($order->getStatus() == $config_status && $order->getState() == $config_state) {
            $pharmaoDeliveryJobInstance = $this->helper->getPharmaoDeliveryJobInstance();
            $response = $pharmaoDeliveryJobInstance->validateAndCreateJob(array(
                'order_amount' => $order->getGrandTotal(),
                'assignment_code' => $assignment_code,
                'order_id' => $order->getEntityId(),
                'is_within_one_hour' => $configIsWithinOneHour,
                'customer_firstname' => $order->getCustomerFirstname(),
                'customer_lastname' => $order->getCustomerLastname(),
                'customer_comment' => $address_data['street_1'],
                'customer_address' => $full_address,
                'customer_phone' => $order->getShippingAddress()->getTelephone(),
                'customer_email' => $order->getCustomerEmail(),
            ));

            if ($response && isset($response->code) && 200 == $response->code) {
                $model = $this->_jobFactory->create();
                $model->addData([
                    "order_id" => $order->getEntityId(),
                    "job_id" => $response->data->job_id,
                    "status" => $response->data->status,
                    "address" => $full_address,
                    "added" => date("Y-m-d H:i:s")
                ]);

                $saveData = $model->save();
            }
        }

        return $order;
    }
}
