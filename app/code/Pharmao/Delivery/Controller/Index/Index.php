<?php
namespace Pharmao\Delivery\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	
	protected $_addressFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Magento\Framework\HTTP\Client\Curl $curl, 
		\Pharmao\Delivery\Model\AddressFactory $addressFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory)
	{
		$this->_pageFactory = $pageFactory;
		$this->_curl = $curl;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_addressFactory = $addressFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
        $email = $this->getRequest()->getPostValue('email');
        $model = $this->_addressFactory->create();
          $collection = $model->getCollection()->addFieldToFilter('email', trim($email));
        
        /** @var \Magento\Framework\Controller\Result\Json $result */
		$result = $this->resultJsonFactory->create();
		return $result->setData(['data' => json_encode($collection->getData())]);
	}
}
?>