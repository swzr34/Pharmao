<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Model;

/**
 * Class Address.
 */
class Address extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('Pharmao\Delivery\Model\ResourceModel\Address');
    }
}
