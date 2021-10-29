<?php
namespace Pharmao\Delivery\Model\Config\Source;

use Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;

class State implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $stateFactory;
     
    private $orderStatusCollection;
    
    public function __construct(
        OrderStatusCollection $orderStatusCollection,
        \Pharmao\Delivery\Model\StateFactory $stateFactory,
        \Pharmao\Delivery\Model\Delivery $deliveryModel
    ) {
        $this->orderStatusCollection=$orderStatusCollection;
        $this->stateFactory = $stateFactory;
        $this->model = $deliveryModel;
    }
    
    public function toOptionArray()
    {
        $model = $this->stateFactory->create();
        $collection = $model->getCollection()
                        ->addFieldToFilter('status', trim($this->model->getConfigData('pharmao_delivery_active_status')));
        
        $stateArr = [];
        foreach ($collection->getData() as $key => $stateData) {
            $stateArr[$key]['value'] = $stateData['state'];
            $stateArr[$key]['label'] = ucwords(str_replace('_', ' ', $stateData['state']));
        }
        
        return $stateArr;
    }
}
