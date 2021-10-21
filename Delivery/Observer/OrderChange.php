<?php

namespace Pharmao\Delivery\Observer;

class OrderChange implements \Magento\Framework\Event\ObserverInterface
{
    protected $_jobFactory;
    
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Pharmao\Delivery\Model\JobFactory $jobFactory,
        \Pharmao\Delivery\Model\Delivery $deliveryModel,
        \Pharmao\Delivery\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_curl = $curl;
        $this->_jobFactory = $jobFactory;
        $this->model = $deliveryModel;
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $assignment_code = $this->helper->generateRandomNumber();
        $order = $observer->getEvent()->getOrder();
        
        $address_data = $this->helper->getFullAddress($order->getShippingAddress());
        $full_address = $address_data['full_address'];
        $config_status = $this->model->getConfigData('pharmao_delivery_active_status');
        $config_state = $this->model->getConfigData('pharmao_delivery_active_stat');
        $configIsWithinOneHour = $this->model->getConfigData('delivery_type');
        
        $configIsWithinOneHour = 0;
        if ($order->getShippingMethod() == "dropoffsday_dropoffsday" || $order->getShippingMethod() == "dropoffs_dropoffs" ) {
            if ($order->getShippingMethod() == "dropoffsday_dropoffsday") {
                $configIsWithinOneHour = 0;    
            } else {
                $configIsWithinOneHour = 1;    
            }
        }
        
        if ($order->getStatus() == $config_status && $order->getState() == $config_state) {
            
            $storeId = $order->getStore()->getId(); 
            $this->model->setStoreId($storeId);

            $pharmaoDeliveryJobInstance = $this->helper->getPharmaoDeliveryJobInstance();

            $response = $pharmaoDeliveryJobInstance->validateAndCreateJob(array(
                'order_amount' => $order->getGrandTotal(),
                'assignment_code' => $assignment_code,
                'order_id' => $order->getIncrementId(),
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
                    "website_id" => $order->getStore()->getStoreId(),
                    "status" => $response->data->status,
                    "address" => $full_address,
                    "added" => date("Y-m-d H:i:s")
                ]);

                $saveData = $model->save();
            }
        }
    }
}
