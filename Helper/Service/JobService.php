<?php

declare(strict_types=1);

namespace Pharmao\Delivery\Helper\Service;

use Exception;

/**
 * Class JobService.
 */
class JobService extends AbstractService
{
    /**
     * Validate Job.
     *
     * @param array $data
     *
     * @return array
     *
     * @throws \JsonException
     * @throws \Zend_Log_Exception
     */
    public function validateJob(array $data): array
    {
        if (!$this->checkDomain()) {
            return [];
        }

        return $this->post('/job/validate', $data);
    }

    /**
     * Create Job.
     *
     * @param array $data
     *
     * @return array
     *
     * @throws \JsonException
     * @throws \Zend_Log_Exception
     */
    public function createJob(array $data): array
    {
        if (!$this->checkDomain()) {
            return [];
        }

        $params = $this->buildJobData($data);

        return $this->post('/jobs', $params);
    }

    /**
     * Validate and Create Job.
     *
     * @param array $data
     *
     * @return array
     *
     * @throws \JsonException
     * @throws \Zend_Log_Exception
     */
    public function validateAndCreateJob(array $data): array
    {
        $params = $this->buildJobData($data);

        $validationResponse = $this->validateJob($params);

        if ($validationResponse
            && isset($validationResponse['code'])
            && 200 == $validationResponse['code']
            && isset($validationResponse['data']['is_valid'])
            && $validationResponse['data']['is_valid']
        ) {
            return $this->post('/jobs', $params);
        }

        return [];
    }

    /**
     * Get Price.
     *
     * @param array $data
     *
     * @return array
     *
     * @throws \JsonException
     * @throws \Zend_Log_Exception
     */
    public function getPrice(array $data): array
    {
        if (!$this->checkDomain()) {
            return [];
        }

        $params = $this->buildJobData($data);

        return $this->post('/job/price', $params);
    }
}
