<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Pharmao\Delivery\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;

/**
 * Class Index.
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected PageFactory $pageFactory;

    /**
     * @var AddressCollectionFactory
     */
    protected AddressCollectionFactory $addressCollectionFactory;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @param Context                  $context
     * @param PageFactory              $pageFactory
     * @param AddressCollectionFactory $addressCollectionFactory
     * @param JsonFactory              $resultJsonFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        AddressCollectionFactory $addressCollectionFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->pageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->addressCollectionFactory = $addressCollectionFactory;

        return parent::__construct($context);
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $email = $this->getRequest()->getPostValue('email');
        $collection = $this->addressCollectionFactory->create();
        $collection->addFieldToFilter('email', trim($email));

        $result = $this->resultJsonFactory->create();

        return $result->setData(['data' => json_encode($collection->getData())]);
    }
}
