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
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->model = $deliveryModel;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
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
            $pharmaoDeliveryJobInstance = $this->helper->getPharmaoDeliveryJobInstance();
            foreach ($dataSource['data']['items'] as & $item) { 
                $order_view_link = $this->urlBuilder->getUrl('sales/order/view/order_id/' . $item['order_id']);
                $job_link =  $this->helper->getJobMapUrl() . $item['job_id'];
                $item['actions'] = '<a target="_blank" href="'.$order_view_link.'"><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"width="20" height="20"viewBox="0 0 172 172"style=" fill:#000000;"><g fill="none" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><path d="M0,172v-172h172v172z" fill="none"></path><g fill="#3498db"><path d="M74.53333,17.2c-31.59642,0 -57.33333,25.73692 -57.33333,57.33333c0,31.59642 25.73692,57.33333 57.33333,57.33333c13.73998,0 26.35834,-4.87915 36.24766,-12.97839l34.23203,34.23203c1.43802,1.49778 3.5734,2.10113 5.5826,1.57735c2.0092,-0.52378 3.57826,-2.09284 4.10204,-4.10204c0.52378,-2.0092 -0.07957,-4.14458 -1.57735,-5.5826l-34.23203,-34.23203c8.09924,-9.88932 12.97839,-22.50768 12.97839,-36.24766c0,-31.59642 -25.73692,-57.33333 -57.33333,-57.33333zM74.53333,28.66667c25.39937,0 45.86667,20.4673 45.86667,45.86667c0,25.39937 -20.46729,45.86667 -45.86667,45.86667c-25.39937,0 -45.86667,-20.46729 -45.86667,-45.86667c0,-25.39937 20.4673,-45.86667 45.86667,-45.86667zM74.44375,45.78828c-3.16203,0.04943 -5.68705,2.6496 -5.64375,5.81172v17.2h-17.2c-2.06765,-0.02924 -3.99087,1.05709 -5.03322,2.843c-1.04236,1.78592 -1.04236,3.99474 0,5.78066c1.04236,1.78592 2.96558,2.87225 5.03322,2.843h17.2v17.2c-0.02924,2.06765 1.05709,3.99087 2.843,5.03322c1.78592,1.04236 3.99474,1.04236 5.78066,0c1.78592,-1.04236 2.87225,-2.96558 2.843,-5.03322v-17.2h17.2c2.06765,0.02924 3.99087,-1.05709 5.03322,-2.843c1.04236,-1.78592 1.04236,-3.99474 0,-5.78066c-1.04236,-1.78592 -2.96558,-2.87225 -5.03322,-2.843h-17.2v-17.2c0.02122,-1.54972 -0.58581,-3.04203 -1.68279,-4.1369c-1.09698,-1.09487 -2.59045,-1.69903 -4.14013,-1.67482z"></path></g></g></svg></a><br /><a target="_blank" href="'.$job_link.'"><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"width="20" height="20"viewBox="0 0 172 172"style=" fill:#000000;"><g fill="none" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><path d="M0,172v-172h172v172z" fill="none"></path><g fill="#3498db"><path d="M86,14.40332c-27.63467,0 -50.11068,22.49001 -50.11068,50.12467c0,32.12817 42.76138,84.31774 44.58171,86.5179l5.52897,6.70475l5.52897,-6.70475c1.82033,-2.20733 44.58171,-54.38974 44.58171,-86.5179c-0.00001,-27.64183 -22.47601,-50.12467 -50.11068,-50.12467zM86,28.73665c19.72983,0 35.77735,16.06151 35.77735,35.79134c-0.00001,19.38583 -22.31835,52.72707 -35.77735,70.3929c-13.459,-17.6515 -35.77734,-50.97841 -35.77734,-70.3929c0,-19.72983 16.04751,-35.79134 35.77734,-35.79134zM86,46.58333c-9.89717,0 -17.91667,8.0195 -17.91667,17.91667c0,9.89717 8.0195,17.91667 17.91667,17.91667c9.89717,0 17.91667,-8.0195 17.91667,-17.91667c0,-9.89717 -8.0195,-17.91667 -17.91667,-17.91667z"></path></g></g></svg></a>';
                $var = '<br />';
                $content = '';
                $content .= $this->storeManager->getWebsite($this->storeManager->getStore($item['store_id'])->getWebsiteId())->getName() . " (" . $this->storeManager->getStore($item['store_id'])->getName() . ")";
                $item['store_id'] = html_entity_decode($content);
            }
        }

        return $dataSource;
    }
}