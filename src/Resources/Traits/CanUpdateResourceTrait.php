<?php

namespace Leyden\Schoology\Resources\Traits;

trait CanUpdateResourceTrait
{
    public function update($schoologyId, $item, array $queryParams = array()){
        $endpoint = $this->buildEndpoint($schoologyId, $queryParams);
        $api_result = $this->api->api($endpoint, 'PUT', $item);
        return $this->parseResults($api_result);
    }
}
