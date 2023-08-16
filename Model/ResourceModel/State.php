<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Model\ResourceModel;

/**
 * Class State.
 */
class State extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('sales_order_status_state', 'status');
    }
}
