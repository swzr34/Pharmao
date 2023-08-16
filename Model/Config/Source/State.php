<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;
use Pharmao\Delivery\Model\Configuration;
use Pharmao\Delivery\Model\ResourceModel\State\CollectionFactory as StateCollectionFactory;

/**
 * Class State.
 */
class State implements OptionSourceInterface
{
    /**
     * @var StateCollectionFactory
     */
    protected StateCollectionFactory $stateCollectionFactory;

    /**
     * @var OrderStatusCollection
     */
    protected OrderStatusCollection $orderStatusCollection;

    /**
     * @var Configuration
     */
    protected Configuration $configuration;

    /**
     * @param OrderStatusCollection  $orderStatusCollection
     * @param StateCollectionFactory $stateCollectionFactory
     * @param Configuration          $configuration
     */
    public function __construct(
        OrderStatusCollection $orderStatusCollection,
        StateCollectionFactory $stateCollectionFactory,
        Configuration $configuration
    ) {
        $this->orderStatusCollection = $orderStatusCollection;
        $this->stateCollectionFactory = $stateCollectionFactory;
        $this->configuration = $configuration;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $stateArr = [];

        if ($phmActiveStatus = $this->configuration->getConfigData('pharmao_delivery_active_status')) {
            $collection = $this->stateCollectionFactory->create();
            $collection->addFieldToFilter('status', trim($phmActiveStatus));

            foreach ($collection->getData() as $key => $stateData) {
                $stateArr[$key]['value'] = $stateData['state'];
                $stateArr[$key]['label'] = ucwords(str_replace('_', ' ', $stateData['state']));
            }
        }

        return $stateArr;
    }
}
