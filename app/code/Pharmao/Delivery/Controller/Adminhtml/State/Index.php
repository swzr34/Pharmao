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
         $model = $this->stateFactory->create();
        $collection = $model->getCollection()
                        ->addFieldToFilter('status', trim($post['status']));
        //  echo "<pre>"; print_r($collection->getData());
         $options = '<option>Select order status State</option>';
        //  return $options;
//         $url = $this->model->getBaseUrl('/create-token');
//         $params = array('secret' => $post['secret'], 'username' => $post['username'], 'password' => $post['password']);
//         $response = $this->helper->performPost($url, $params);
//         /** @var \Magento\Framework\Controller\Result\Json $result */
		$result = $this->resultJsonFactory->create();
// 		// Generate Log File
//         	$logData = array(
//         	    'secret' => $post['secret'], 'username' => $post['username'], 'password' => $post['password'],
//                             'validate' => print_r($response, true)
//                     );
//             $this->helper->generateLog('validate', $logData);
		return $result->setData(['data' => json_encode($options)]);
    }
}