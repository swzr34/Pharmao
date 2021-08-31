<?php 
namespace Pharmao\Delivery\Model\Config\Source;

class Kilometers implements \Magento\Framework\Data\OptionSourceInterface
{
 public function toOptionArray()
 {
  return [
    ['value' => '', 'label' => __('Select Distance Range')],
    ['value' => '1', 'label' => __('1')],
    ['value' => '2', 'label' => __('2')],
    ['value' => '3', 'label' => __('3')],
    ['value' => '4', 'label' => __('4')],
    ['value' => '5', 'label' => __('5')],
    ['value' => '6', 'label' => __('6')],
    ['value' => '7', 'label' => __('7')],
    ['value' => '8', 'label' => __('8')],
    ['value' => '9', 'label' => __('9')],
    ['value' => '10', 'label' => __('10')]
  ];
 }
}
?>