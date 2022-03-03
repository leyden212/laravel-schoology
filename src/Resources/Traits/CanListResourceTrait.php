<?php

namespace Leyden\Schoology\Resources\Traits;

trait CanListResourceTrait
{
    protected $list_response_total;
    protected $list_pagination_links;

    public $paginate = false;
    public $pagination_start = 0;
    public $pagination_limit = 20;

    public function list(array $queryParams = array())
    {
        $endpoint = $this->buildEndpoint(null, $queryParams, true, $this->paginate);
        $api_result = $this->api->apiResult($endpoint, 'GET');
        return $this->parseResults($api_result);
    }

    public function listAll(array $queryParams = array())
    {
        $queryParams = array_merge($queryParams, ['start' => 0, 'limit' => self::MAX_PAGE_LIMIT]);
        $endpoint = $this->buildEndpoint(null, $queryParams, true);
        $api_result = $this->api->api($endpoint, 'GET');

        $items = $this->parseRawResults($api_result);
        if ($api_result->result->total <= self::MAX_PAGE_LIMIT || !property_exists($api_result->result, 'links')) {
            return $items;
        }

        for ($counter = 0; $counter < 100; $counter++) {
            if (!property_exists($api_result->result->links, 'next')) {
                return $items;
            }
            $queryParams['start'] += self::MAX_PAGE_LIMIT;
            $endpoint = $this->buildEndpoint(null, $queryParams, true);
            $api_result = $this->api->api($endpoint, 'GET');
            $items = $items->merge($this->parseResults($api_result));
        }

        return $items;
    }


    public function paginate($start, $count)
    {
        return $this->setPagination($start, $count)
            ->list();
    }

    public function setPagination($start, $limit)
    {
        $this->paginate = true;
        $this->pagination_start = $start;
        $this->pagination_limit = $limit;
        return $this;
    }

    public function unsetPagination($start, $limit)
    {
        $this->paginate = false;
        $this->pagination_start = 0;
        $this->pagination_limit = 20;
        return $this;
    }
}
