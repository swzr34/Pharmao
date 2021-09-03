<?php

namespace Pharmao\Delivery\Model\Api;

use Psr\Log\LoggerInterface;

class Webhook
{
    protected $logger;

    public function __construct(
        LoggerInterface $logger
    )
    {

        $this->logger = $logger;
    }

    /**
    * @inheritdoc
    */

    public function getPost($job_id, $status, $order_reference_number)
    {
    $response = ['success' => false];

        try {
            $response = ['success' => true, 'data' => ['job_id' => $job_id, 'status' => $status, 'order_reference_number' => $order_reference_number]];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
            $this->logger->info($e->getMessage());
        }
        $returnArray = json_encode($response);
        return $returnArray;
    }
}