<?php
namespace Pharmao\Delivery\Model\Config\Source;

/**
 * Used in creating options for getting product type value
 *
 */
class DeliveryType
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '0', 'label' => __('Activate delivery in the day')],
            ['value' => '1', 'label' => __('Activate in one hour')],
            ['value' => '2', 'label' => __('Activate both')]
        ];
    }
}
