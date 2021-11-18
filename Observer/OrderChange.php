<?php

namespace Pharmao\Delivery\Observer;

class OrderChange implements \Magento\Framework\Event\ObserverInterface
{
    protected $_within_hour_code = 'dropoffs_dropoffs';
    
    protected $_within_day_code = 'dropoffsday_dropoffsday';
    
    protected $_jobFactory;
    
    protected $_storeManager;

    public function __construct(
        \Pharmao\Delivery\Model\JobFactory $jobFactory,
        \Pharmao\Delivery\Model\Delivery $deliveryModel,
        \Pharmao\Delivery\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_jobFactory = $jobFactory;
        $this->model = $deliveryModel;
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $storeId = $order->getStore()->getId();
        $this->model->setStoreId($storeId);
        
        if (!$this->model->isEnabled() || $this->helper->checkDomain() == false) {
            return false;
        }
        
        $assignmentCode = $this->helper->generateRandomNumber();
        
        $configStatus = $this->model->getConfigData('pharmao_delivery_active_status');
        $configState = $this->model->getConfigData('pharmao_delivery_active_stat');
        $configIsWithinOneHour = $this->model->getConfigData('delivery_type');
        
        $configIsWithinOneHour = 0;
        $isPharmaoOrder = false;
        if ($order->getShippingMethod() == $this->_within_day_code || $order->getShippingMethod() == $this->_within_hour_code) {
            $isPharmaoOrder = true;
            if ($order->getShippingMethod() == $this->_within_day_code) {
                $configIsWithinOneHour = 0;
            } else {
                $configIsWithinOneHour = 1;
            }
        }
        
        if ($isPharmaoOrder) {
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
