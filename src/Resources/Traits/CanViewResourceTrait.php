<?php

namespace Leyden\Schoology\Resources\Traits;

trait CanViewResourceTrait
{
    public function view($schoologyId, array $queryParams = array())
    {
        $endpoint = $this->buildEndpoint($schoologyId, $queryParams);
        $api_result = $this->api->apiResult($endpoint, 'GET');
        return $this->parseResults($api_result);
    }
}
