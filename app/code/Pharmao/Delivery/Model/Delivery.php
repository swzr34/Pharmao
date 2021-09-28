<?php 
namespace Pharmao\Delivery\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;

class Delivery extends AbstractModel
{
    protected $storeScope;
    protected $statusCollection;
    
     /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Order\StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
        
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
    }
    
    public function getConfigData($key, $path = 'general') {
        $configValue = $this->scopeConfig->getValue('delivery_configuration/' . $path . '/' . $key, $this->storeScope);
        return $configValue;
    }
    
    public function getWeightUnit() {
        return $this->scopeConfig->getValue('general/locale/weight_unit', $this->storeScope);
    }
    
    public function getBaseUrl($url) {
        return $this->getConfigData('base_url') . $url;
    }
    
    public function getStatusCollection() {
        $status = $this->statusFactory->create();
         $collection = $status->getCollection()
                        ->addFieldToFilter('status', 'fraud');
        return $collection->getData();
    }

}
?>