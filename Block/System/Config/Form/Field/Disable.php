<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Disable.
 */
class Disable extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $element->setDisabled('disabled');

        return $element->getElementHtml();
    }
}
