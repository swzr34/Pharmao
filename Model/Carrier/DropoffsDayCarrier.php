<?php
namespace Pharmao\Delivery\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class DropoffsDayCarrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'dropoffsday';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    protected $scopeConfig;

    protected $_cart;

    protected $helper;

    /**
     * Shipping constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array                                                       $data
     */
    public function __construct(
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Checkout\Model\Cart $cartModel,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Pharmao\Delivery\Model\Delivery $deliveryModel,
        \Pharmao\Delivery\Helper\Data $helper
    ) {
        $this->serializer = $serializer;
        $this->scopeConfig = $scopeConfig;
        $this->_rateFactory = $rateFactory;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_cart = $cartModel;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->model = $deliveryModel;
        $this->helper = $helper;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger);
    }

    /**
     * get allowed methods
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * @return float
     */
    private function getShippingPrice()
    {
        $configPrice = $this->getConfigData('price');

        $shippingPrice = $this->getFinalPriceWithHandlingFee($configPrice);

        return $shippingPrice;
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        $configDeliveryType = $this->model->getConfigData('delivery_type');

        if (!$this->model->isEnabled()
            || !$this->getConfigFlag('active')
            || $configDeliveryType == 1
        ) {
            return false;
        }

        $assignment_code = $this->helper->generateRandomNumber();
        $city = $request->getDestCity();
        $postCode = $request->getDestPostcode();
        $address = $request->getDestStreet();
        $fullAddress = $address . ", " . $postCode . " " . $city . ", " . $this->helper->getCountryName();

        $items = $this->_cart->getQuote()->getAllItems();
        $sub_total = $this->_cart->getQuote()->getSubtotal();
        $total = $this->_cart->getQuote()->getGrandTotal();

        $weight = 0;
        foreach ($items as $item) {
            $weight += ($item->getWeight() * $item->getQty()) ;
        }

        $weight_limit = '';
        if ($this->model->getWeightUnit() == 'kgs') {
            $weight_limit = '10';
        } else {
            $weight_limit = '22.0462';
        }

        $pharmaoDeliveryJobInstance = $this->helper->getPharmaoDeliveryJobInstance();
        $params = [
            'order_amount' => $total,
            'is_within_one_hour' => 0,
            'assignment_code' => $assignment_code,
            'order_id' => '',
            'customer_address' => $fullAddress
        ];
        
        $response = '';    
        if ($pharmaoDeliveryJobInstance->getAccessToken()) {
            $response = $pharmaoDeliveryJobInstance->getPrice($params);
        }
        $result = $this->_rateResultFactory->create();

        /*store shipping in session*/
        if ($response && isset($response->code) && 200 == $response->code) {
            $limitationOfKms = $this->model->getConfigData('distance_range');

            if (isset($response->data->distance) && $response->data->distance < $limitationOfKms && $weight < $weight_limit) {
                $method = $this->_rateMethodFactory->create();

                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->getConfigData('title'));

                $method->setMethod($this->_code);
                $method->setMethodTitle($this->getConfigData('name'));

                $amount = $this->getShippingPrice();

                $method->setPrice($response->data->amount);
                $method->setCost($response->data->amount);

                $result->append($method);

                return $result;
            }
        }
    }
}
