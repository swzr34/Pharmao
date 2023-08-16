<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Used in creating options for getting product type value.
 *
 * Class DeliveryType
 */
class DeliveryType implements OptionSourceInterface
{
    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '0', 'label' => __('Activate delivery in the day')],
            ['value' => '1', 'label' => __('Activate in one hour')],
            ['value' => '2', 'label' => __('Activate both')],
        ];
    }
}
