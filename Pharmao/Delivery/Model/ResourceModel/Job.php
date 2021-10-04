<?php

namespace Pharmao\Delivery\Model\ResourceModel;

class Job extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('pharmao_job', 'id');
    }
}
?>