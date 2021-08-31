<?php

namespace Pharmao\Delivery\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Environment
 * @package Pharmao\Delivery\Model\Config\Source
 */
class Environment implements ArrayInterface
{
    const TYPE_SANDBOX = 0;
    const TYPE_LIVE = 1;

    public function toOptionArray()
    {
        return [
            ['value' => self::TYPE_SANDBOX, 'label' => __('Sandbox')],
            ['value' => self::TYPE_LIVE, 'label' => __('Live')]
        ];
    }

    public function toArray()
    {
        $result = [];
        foreach ($this->toOptionArray() as $option) {
            $result[$option['value']] = $option['label'];
        }
        return $result;
    }
}
