<?php

namespace Leyden\Schoology\Resources\Traits;

use Leyden\Schoology\Exceptions\BulkChangeLimitExceeded;

trait BulkReadWriteRealmTrait
{
    protected $bulk_change_limit = 50;

    protected $latest_bulk_api_response;
    
    public function bulkCreate(array $items, array $queryParams = array()){
        if(count($items) > $this->bulk_change_limit) {
            throw new BulkChangeLimitExceeded();
        }

        $endpoint = $this->buildEndpoint(null, $queryParams, true);
        $api_result = $this->api->api($endpoint, 'POST', $items);

        return $this->parseBulkResults($api_result);
    }
    
    public function bulkUpdate(array $items, array $queryParams = array()){
        if(count($items) > $this->bulk_change_limit) {
            throw new BulkChangeLimitExceeded();
        }

        $endpoint = $this->buildEndpoint(null, $queryParams);
        $api_result = $this->api->api($endpoint, 'PUT', $items);

        return $this->parseBulkResults($api_result);
    }
    
    public function bulkDelete(array $items){
        // TODO: Check if delete will exceed bulk change limit.

        $endpoint = $this->buildEndpoint();
        $api_result = $this->api->api($endpoint, 'DELETE');

        return $this->parseBulkResults($api_result);
    }

    public function parseBulkResults($rawResults) {
        $this->latest_bulk_api_response = $rawResults;
        // TODO: Response Types: Null, Array of Objects, Single Object.
        return $rawResults->result;
    }
}
