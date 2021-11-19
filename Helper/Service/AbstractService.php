<?php

namespace Pharmao\Delivery\Helper\Service;

use Pharmao\Delivery\Model\Delivery;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Directory\Model\CountryFactory;

abstract class AbstractService
{
    /**
     * Curl Client
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curlClient;

    /**
     * Config Model
     * @var null|\Pharmao\Delivery\Model\Delivery
     */
    protected $config = null;

    /**
     * Access Token
     * @var string
     */
    protected $accessToken = '';

    /**
     * Version
     * @var string
     */
    protected $version = 'v1';

    /**
     * $baseUrl
     * @var string
     */
    protected $baseUrl = '';


    /**
     * Curl Cleint
     * @param array $params
     */
    public function __construct($params)
    {
        $secret = $params['secret'];
        $username = $params['username'];
        $password = $params['password'];
        $this->baseUrl = $params['base_url'] . $this->version;

        $this->_curlClient = new Curl();
        $this->config = $params['config'];
        $this->country = $params['country'];
        
        if (empty($this->accessToken) && $this->checkDomain()) {
            $data = [
                'secret' => $secret,
                'username' => $username,
                'password' => $password,
            ];
            
            $tokenResponse = $this->post('/create-token', $data);
            
            if (isset($tokenResponse->access_token)) {
                $this->accessToken = $tokenResponse->access_token;
            }
        }
    }

    /**
     * Get Access Token
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Build Url
     * @param  string $endpoint
     * @return string
     */
    public function buildUrl($endpoint)
    {
        return $this->baseUrl . $endpoint;
    }

    /**
     * Gets Default Headers
     * @return array
     */
    public function getDefaultHeaders()
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => sprintf('Bearer %s', $this->accessToken),
        ];
    }

    /**
     * Set Headers
     * @param self
     */
    public function setHeaders($data = [])
    {
        $headers = $this->getDefaultHeaders();

        foreach ($data as $key => $val) {
            $headers[$key] = $val;
        }

        $this->_curlClient->setHeaders($headers);

        return $this;
    }

    /**
     * Sets Options
     * @param array $options
     */
    public function setOptions($options = [])
    {
        foreach ($options as $key => $val) {
            $this->_curlClient->setOption($key, $val);
        }

        return $this;
    }

    /**
     * Gets Response Body
     * @return mixed
     */
    public function getResponseBody()
    {
        $response = $this->_curlClient->getBody();

        return json_decode($response);
    }

    /**
     * Perform Post
     * @param  string $endpont
     * @param  array $data
     * @return mixed
     */
    public function post($endpoint, $data)
    {
        $this->setHeaders([]);
        $this->_curlClient->post($this->buildUrl($endpoint), json_encode($data));

        return $this->getResponseBody();
    }

    /**
     * Perform Get
     * @param  string $endpont
     * @return mixed
     */
    public function get($endpont)
    {
        $this->setHeaders([]);

        $this->_curlClient->get($this->buildUrl($endpoint));

        return $this->getResponseBody();
    }

    /**
     * Get Country Name
     * @return string
     */
    public function getCountryName()
    {
        //$countryFactory = new CountryFactory();

        $country = $this->country->create()->loadByCode($this->config->getConfigData('pharmaocountry'));

        return $country->getName();
    }

    /**
     * Build Job Data
     * @param  array $data
     * @return array
     */
    public function buildJobData($data)
    {
        $job = [
            'job' => [
                'client_type' => 'magento',
                'is_external' => 1,
                'external_order_amount' => isset($data['order_amount']) ? $data['order_amount'] : '',
                'assignment_code' => isset($data['assignment_code']) ? $data['assignment_code'] : '',
                'external_order_reference' => isset($data['order_id']) ? $data['order_id'] : '',
                'transport_type' => 'Bike',
                'package_type' => 'small',
                'package_description' => '',
                'is_within_one_hour' => isset($data['is_within_one_hour']) ? $data['is_within_one_hour'] : '',
                'pickups' => [
                    [
                        'comment' => sprintf(
                            'Rentrez dans la pharmacie, allez au comptoir et demander la commande Pharmao Nom: %s %s',
                            isset($data['customer_firstname']) ? $data['customer_firstname'] : '',
                            isset($data['customer_lastname']) ? $data['customer_lastname'] : ''
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
                            "company" => $this->config->getConfigData('company_name', 'global_settings'),
                        ],
                    ],
                ],
                'dropoffs' => [
                    [
                        'comment' => isset($data['customer_comment']) ? $data['customer_comment'] : '',
                        'address' => $data['customer_address'],
                        'contact' =>  [
                            'firstname' => isset($data['customer_firstname']) ? $data['customer_firstname'] : '',
                            'lastname' => isset($data['customer_lastname']) ? $data['customer_lastname'] : '',
                            'phone' => isset($data['customer_phone']) ? $data['customer_phone'] : '',
                            'email' => isset($data['customer_email']) ? $data['customer_email'] : '',
                        ],
                    ],
                ],
            ],
        ];

        return $job;
    }

    /**
     * Check Domain
     * @return boolean
     */
    public function checkDomain()
    {
        $baseUrl = str_replace('/v1', '', $this->baseUrl);
        $result = false;
        $url = filter_var($baseUrl, FILTER_VALIDATE_URL);

        /* Open curl connection */
        $handle = curl_init($url);

        /* Set curl parameter */
        curl_setopt_array($handle, array(
            CURLOPT_FOLLOWLOCATION => TRUE,     // we need the last redirected url
            CURLOPT_NOBODY => TRUE,             // we don't need body
            CURLOPT_HEADER => FALSE,            // we don't need headers
            CURLOPT_RETURNTRANSFER => FALSE,    // we don't need return transfer
            CURLOPT_SSL_VERIFYHOST => FALSE,    // we don't need verify host
            CURLOPT_SSL_VERIFYPEER => FALSE     // we don't need verify peer
        ));

        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);

        //$httpCode = curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);  // Try to get the last url
        $httpCode = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);      // Get http status from last url

        /* Close curl connection */
        curl_close($handle);

        return (200 === $httpCode);
    }
}
