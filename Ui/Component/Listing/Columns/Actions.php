<?php
namespace Pharmao\Delivery\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    
    /**
     * @var Order
     */
    private $order;
    
    protected $_assetRepo;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Pharmao\Delivery\Model\Delivery $deliveryModel,
        \Pharmao\Delivery\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->model = $deliveryModel;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->order = $order;
        $this->_assetRepo = $assetRepo;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            if ($this->helper->checkDomain()) {
                $pharmaoDeliveryJobInstance = $this->helper->getPharmaoDeliveryJobInstance();
                foreach ($dataSource['data']['items'] as & $item) {
                    $order = $this->order->load($item['order_id']);
                    $order_view_link = $this->urlBuilder->getUrl('sales/order/view/order_id/' . $item['order_id']);
                    $this->model->setStoreId($item['store_id']);
                    $job_link =  $this->helper->getJobMapUrl() . $item['job_id'];

                    $action = '';
                    $action .= '<a target="_blank" href="' . $order_view_link . '"><img src="' . $this->_assetRepo->getUrl("Pharmao_Delivery::images/1.png") . '" /></a>';
                    $action .= '<a target="_blank" href="' . $job_link . '"><img src="' . $this->_assetRepo->getUrl("Pharmao_Delivery::images/2.png") . '" /></a>';
                    $item['actions'] = $action;

                    $content = '';
                    $content .= $this->storeManager->getWebsite($this->storeManager->getStore($item['store_id'])->getWebsiteId())->getName() . " (" . $this->storeManager->getStore($item['store_id'])->getName() . ")";
                    $item['store_id'] = html_entity_decode($content);
                    $item['order_id'] = $order->getRealOrderId();
                }
            }
        }

        return $dataSource;
    }
}
