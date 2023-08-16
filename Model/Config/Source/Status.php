<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;

/**
 * Class Status.
 */
class Status implements OptionSourceInterface
{
    /**
     * @var OrderStatusCollection
     */
    private OrderStatusCollection $orderStatusCollection;

    public function __construct(OrderStatusCollection $orderStatusCollection)
    {
        $this->orderStatusCollection = $orderStatusCollection;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return $this->orderStatusCollection->toOptionArray();
    }
}
