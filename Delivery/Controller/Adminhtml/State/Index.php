<?php

namespace Pharmao\Delivery\Controller\Adminhtml\State;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;


class Index extends \Magento\Backend\App\Action
{
    protected $stateFactory;

    protected $resultPageFactory;
    
    public function __construct(Context $context, PageFactory $pageFactory, 
                            \Pharmao\Delivery\Model\Delivery $deliveryModel,
                            \Pharmao\Delivery\Model\StateFactory $stateFactory, 
                            \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
                        )
    {
        $this->resultPageFactory = $pageFactory;
        $this->model = $deliveryModel;
        $this->stateFactory = $stateFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
    * The controller action
    *
    * @return \Magento\Framework\View\Result\Page
    */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        
        // Get States from order Status value
        $model = $this->stateFactory->create();
        $collection = $model->getCollection()
                        ->addFieldToFilter('status', trim($post['status']));
         $options = '<option>Select order status State</option>';
         foreach($collection->getData() as $stateData) {
            $options .= '<option value="' . $stateData['state'] . '">' . ucwords(str_replace('_', ' ', $stateData['state'])) . '</option>';             
         }
         
        /** @var \Magento\Framework\Controller\Result\Json $result */
		$result = $this->resultJsonFactory->create();
		return $result->setData(['data' => json_encode($options)]);
    }
}