<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Helper;

use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\ResourceModel\Country as CountryResource;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Pharmao\Delivery\Helper\Service\JobService;
use Pharmao\Delivery\Helper\Service\JobServiceFactory;
use Pharmao\Delivery\Model\Configuration;

/**
 * Class Data.
 */
class Data extends AbstractHelper
{
    /**
     * Environments.
     *
     * @var array
     */
    protected array $environments = [
        'sandbox' => 'https://delivery-sandbox.pharmao.fr/',
        'production' => 'https://pharmao-delivery-live.pharmao.fr/',
    ];

    /**
     * @var Configuration
     */
    protected Configuration $configuration;

    /**
     * @var CountryFactory
     */
    protected CountryFactory $countryFactory;

    /**
     * @var CountryResource
     */
    protected CountryResource $countryResource;

    /**
     * @var JobServiceFactory
     */
    protected JobServiceFactory $jobServiceFactory;

    /**
     * @param Context           $context
     * @param Configuration     $configuration
     * @param CountryFactory    $countryFactory
     * @param CountryResource   $countryResource
     * @param JobServiceFactory $jobServiceFactory
     */
    public function __construct(
        Context $context,
        Configuration $configuration,
        CountryFactory $countryFactory,
        CountryResource $countryResource,
        JobServiceFactory $jobServiceFactory
    ) {
        parent::__construct($context);
        $this->configuration = $configuration;
        $this->countryFactory = $countryFactory;
        $this->countryResource = $countryResource;
        $this->jobServiceFactory = $jobServiceFactory;
    }

    /**
     * @return string
     */
    public function generateRandomNumber(): string
    {
        return md5(microtime(true).mt_rand());
    }

    /**
     * @param $shippingAddress
     *
     * @return array
     */
    public function getFullAddress($shippingAddress): array
    {
        $street_data = $shippingAddress->getStreet();
        $street_0 = $street_data[0] ?? '';
        $street_1 = $street_data[1] ?? '';
        $post_code = $shippingAddress->getPostCode();
        $city = $shippingAddress->getCity();
        $full_address = $street_0.' '.$street_1.', '.$post_code.' '.$city.', '.$this->getCountryName();

        return ['full_address' => $full_address, 'street_1' => $street_1];
    }

    /**
     * @return string
     */
    public function getCountryName(): string
    {
        $country = $this->countryFactory->create();
        $this->countryResource->loadByCode($country, $this->configuration->getConfigData('pharmaocountry'));

        return $country->getName();
    }

    /**
     * Get PharmaoDeliveryJobInstance.
     *
     * @return false|JobService [description]
     */
    public function getPharmaoDeliveryJobInstance(): false|JobService
    {
        if (!$this->configuration->getConfigData('api_key')) {
            return false;
        }

        /** @var JobService $pharmaoDelivery */
        $pharmaoDelivery = $this->jobServiceFactory->create([
            'params' => [
                'country_factory' => $this->countryFactory,
                'country_resource' => $this->countryResource,
                'config' => $this->configuration,
                'secret' => $this->configuration->getConfigData('api_key'),
                'username' => $this->configuration->getConfigData('username'),
                'password' => $this->configuration->getConfigData('password'),
                'environment' => $this->configuration->getConfigData('environment'),
                'base_url' => ($this->configuration->getConfigData('environment'))
                    ? $this->environments['production'] : $this->environments['sandbox'],
            ],
        ]);

        return $pharmaoDelivery;
    }

    /**
     * Get PharmaoDeliveryJobInstance.
     *
     * @return string [description]
     */
    public function getJobMapUrl(): string
    {
        $base_url = ($this->configuration->getConfigData('environment'))
            ? $this->environments['production'] : $this->environments['sandbox'];

        return $base_url.'job-map/';
    }
}
