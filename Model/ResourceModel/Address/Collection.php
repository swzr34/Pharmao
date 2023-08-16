<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Model\ResourceModel\Address;

/**
 * Class Collection.
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(
            'Pharmao\Delivery\Model\Address',
            'Pharmao\Delivery\Model\ResourceModel\Address'
        );
    }
}
