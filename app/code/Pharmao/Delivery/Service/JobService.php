<?php

namespace Pharmao\Delivery\Service;

use \Pharmao\Delivery\Service\AbstractService;

class JobService extends AbstractService
{
    /**
     * Validate Job
     * @param  array $data
     * @return array
     */
    public function validateJob($data)
    {
        $body = $this->post('/job/validate', $params);

        return $body;
    }

    /**
     * Create Job
     * @param  array $data
     * @return mixed
     */
    public function createJob($data)
    {
        $params = $this->buildJobData($data);

        $body = $this->post('/jobs', $params);

        return $body;
    }

    /**
     * Validate and Create Job
     * @param  array $data
     * @return mixed
     */
    public function validateAndCreateJob($data)
    {
        $params = $this->buildJobData($data);

        $validationResponse = $this->validateJob($params);

        if (
            $validationResponse
            && isset($validationResponse->code)
            && 200 == $validationResponse
            && $validationResponse->data->is_valid
        ) {
            return $this->post('/jobs', $params);
        }


        return false;
    }
}
