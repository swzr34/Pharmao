<?php
namespace Pharmao\Delivery\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class DropoffsCarrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'dropoffs';

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
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\SerializerInterface $serializer, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Checkout\Model\Cart $cartModel,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->serializer = $serializer;
        $this->_curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->_rateFactory = $rateFactory;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_cart = $cartModel;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
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
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $city = $request->getDestCity();
        $postCode = $request->getDestPostcode();
        $address = $request->getDestStreet();
        $fullAddress = $address . ", " . $postCode . " " . $city . ", " . "France";
        
        $items = $this->_cart->getQuote()->getAllItems();
        $sub_total = $this->_cart->getQuote()->getSubtotal();
        $total = $this->_cart->getQuote()->getGrandTotal();

        $weight = 0;
        foreach ($items as $item) {
            $weight += ($item->getWeight() * $item->getQty()) ;        
        }
        
        $weight_unit = '';
        if ($this->scopeConfig->getValue('general/locale/weight_unit', $storeScope) == 'kgs') {
            $weight_unit = '10';
        } else {
            $weight_unit = '22.0462';
        }
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/price-log.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        
        $logger->info('unit : ' . $this->scopeConfig->getValue('general/locale/weight_unit', $storeScope));
        $logger->info('weight : ' . $weight);
        $logger->info('Sub : ' . $sub_total);
        $logger->info('Total : ' . $total);
        //lbs 22.0462 -> 10kgs
        
        $url = 'https://delivery-sandbox.pharmao.fr/v1/job/price';
        $params = array(
            "job" => array(
                "external_order_amount" => $total,
                "client_type" => 'magento',
                "package_type" => "small",
                "is_within_one_hour" => 1,
                "transport_type" => "bike",
                "pickups" => [
                    array(
                        "address" => $this->scopeConfig->getValue('delivery_configuration/global_settings/address', $storeScope) . ", ". $this->scopeConfig->getValue('delivery_configuration/global_settings/postcode', $storeScope) . " " . $this->scopeConfig->getValue('delivery_configuration/global_settings/city', $storeScope) . ", France",
                    ),
                ],
                "dropoffs" => array(
                    array(
                        "address" => $fullAddress . ", France",
                    ),
                )
            )
        );
        
        $this->_curl->post($url, $params);
        $response = $this->_curl->getBody();
        $logger->info('res : ' . $response);
        $data = json_decode($response);
        $result = $this->_rateResultFactory->create();

        /*store shipping in session*/
        if (isset($data->data->amount)) {
            if (isset($data->data->distance) && $data->data->distance < 10 && $weight < $weight_unit) {

                /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                $method = $this->_rateMethodFactory->create();
        
                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->getConfigData('title'));
        
                $method->setMethod($this->_code);
                $method->setMethodTitle($this->getConfigData('name'));
        
                $amount = $this->getShippingPrice();
        
                $method->setPrice($data->data->amount);
                $method->setCost($data->data->amount);
        
                $result->append($method);
                
                return $result;
                
            } 
        } 
    }
}