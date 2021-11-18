<?php

namespace Pharmao\Delivery\Helper;

use Pharmao\Delivery\Helper\Service\JobService;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    protected $storeScope;

    /**
     * Environments
     * @var array
     */
    protected $environments = [
        'sandbox' => 'https://delivery-sandbox.pharmao.fr/',
        'production' => 'https://pharmao-delivery-live.pharmao.fr/',
    ];
    
    /**
     * @param \Pharmao\Delivery\Model\Delivery          $deliveryModel
     */
    public function __construct(
        \Pharmao\Delivery\Model\Delivery $deliveryModel,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->model = $deliveryModel;
        $this->_countryFactory = $countryFactory;
    }
   
    public function generateRandomNumber()
    {
        return md5(microtime(true).mt_Rand());
    }
   
    public function getFullAddress($shippingAddress)
    {
        $street_data = $shippingAddress->getStreet();
        $street_0 = isset($street_data[0]) ? $street_data[0] : '';
        $street_1 = isset($street_data[1]) ? $street_data[1] : '';
        $post_code = $shippingAddress->getPostCode();
        $city = $shippingAddress->getCity();
        $full_address = $street_0 . " " . $street_1 . ", " . $post_code . " " . $city . ", " . $this->getCountryName();
        return ['full_address' => $full_address, 'street_1' => $street_1];
    }
   
    public function getCountryName()
    {
        $country = $this->_countryFactory->create()->loadByCode($this->model->getConfigData('pharmaocountry'));
        return $country->getName();
    }

    /**
     * Get PharmaoDeliveryJobInstance
     * @return  [description]
     */
    public function getPharmaoDeliveryJobInstance()
    {
        if (!$this->model->getConfigData('api_key', 'general')) {
            return false;
        }
        
        $pharmaoDelivery = new JobService([
            'country' => $this->_countryFactory,
            'config' => $this->model,
            'secret' => $this->model->getConfigData('api_key', 'general'),
            'username' => $this->model->getConfigData('username', 'general'),
            'password' => $this->model->getConfigData('password', 'general'),
            'environment' => $this->model->getConfigData('environment', 'general'),
            'base_url' => ($this->model->getConfigData('environment', 'general'))
                ? $this->environments['production'] : $this->environments['sandbox'],
        ]);
        return $pharmaoDelivery;
    }
    
    /**
     * Get PharmaoDeliveryJobInstance
     * @return  [description]
     */
    public function getJobMapUrl()
    {
        $base_url = ($this->model->getConfigData('environment', 'general'))
                ? $this->environments['production'] : $this->environments['sandbox'];
        return $base_url . 'job-map/';
    }
    
    /**
     * Check Domain
     * @return  [boolean]
     */
    public function checkDomain()
    {
        $base_url = ($this->model->getConfigData('environment', 'general'))
                ? $this->environments['production'] : $this->environments['sandbox'];
        
        $result = false;
        $url = filter_var($base_url, FILTER_VALIDATE_URL);
        
        /* Open curl connection */
        $handle = curl_init($url);
        
        /* Set curl parameter */
        curl_setopt_array($handle, array(
            CURLOPT_FOLLOWLOCATION => TRUE,     // we need the last redirected url
            CURLOPT_NOBODY => TRUE,             // we don't need body
            CURLOPT_HEADER => FALSE,            // we don't need headers
            CURLOPT_RETURNTRANSFER => FALSE,    // we don't need return transfer
            CURLOPT_SSL_VERIFYHOST => FALSE,    // we don't need verify host
            CURLOPT_SSL_VERIFYPEER => FALSE     // we don't need verify peer
        ));
    
        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);
        
        $httpCode = curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);  // Try to get the last url
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);      // Get http status from last url
        
        /* Check for 200 (file is found). */
        if($httpCode == 200) {
            $result = true;
        }
        
        /* Close curl connection */
        curl_close($handle);
        return $result;
    }
}
