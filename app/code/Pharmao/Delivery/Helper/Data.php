<?php

namespace Pharmao\Delivery\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    protected $storeScope;
    
     /**
     * @param \Magento\Framework\HTTP\Client\Curl       $curl
     * @param \Pharmao\Delivery\Model\Delivery          $deliveryModel
     */
    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Pharmao\Delivery\Model\Delivery $deliveryModel,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->_curl = $curl;
        $this->model = $deliveryModel;
        $this->_countryFactory = $countryFactory;
    }
    
    public function generateLog($logFileName, $data)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/' . $logFileName . '.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        foreach($data as $key => $value) {
            $logger->info($key . ' : ' . $value);
        }
    }
       
   public function performPost($url, $data) {
        $access_token = "Bearer " . $this->model->getConfigData('access_token');
        if($url != $this->model->getConfigData('base_url') . '/job/price' && $url != $this->model->getConfigData('base_url') . '/create-token') {
            $headers = ["Content-Type" => "application/json", "Authorization" => $access_token];
            $this->_curl->setHeaders($headers);
        }
        $this->_curl->post($url, $data);
        $response = $this->_curl->getBody(); 
        return json_decode($response);
   }
   
   public function generateRandomNumber() {
       return random_int(1000000000, 9999999999);
   }
   
   public function getFullAddress($shippingAddress) {
        $street_data = $shippingAddress->getStreet();
        $street_0 = isset($street_data[0]) ? $street_data[0] : '';
        $street_1 = isset($street_data[1]) ? $street_data[1] : '';
        $postCode = $shippingAddress->getPostCode();
        $city = $shippingAddress->getCity();
        $full_address = $street_0 . " " . $street_1 . ", " . $postCode . " " . $city . ", " . $this->getCountryName();
        return array('full_address' => $full_address, 'street_1' => $street_1);
   }
   
   public function getCountryName() {
        $country = $this->_countryFactory->create()->loadByCode($this->model->getConfigData('pharmaocountry'));
        return $country->getName();
   }
}