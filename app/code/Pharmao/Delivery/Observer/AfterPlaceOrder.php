<?php
namespace Pharmao\Delivery\Observer;
 
use Magento\Framework\Event\ObserverInterface;
 
class AfterPlaceOrder implements ObserverInterface
{
    protected $_addressFactory;
    
    public function __construct(
          \Magento\Framework\View\Element\Template\Context $context,
          \Pharmao\Delivery\Model\AddressFactory $addressFactory,  array $data = []
          )
     {
          $this->_addressFactory = $addressFactory;
     }
     
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // ini_set('display_errors', -1);
       	$order = $observer->getEvent()->getOrder();
        // $guestCustomer = $order->getCustomerIsGuest();
        $shippingAddress = $order->getShippingAddress();
        $customerAddressId = 0;
        $customerId = 0;
        
        if ($order->getCustomerId()) {
           $customerId = $order->getCustomerId();
        }
        
        if ($shippingAddress->getCustomerAddressId()) {
           $customerAddressId = $shippingAddress->getCustomerAddressId();
        }
        
        $street_data = $shippingAddress->getStreet();
        $postCode = $shippingAddress->getPostCode();
        $city = $shippingAddress->getCity();
        
        $street1 = isset($street_data[0]) ? $street_data[0] : '';
        $street2 = isset($street_data[1]) ? $street_data[1] : '';
        $street3 = isset($street_data[2]) ? $street_data[2] : '';
        $country = "France";
        $email = $order->getCustomerEmail();
           
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/after-order.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('result : ' . $street1);
        $logger->info('result : ' . $street2);
        $logger->info('result : ' . $street3);
        $model = $this->_addressFactory->create();
        $collection = $model->getCollection()->addFieldToFilter('email', trim($email))->addFieldToFilter('street1', trim($street1))->addFieldToFilter('street2', trim($street2))->addFieldToFilter('city', trim($city))->addFieldToFilter('postCode', trim($postCode))->addFieldToFilter('country', trim($country));
        $logger->info('result : ' . print_r($collection->getData(), true));
        
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