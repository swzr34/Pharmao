<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Pharmao\Delivery\Helper\Data;
use Pharmao\Delivery\Helper\Service\JobService;

/**
 * Class Index.
 */
class Index extends Action
{
    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * Result constructor.
     *
     * @param Context     $context
     * @param Data        $helper
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Data $helper,
        JsonFactory $resultJsonFactory
    ) {
        $this->helper = $helper;
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
        $result = $this->resultJsonFactory->create();

        $pharmaoDeliveryJobInstance = $this->helper->getPharmaoDeliveryJobInstance();
        if ($pharmaoDeliveryJobInstance instanceof JobService && null != $pharmaoDeliveryJobInstance->getAccessToken()) {
            return $result->setData(['success' => true]);
        }

        return $result->setData(['success' => false]);
    }
}
