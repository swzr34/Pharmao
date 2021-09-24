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
    
    public function __construct(Context $context, PageFactory $pageFactory, \Magento\Framework\HTTP\Client\Curl $curl, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory)
    {
        $this->resultPageFactory = $pageFactory;
        $this->_curl = $curl;
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
        $url = 'https://delivery-sandbox.pharmao.fr/v1/create-token';
        $params = array('secret' => $post['secret'], 'username' => $post['username'], 'password' => $post['password']);
        $this->_curl->post($url, $params);
        $response = $this->_curl->getBody();
        /** @var \Magento\Framework\Controller\Result\Json $result */
		$result = $this->resultJsonFactory->create();
		return $result->setData(['data' => $response]);
    }
}