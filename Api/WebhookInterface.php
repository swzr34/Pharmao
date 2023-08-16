<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Api;

/**
 * @Api
 */
interface WebhookInterface
{
    /**
     * @api
     *
     * @param mixed $data
     *
     * @return bool
     */
    public function getPost(mixed $data): bool;
}
