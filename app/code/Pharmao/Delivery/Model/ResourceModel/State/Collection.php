<?php

namespace Pharmao\Delivery\Model\ResourceModel\State;

class Collection
    extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            'Pharmao\Delivery\Model\State',
            'Pharmao\Delivery\Model\ResourceModel\State'
        );
    }
}
?>