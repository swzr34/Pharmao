<?php

namespace Pharmao\Delivery\Controller\Adminhtml\Index;


use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;


class Index extends \Magento\Backend\App\Action
{

    /**
    * @var PageFactory
    */
    protected $resultPageFactory;


    /**
    * Result constructor.
    * @param Context $context
    * @param PageFactory $pageFactory
    */
    
    public function __construct(Context $context, PageFactory $pageFactory, 
                            \Magento\Framework\HTTP\Client\Curl $curl,
                            \Pharmao\Delivery\Model\Delivery $deliveryModel,
                            \Pharmao\Delivery\Helper\Data $helper,
                            \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
                        )
    {
        $this->resultPageFactory = $pageFactory;
        $this->_curl = $curl;
        $this->model = $deliveryModel;
        $this->helper = $helper;
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
        $url = $this->model->getBaseUrl('/create-token');
        $params = array('secret' => $post['secret'], 'username' => $post['username'], 'password' => $post['password']);
        $response = $this->helper->performPost($url, $params);
        /** @var \Magento\Framework\Controller\Result\Json $result */
		$result = $this->resultJsonFactory->create();
		// Generate Log File
        	$logData = array(
        	    'secret' => $post['secret'], 'username' => $post['username'], 'password' => $post['password'],
                            'validate' => print_r($response, true)
                    );
            $this->helper->generateLog('validate', $logData);
		return $result->setData(['data' => json_encode($response)]);
    }
}