<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Model;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Configuration.
 */
class Configuration
{
    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @param ScopeConfigInterface  $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string   $key
     * @param string   $path
     * @param null|int $storeId
     *
     * @return mixed
     */
    public function getConfigData(string $key, string $path = 'general', ?int $storeId = null): mixed
    {
        if (null === $storeId) {
            $storeId = $this->populateStoreId();
        }

        return $this->scopeConfig->getValue(
            'delivery_configuration/'.$path.'/'.$key,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     *
     * @return mixed
     */
    public function getWeightUnit(?int $storeId = null): mixed
    {
        if (null === $storeId) {
            $storeId = $this->populateStoreId();
        }

        return $this->scopeConfig->getValue('general/locale/weight_unit', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null|int $storeId
     *
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool
    {
        if (null === $storeId) {
            $storeId = $this->populateStoreId();
        }

        return $this->scopeConfig->isSetFlag(
            'delivery_configuration/general/enable',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return int
     */
    protected function populateStoreId(): int
    {
        try {
            return intval($this->storeManager->getStore()->getId());
        } catch (Exception) {
            return Store::DEFAULT_STORE_ID;
        }
    }
}
