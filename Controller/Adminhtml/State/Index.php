<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Controller\Adminhtml\State;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Pharmao\Delivery\Model\ResourceModel\State\CollectionFactory as StateCollectionFactory;

/**
 * Class Index.
 */
class Index extends Action
{
    /**
     * @var StateCollectionFactory
     */
    protected StateCollectionFactory $stateCollectionFactory;

    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @param Context                $context
     * @param PageFactory            $pageFactory
     * @param StateCollectionFactory $stateCollectionFactory
     * @param JsonFactory            $resultJsonFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        StateCollectionFactory $stateCollectionFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->resultPageFactory = $pageFactory;
        $this->stateCollectionFactory = $stateCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * The controller action.
     *
     * @return Json
     */
    public function execute(): Json
    {
        $post = $this->getRequest()->getPostValue();

        // Get States from order Status value
        $collection = $this->stateCollectionFactory->create();
        $collection->addFieldToFilter('status', trim($post['status']));
        $options = '<option>Select order status State</option>';
        foreach ($collection->getData() as $stateData) {
            $options .= '<option value="'.$stateData['state'].'">'.ucwords(str_replace('_', ' ', $stateData['state'])).'</option>';
        }

        $result = $this->resultJsonFactory->create();

        return $result->setData(['data' => json_encode($options)]);
    }
}
