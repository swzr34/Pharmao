<?php
namespace Pharmao\Delivery\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Button extends Field
{
    protected $_template = 'Pharmao_Delivery::system/config/button.phtml';
    
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
    
    public function getControllerUrl($url)
    {
        return $this->getUrl($url);
    }
    
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(['id' => 'validate_api', 'label' => __('Validate API'),]);
        return $button->toHtml();
    }
}