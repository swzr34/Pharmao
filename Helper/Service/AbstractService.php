<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Helper\Service;

use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\ResourceModel\Country as CountryResource;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Pharmao\Delivery\Model\Configuration;

/**
 * Class AbstractService.
 */
abstract class AbstractService
{
    /**
     * Curl Client.
     *
     * @var Curl
     */
    protected Curl $curlClient;

    /**
     * Config Model.
     *
     * @var Configuration
     */
    protected Configuration $config;

    /**
     * Access Token.
     *
     * @var string
     */
    protected string $accessToken = '';

    /**
     * Version.
     *
     * @var string
     */
    protected string $version = 'v1';

    /**
     * $baseUrl.
     *
     * @var string
     */
    protected string $baseUrl = '';

    /**
     * @var CountryFactory
     */
    protected CountryFactory $countryFactory;

    /**
     * @var CountryResource
     */
    protected CountryResource $countryResource;

    /**
     * @param CurlFactory $curlFactory
     * @param array       $params
     */
    public function __construct(
        CurlFactory $curlFactory,
        array $params
    ) {
        $secret = $params['secret'];
        $username = $params['username'];
        $password = $params['password'];
        $this->baseUrl = $params['base_url'].$this->version;

        $this->curlClient = $curlFactory->create();
        $this->config = $params['config'];
        $this->countryFactory = $params['country_factory'];
        $this->countryResource = $params['country_resource'];

        if (empty($this->accessToken) && $this->checkDomain()) {
            $data = [
                'secret' => $secret,
                'username' => $username,
                'password' => $password,
            ];

            $tokenResponse = $this->post('/create-token', $data);
            if (isset($tokenResponse['access_token'])) {
                $this->accessToken = $tokenResponse['access_token'];
            }
        }
    }

    /**
     * Get Access Token.
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * Build Url.
     *
     * @param string $endpoint
     *
     * @return string
     */
    public function buildUrl(string $endpoint): string
    {
        return $this->baseUrl.$endpoint;
    }

    /**
     * Gets Default Headers.
     *
     * @return array
     */
    public function getDefaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => sprintf('Bearer %s', $this->accessToken),
        ];
    }

    /**
     * Set Headers.
     *
     * @param array $data
     *
     * @return AbstractService
     */
    public function setHeaders(array $data = []): static
    {
        $headers = $this->getDefaultHeaders();

        foreach ($data as $key => $val) {
            $headers[$key] = $val;
        }

        $this->curlClient->setHeaders($headers);

        return $this;
    }

    /**
     * Sets Options.
     *
     * @param array $options
     *
     * @return AbstractService
     */
    public function setOptions(array $options = []): static
    {
        foreach ($options as $key => $val) {
            $this->curlClient->setOption($key, $val);
        }

        return $this;
    }

    /**
     * @return array
     *
     * @throws \JsonException
     * @throws \Zend_Log_Exception
     */
    public function getResponseBody(): array
    {
        $response = $this->curlClient->getBody();

        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Perform post.
     *
     * @param string $endpoint
     * @param array  $data
     *
     * @return array
     *
     * @throws \JsonException
     * @throws \Zend_Log_Exception
     */
    public function post(string $endpoint, array $data): array
    {
        $this->setHeaders([]);
        $this->curlClient->post($this->buildUrl($endpoint), json_encode($data));

        return $this->getResponseBody();
    }

    /**
     * Perform get.
     *
     * @param string $endpoint
     *
     * @return array
     *
     * @throws \JsonException
     * @throws \Zend_Log_Exception
     */
    public function get(string $endpoint): array
    {
        $this->setHeaders([]);

        $this->curlClient->get($this->buildUrl($endpoint));

        return $this->getResponseBody();
    }

    /**
     * Get Country Name.
     *
     * @return string
     */
    public function getCountryName(): string
    {
        $country = $this->countryFactory->create();
        $this->countryResource->loadByCode($country, $this->config->getConfigData('pharmaocountry'));

        return $country->getName();
    }

    /**
     * Build Job Data.
     *
     * @param array $data
     *
     * @return array
     */
    public function buildJobData(array $data): array
    {
        $job = [
            'job' => [
                'client_type' => 'magento',
                'is_external' => 1,
                'external_order_amount' => $data['order_amount'] ?? '',
                'assignment_code' => $data['assignment_code'] ?? '',
                'external_order_reference' => $data['order_id'] ?? '',
                'transport_type' => 'Bike',
                'package_type' => 'small',
                'package_description' => '',
                'is_within_one_hour' => $data['is_within_one_hour'] ?? '',
                'pickups' => [
                    [
                        'comment' => sprintf(
                            'Rentrez dans la pharmacie, allez au comptoir et demander la commande Pharmao Nom: %s %s',
                            $data['customer_firstname'] ?? '',
                            $data['customer_lastname'] ?? ''
                        ),
                        'address' => sprintf(
                            '%s, %s %s, %s',
                            $this->config->getConfigData('address', 'global_settings'),
                            $this->config->getConfigData('postcode', 'global_settings'),
                            $this->config->getConfigData('city', 'global_settings'),
                            $this->getCountryName()
                        ),
                        'contact' => [
                            'firstname' => $this->config->getConfigData('firstname', 'global_settings'),
                            'phone' => $this->config->getConfigData('phone', 'global_settings'),
                            'email' => $this->config->getConfigData('username', 'global_settings'),
                            'company' => $this->config->getConfigData('company_name', 'global_settings'),
                        ],
                    ],
                ],
                'dropoffs' => [
                    [
                        'comment' => $data['customer_comment'] ?? '',
                        'address' => $data['customer_address'],
                        'contact' => [
                            'firstname' => $data['customer_firstname'] ?? '',
                            'lastname' => $data['customer_lastname'] ?? '',
                            'phone' => $data['customer_phone'] ?? '',
                            'email' => $data['customer_email'] ?? '',
                        ],
                    ],
                ],
            ],
        ];

        return $job;
    }

    /**
     * Check Domain.
     *
     * @return bool
     */
    public function checkDomain(): bool
    {
        $baseUrl = str_replace('/v1', '', $this->baseUrl);
        $result = false;
        $url = filter_var($baseUrl, FILTER_VALIDATE_URL);

        /* Open curl connection */
        $handle = curl_init($url);

        /* Set curl parameter */
        curl_setopt_array($handle, [
            CURLOPT_FOLLOWLOCATION => true,     // we need the last redirected url
            CURLOPT_NOBODY => true,             // we don't need body
            CURLOPT_HEADER => false,            // we don't need headers
            CURLOPT_RETURNTRANSFER => false,    // we don't need return transfer
            CURLOPT_SSL_VERIFYHOST => false,    // we don't need verify host
            CURLOPT_SSL_VERIFYPEER => false,     // we don't need verify peer
        ]);

        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);

        // $httpCode = curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);  // Try to get the last url
        $httpCode = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);      // Get http status from last url

        /* Close curl connection */
        curl_close($handle);

        return 200 === $httpCode;
    }
}
