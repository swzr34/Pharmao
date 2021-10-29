<?php

namespace Pharmao\Delivery\Model\ResourceModel;

class State extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('sales_order_status_state', 'status');
    }
}
