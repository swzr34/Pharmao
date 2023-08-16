<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Model\Carrier;

use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Pharmao\Delivery\Helper\Data;
use Pharmao\Delivery\Model\Configuration;
use Psr\Log\LoggerInterface;

class DropoffsCarrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'dropoffs';

    /**
     * @var ResultFactory
     */
    protected ResultFactory $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected MethodFactory $_rateMethodFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var Cart
     */
    protected Cart $_cart;

    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * @var ResultFactory
     */
    protected ResultFactory $_rateFactory;

    /**
     * @var Configuration
     */
    protected Configuration $configuration;

    /**
     * Shipping constructor.
     *
     * @param SerializerInterface  $serializer
     * @param ScopeConfigInterface $scopeConfig
     * @param ResultFactory        $rateFactory
     * @param Cart                 $cartModel
     * @param ErrorFactory         $rateErrorFactory
     * @param LoggerInterface      $logger
     * @param ResultFactory        $rateResultFactory
     * @param MethodFactory        $rateMethodFactory
     * @param Configuration        $configuration
     * @param Data                 $helper
     */
    public function __construct(
        SerializerInterface $serializer,
        ScopeConfigInterface $scopeConfig,
        ResultFactory $rateFactory,
        Cart $cartModel,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        Configuration $configuration,
        Data $helper
    ) {
        $this->serializer = $serializer;
        $this->scopeConfig = $scopeConfig;
        $this->_rateFactory = $rateFactory;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_cart = $cartModel;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->configuration = $configuration;
        $this->helper = $helper;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger);
    }

    /**
     * get allowed methods.
     *
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * @return float
     */
    private function getShippingPrice(): float
    {
        $configPrice = $this->getConfigData('price');

        return $this->getFinalPriceWithHandlingFee($configPrice);
    }

    /**
     * @param RateRequest $request
     *
     * @return bool|Result
     */
    public function collectRates(RateRequest $request): bool|Result
    {
        $assignment_code = $this->helper->generateRandomNumber();
        $configDeliveryType = $this->configuration->getConfigData('delivery_type');

        if (!$this->configuration->isEnabled()
            || !$this->getConfigFlag('active')
            || 0 == $configDeliveryType
        ) {
            return false;
        }

        $city = $request->getDestCity();
        $postCode = $request->getDestPostcode();
        $address = $request->getDestStreet();
        $fullAddress = $address.', '.$postCode.' '.$city.', '.$this->helper->getCountryName();

        $items = $this->_cart->getQuote()->getAllItems();
        $sub_total = $this->_cart->getQuote()->getSubtotal();
        $total = $this->_cart->getQuote()->getGrandTotal();

        $weight = 0;
        foreach ($items as $item) {
            $weight += ($item->getWeight() * $item->getQty());
        }

        $weight_limit = '';
        if ('kgs' == $this->configuration->getWeightUnit()) {
            $weight_limit = '10';
        } else {
            $weight_limit = '22.0462';
        }

        $pharmaoDeliveryJobInstance = $this->helper->getPharmaoDeliveryJobInstance();
        $params = [
            'order_amount' => $total,
            'is_within_one_hour' => 1,
            'assignment_code' => $assignment_code,
            'order_id' => '',
            'customer_address' => $fullAddress,
        ];

        $response = '';
        if ($pharmaoDeliveryJobInstance->getAccessToken()) {
            $response = $pharmaoDeliveryJobInstance->getPrice($params);
        }
        $result = $this->_rateResultFactory->create();

        /* store shipping in session */
        if ($response && isset($response['code']) && 200 == $response['code']) {
            $limitationOfKms = $this->configuration->getConfigData('distance_range');

            if (
                isset($response['data']['distance'])
                && $response['data']['distance'] < $limitationOfKms
                && $weight < $weight_limit
                && isset($response['data']['amount_within_one_hour'])
            ) {
                $method = $this->_rateMethodFactory->create();

                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->getConfigData('title'));

                $method->setMethod($this->_code);
                $method->setMethodTitle($this->getConfigData('name'));

                $amount = $this->getShippingPrice();

                $method->setPrice($response['data']['amount_within_one_hour']);
                $method->setCost($response['data']['amount_within_one_hour']);

                $result->append($method);

                return $result;
            }
        }

        return false;
    }
}
