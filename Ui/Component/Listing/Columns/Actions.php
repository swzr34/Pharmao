<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Ui\Component\Listing\Columns;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Pharmao\Delivery\Helper\Data;

/**
 * Class Actions.
 */
class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    protected UrlInterface $urlBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var Repository
     */
    protected Repository $_assetRepo;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * Constructor.
     *
     * @param ContextInterface         $context
     * @param UiComponentFactory       $uiComponentFactory
     * @param UrlInterface             $urlBuilder
     * @param Data                     $helper
     * @param StoreManagerInterface    $storeManager
     * @param OrderRepositoryInterface $orderRepository
     * @param Repository               $assetRepo
     * @param array                    $components
     * @param array                    $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Data $helper,
        StoreManagerInterface $storeManager,
        OrderRepositoryInterface $orderRepository,
        Repository $assetRepo,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
        $this->_assetRepo = $assetRepo;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $order = $this->orderRepository->get($item['order_id']);
                $order_view_link = $this->urlBuilder->getUrl('sales/order/view/order_id/'.$item['order_id']);
                $job_link = $this->helper->getJobMapUrl().$item['job_id'];

                $action = '';
                $action .= '<a target="_blank" href="'.$order_view_link.'"><img src="'.$this->_assetRepo->getUrl('Pharmao_Delivery::images/1.png').'" /></a>';
                $action .= '<a target="_blank" href="'.$job_link.'"><img src="'.$this->_assetRepo->getUrl('Pharmao_Delivery::images/2.png').'" /></a>';
                $item['actions'] = $action;

                $content = $this->storeManager->getWebsite($this->storeManager->getStore($item['store_id'])->getWebsiteId())->getName().' ('.$this->storeManager->getStore($item['store_id'])->getName().')';
                $item['store_id'] = html_entity_decode($content);
                $item['order_id'] = $order->getRealOrderId();
            }
        }

        return $dataSource;
    }
}
