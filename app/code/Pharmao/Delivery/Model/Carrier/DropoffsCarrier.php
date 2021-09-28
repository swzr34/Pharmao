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
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        
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
        
        $url = $this->model->getBaseUrl('/job/price');
        $params = array(
            "job" => array(
                "external_order_amount" => $total,
                "client_type" => 'magento',
                "package_type" => "small",
                "is_within_one_hour" => 1,
                "transport_type" => "bike",
                "pickups" => [
                    array(
                        "address" => $this->model->getConfigData('address', 'global_settings') . ", ". $this->model->getConfigData('postcode', 'global_settings') . " " . $this->model->getConfigData('city', 'global_settings') . ", " . $this->helper->getCountryName(),
                    ),
                ],
                "dropoffs" => array(
                    array(
                        "address" => $fullAddress . ", " . $this->helper->getCountryName(),
                    ),
                )
            )
        );
        $data = $this->helper->performPost($url, $params);
        $result = $this->_rateResultFactory->create();
        
        // Generate Log File
        $logData = array(
                            'weight_unit' => $this->model->getWeightUnit(),
                            'weight' => $weight,
                            'Sub' => $sub_total,
                            'Total' => $total,
                            'Url' => $url,
                            'res' => print_r($data, true)
                    );
        $this->helper->generateLog('price-log', $logData);
        
        /*store shipping in session*/
        if (isset($data->data->amount)) {
            $limitationOfKms = $this->model->getConfigData('distance_range');
            
            if (isset($data->data->distance) && $data->data->distance < $limitationOfKms && $weight < $weight_limit) {

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