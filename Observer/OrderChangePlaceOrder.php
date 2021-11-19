<?php

namespace Pharmao\Delivery\Observer;

class OrderChangePlaceOrder
{
    protected $_within_hour_code = 'dropoffs_dropoffs';
    
    protected $_within_day_code = 'dropoffsday_dropoffsday';
    
    protected $scopeConfig;

    protected $_jobFactory;
    
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Pharmao\Delivery\Model\JobFactory $jobFactory,
        \Pharmao\Delivery\Model\Delivery $deliveryModel,
        \Pharmao\Delivery\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_jobFactory = $jobFactory;
        $this->model = $deliveryModel;
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
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
        if ($this->model->isEnabled()) {
            $orderId = $order->getId();
            $storeId = $order->getStore()->getId();
            $this->model->setStoreId($storeId);
            if ($order->getShippingAddress()) {
                $assignmentCode = $this->helper->generateRandomNumber();
                $addressData = $this->helper->getFullAddress($order->getShippingAddress());
                $fullAddress = $addressData['full_address'];
                $configStatus = $this->model->getConfigData('pharmao_delivery_active_status');
                $configState = $this->model->getConfigData('pharmao_delivery_active_stat');
                $configIsWithinOneHour = $this->model->getConfigData('delivery_type');
                $isPharmaoOrder = false;
                
                if ($configIsWithinOneHour == 2) {
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
            
                        if ($response && isset($response->code) && 200 == $response->code) {
                            $model = $this->_jobFactory->create();
                            $model->addData([
                                "order_id" => $order->getEntityId(),
                                "job_id" => $response->data->job_id,
                                "store_id" => $order->getStore()->getStoreId(),
                                "status" => $response->data->status,
                                "address" => $fullAddress,
                                "added" => date("Y-m-d H:i:s")
                            ]);
            
                            $saveData = $model->save();
                        }
                    }
                }
            }
        }
        return $order;
    }
}
