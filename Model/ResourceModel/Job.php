<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Model\ResourceModel;

/**
 * Class Job.
 */
class Job extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('pharmao_job', 'id');
    }
}
