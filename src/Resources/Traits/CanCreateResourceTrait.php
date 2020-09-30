<?php

namespace Leyden\Schoology\Resources\Traits;

trait CanCreateResourceTrait
{
    public function create($item, array $queryParams = array()){
        $endpoint = $this->buildEndpoint(null, $queryParams, true);
        $api_result = $this->api->api($endpoint, 'POST', $item);
        return $this->parseResults($api_result);
    }
}
