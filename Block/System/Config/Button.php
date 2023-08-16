<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Button.
 */
class Button extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Pharmao_Delivery::system/config/button.phtml';

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @param $url
     *
     * @return string
     */
    public function getControllerUrl($url)
    {
        return $this->getUrl($url);
    }

    /**
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()
            ->createBlock(\Magento\Backend\Block\Widget\Button::class)
            ->setData([
                'id' => 'validate_api',
                'label' => __('Validate API'),
            ]);

        return $button->toHtml();
    }
}
