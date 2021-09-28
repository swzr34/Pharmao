<?php

namespace Pharmao\Delivery\Observer;
class OrderChangePlaceOrder {
    
      protected $scopeConfig;
  
      protected $_jobFactory;
    
      public function __construct(\Magento\Framework\HTTP\Client\Curl $curl, 
                                    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
                                    \Pharmao\Delivery\Model\JobFactory $jobFactory,
                                    \Pharmao\Delivery\Model\Delivery $deliveryModel,
                                    \Pharmao\Delivery\Helper\Data $helper
                                )
      {
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
    public function afterPlace(\Magento\Sales\Api\OrderManagementInterface $orderManagementInterface , $order)
    {
        $orderId = $order->getId();
        $assignment_code = $this->helper->generateRandomNumber();
        $address_data = $this->helper->getFullAddress($order->getShippingAddress());
        $full_address = $address_data['full_address'];
        $access_token = "Bearer " . $this->model->getConfigData('access_token');
        $config_status = $this->model->getConfigData('pharmao_delivery_active_status');
    
    
        if ($order->getStatus() == $config_status) {
          $data = array(
            'job' =>
            array(
              'assignment_code' => $assignment_code,
              'external_order_reference' => $order->getEntityId(),
              'transport_type' => 'Bike',
              'package_type' => 'small',
              'package_description' => '',
              'comment' => 'this is a test comment',
              'is_within_one_hour' => 1,
              'pickups' =>
              array(
                0 =>
                array(
                  'comment' => 'Rentrez dans la pharmacie, allez au comptoir et demander la commande Pharmao Nom: ' . $order->getCustomerFirstname() . " " . $order->getCustomerLastname(),
                  'contact' =>
                  array(
                    'firstname' => $this->model->getConfigData('firstname', 'global_settings'),
                    'phone' => $this->model->getConfigData('phone', 'global_settings'),
                  ),
                  'address' => $this->model->getConfigData('address', 'global_settings') . ", " . $this->model->getConfigData('postcode', 'global_settings') . " " . $this->model->getConfigData('city', 'global_settings') . ", " . $this->helper->getCountryName(),
                ),
              ),
              'dropoffs' =>
              array(
                0 =>
                array(
                  'comment' => $address_data['street_1'],
                  'address' => $full_address,
                  'contact' =>
                  array(
                    'firstname' => $order->getCustomerFirstname(),
                    'phone' => $order->getShippingAddress()->getTelephone(),
                    'lastname' => $order->getCustomerLastname(),
                    'email' => $order->getCustomerEmail(),
                  ),
                ),
              ),
            ),
          );
    
          $data_json = json_encode($data);
    
          
        $validate_url = $this->model->getBaseUrl('/job/validate');
        $response = $this->helper->performPost($validate_url, $data_json);
    
            if (isset($response->data->is_valid) && $response->data->is_valid == true) {
            // $logger->info('response : ' . $response->data->is_valid);
            $model = $this->_jobFactory->create();
            $creat_job_url = $this->model->getBaseUrl('/jobs');
            $job_response_decode = $this->helper->performPost($creat_job_url, $data_json);
              $model->addData([
            	"order_id" => $order->getEntityId(),
            	"job_id" => $job_response_decode->data->job_id,
            	"status" => $job_response_decode->data->status,
            	"address" => $full_address,
            	"added" => date("Y-m-d H:i:s")
            	]);
                $saveData = $model->save();
                
                // Generate Log File
            	$logData = array(
                                'job_response123' => print_r($job_response_decode, true)
                        );
                $this->helper->generateLog('status-updated', $logData);
            }
        }

       return $order;
    }
}
