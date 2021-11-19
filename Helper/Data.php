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
}
