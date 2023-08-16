<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Pharmao\Delivery\Helper\Data;
use Pharmao\Delivery\Model\AddressFactory;
use Pharmao\Delivery\Model\Configuration;
use Pharmao\Delivery\Model\ResourceModel\Address as AddressResource;
use Pharmao\Delivery\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;

/**
 * Class AfterPlaceOrder.
 */
class AfterPlaceOrder implements ObserverInterface
{
    /**
     * @var AddressFactory
     */
    protected AddressFactory $addressFactory;

    /**
     * @var AddressResource
     */
    protected AddressResource $addressResource;

    /**
     * @var AddressCollectionFactory
     */
    protected AddressCollectionFactory $addressCollectionFactory;

    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * @var Configuration
     */
    protected Configuration $configuration;

    /**
     * @param AddressFactory           $addressFactory
     * @param AddressResource          $addressResource
     * @param AddressCollectionFactory $addressCollectionFactory
     * @param Configuration            $configuration
     * @param Data                     $helper
     */
    public function __construct(
        AddressFactory $addressFactory,
        AddressResource $addressResource,
        AddressCollectionFactory $addressCollectionFactory,
        Configuration $configuration,
        Data $helper
    ) {
        $this->addressFactory = $addressFactory;
        $this->addressResource = $addressResource;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->configuration = $configuration;
        $this->helper = $helper;
    }

    /**
     * @param Observer $observer
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function execute(Observer $observer): bool
    {
        if (!$this->configuration->isEnabled()) {
            return false;
        }

        $order = $observer->getEvent()->getOrder();
        if (!$order->getShippingAddress()) {
            return false;
        }

        $shippingAddress = $order->getShippingAddress();
        $customerAddressId = ($order->getShippingAddress()) ? $shippingAddress->getCustomerAddressId() : 0;
        $customerId = 0;

        if ($order->getCustomerId()) {
            $customerId = $order->getCustomerId();
        }

        $streetData = $shippingAddress->getStreet();
        $postCode = $shippingAddress->getPostCode();
        $city = $shippingAddress->getCity();

        $street1 = $streetData[0] ?? '';
        $street2 = $streetData[1] ?? '';
        $street3 = $streetData[2] ?? '';
        $country = $this->helper->getCountryName();
        $email = $order->getCustomerEmail();

        $collection = $this->addressCollectionFactory->create();
        $collection->addFieldToFilter('email', trim($email))
            ->addFieldToFilter('street1', trim($street1))
            ->addFieldToFilter('street2', trim($street2))
            ->addFieldToFilter('city', trim($city))
            ->addFieldToFilter('postCode', trim($postCode))
            ->addFieldToFilter('country', trim($country));

        if (empty($collection->getData())) {
            $model = $this->addressFactory->create();
            $model->addData([
                'customer_id' => trim((string) $customerId),
                'email' => trim($email),
                'address_id' => trim((string) $customerAddressId),
                'street1' => trim($street1),
                'street2' => trim($street2),
                'street3' => trim($street3),
                'city' => trim($city),
                'postcode' => trim($postCode),
                'country' => trim($country),
            ]);

            $this->addressResource->save($model);
        }

        return true;
    }
}
