<?php

namespace Pharmao\Delivery\Api;

interface WebhookInterface
{
    /**
     * @api
     * @param mixed $data
     * @return array
     */
    

    public function getPost($data);
}