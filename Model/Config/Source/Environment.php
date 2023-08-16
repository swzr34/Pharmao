<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Environment.
 */
class Environment implements OptionSourceInterface
{
    public const TYPE_SANDBOX = 0;
    public const TYPE_LIVE = 1;

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::TYPE_SANDBOX, 'label' => __('Sandbox')],
            ['value' => self::TYPE_LIVE, 'label' => __('Live')],
        ];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->toOptionArray() as $option) {
            $result[$option['value']] = $option['label'];
        }

        return $result;
    }
}
