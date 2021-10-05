<?php

namespace Pharmao\Delivery\Model\ResourceModel\Address;

class Collection
    extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            'Pharmao\Delivery\Model\Address',
            'Pharmao\Delivery\Model\ResourceModel\Address'
        );
    }
}