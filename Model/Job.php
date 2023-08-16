<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Model;

/**
 * Class Job.
 */
class Job extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('Pharmao\Delivery\Model\ResourceModel\Job');
    }
}
