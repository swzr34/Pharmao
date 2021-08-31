<?php 
namespace Pharmao\Delivery\Model\Config\Source;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    
    private $orderStatusCollection;
    
    public function __construct(OrderStatusCollection $orderStatusCollection)
    {
        $this->orderStatusCollection=$orderStatusCollection;
    }
    
    public function toOptionArray()
    {
        return $this->orderStatusCollection->toOptionArray();
    }
}
?>