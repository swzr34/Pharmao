<?php
namespace Pharmao\Delivery\Observer;

use Magento\Framework\Event\ObserverInterface;

class AfterPlaceOrder implements ObserverInterface
{
    protected $_addressFactory;

    protected $helper;

    public function __construct(
        \Pharmao\Delivery\Model\AddressFactory $addressFactory,
        \Pharmao\Delivery\Helper\Data $helper
    ) {
        $this->_addressFactory = $addressFactory;
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
       
        if ($order->getShippingAddress() != null || $order->getShippingAddress() != '') {
            $shippingAddress = $order->getShippingAddress();
            $customerAddressId = ($order->getShippingAddress()) ? $shippingAddress->getCustomerAddressId() : 0;
            $customerId = 0;
    
            if ($order->getCustomerId()) {
                $customerId = $order->getCustomerId();
            }
    
            $streetData = $shippingAddress->getStreet();
            $postCode = $shippingAddress->getPostCode();
            $city = $shippingAddress->getCity();
    
            $street1 = isset($streetData[0]) ? $streetData[0] : '';
            $street2 = isset($streetData[1]) ? $streetData[1] : '';
            $street3 = isset($streetData[2]) ? $streetData[2] : '';
            $country = $this->helper->getCountryName();
            $email = $order->getCustomerEmail();
    
            $model = $this->_addressFactory->create();
            $collection = $model->getCollection()
                ->addFieldToFilter('email', trim($email))
                ->addFieldToFilter('street1', trim($street1))
                ->addFieldToFilter('street2', trim($street2))
                ->addFieldToFilter('city', trim($city))
                ->addFieldToFilter('postCode', trim($postCode))
                ->addFieldToFilter('country', trim($country));
    
            if (empty($collection->getData())) {
                $model->addData([
                    "customer_id" => trim($customerId),
                    "email" => trim($email),
                    "address_id" => trim($customerAddressId),
                    "street1" => trim($street1),
                    "street2" => trim($street2),
                    "street3" => trim($street3),
                    "city" => trim($city),
                    "postcode" => trim($postCode),
                    "country" => trim($country)
                ]);
    
                $saveData = $model->save();
            }
        }
    }
}
