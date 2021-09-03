<?php

namespace Pharmao\Delivery\Api;

interface WebhookInterface
{
    /**
    * GET for Post api
    * @param string $job_id
    * @param string $status
    * @param string $order_reference_number
    * @return json
    */

    public function getPost($job_id, $status, $order_reference_number);
}