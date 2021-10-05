<?php

namespace Pharmao\Delivery\Model;

class State extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Pharmao\Delivery\Model\ResourceModel\State');
    }
}