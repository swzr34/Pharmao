<?php

namespace Pharmao\Delivery\Model\ResourceModel\Job;

class Collection
    extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            'Pharmao\Delivery\Model\Job',
            'Pharmao\Delivery\Model\ResourceModel\Job'
        );
    }
}
?>