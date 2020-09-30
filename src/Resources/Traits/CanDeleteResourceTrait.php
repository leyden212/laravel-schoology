<?php

namespace Leyden\Schoology\Resources\Traits;

trait CanDeleteResourceTrait
{
    public function delete($schoologyId, array $queryParams = array()){
        $endpoint = $this->buildEndpoint($schoologyId, $queryParams);
        $api_result = $this->api->api($endpoint, 'DELETE');
        return $this->parseResults($api_result);
    }
}
