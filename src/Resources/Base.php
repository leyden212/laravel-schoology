<?php
namespace Leyden\Schoology\Resources;

use Leyden\Schoology\Exceptions\MethodNotImplemented;
use Illuminate\Support\Str;
use Leyden\Schoology\API\SchoologyApi;

abstract class Base
{
    use Traits\CanFilterResourceTrait;

    protected $model;

    protected $base_path;
    protected $nested_path;
    protected $path_parents;

    protected $api;
    protected $latest_api_response;

    protected $auth_mode = 'two_legged';
    protected $domain;
    private $consumer_key;
    private $consumer_secret;
    private $token_key = '';
    private $token_secret = '';

    const MAX_PAGE_LIMIT = 200;

    public function __construct($model)
    {
        $this->setAuthMode(config('schoology.auth.default_mode', 'two_legged'))
            ->setModel($model);
    }

    public function getBasePath()
    {
        return $this->model->getBasePath();
    }

    public function getNestedPath()
    {
        return $this->model->getNestedPath();
    }

    public function getModelClass()
    {
        return get_class($this->model);
    }

    public function getApi()
    {
        return $this->api;
    }

    public function buildEndpoint($id = null, array $queryParams = [], bool $useNestedPath = false, bool $usePagination = false)
    {
        $endpoint = $useNestedPath ? $this->getNestedPath() : $this->getBasePath();
        if ($id) {
            $endpoint .= '/' . $id;
        }

        if ($this->wheres) {
            $queryParams = array_merge($queryParams, $this->wheres);
        }

        if ($usePagination) {
            $queryParams = array_merge($queryParams, ['start' => $this->pagination_start, 'limit' => $this->pagination_limit]);
        }

        if ($queryParams) {
            $endpoint .= '?' . http_build_query($queryParams);
        }

        return $endpoint;
    }

    protected function setAuthMode($mode)
    {
        $this->auth_mode = $mode;
        $this->setupApi();
        return $this;
    }

    protected function setModel($model)
    {
        if (!$model) {
            $this->clearModel();
        }

        $this->model = $model;

        return $this;
    }

    protected function clearModel()
    {
        $this->model = null;
        return $this;
    }

    protected function setupApi()
    {
        $authBaseConfig = 'schoology.auth.' . $this->auth_mode;

        $this->domain = config($authBaseConfig . '.domain');
        $this->consumer_key = config($authBaseConfig . '.consumer_key');
        $this->consumer_secret = config($authBaseConfig . '.consumer_secret');

        $this->api = strcasecmp($this->auth_mode, 'two_legged') === 0
            ? new SchoologyApi($this->consumer_key, $this->consumer_secret, $this->domain, '', '', TRUE)
            : new SchoologyApi($this->consumer_key, $this->consumer_secret);

        return $this;
    }

    public function createModel($object)
    {
        if (!$this->model) {
            return $object;
        }
        $class = $this->getModelClass();

        return (new $class((array)$object));
    }

    public function parseRawResults($rawResults)
    {
        $this->latest_api_response = $rawResults;
        $this->resetWheres();

        if (!$rawResults) {
            return null;
        }

        if (property_exists($rawResults, 'result')) {
            return $this->parseResults($rawResults->result, true);
        }

        return null;
    }

    public function parseResults($results, $wasRaw = false)
    {
        if (!$wasRaw) {
            $this->latest_api_response = $results;
            $this->resetWheres();
        }

        if (!$results) {
            return null;
        }

        $this->list_response_total = property_exists($results, 'total')
            ? $results->total
            : null;

        $this->list_pagination_links = property_exists($results, 'links')
            ? $results->links
            : null;

        $resource_name = $this->model->getResourceName();
        $resources = $results;
        // Handle Nested Attributes
        foreach (explode('.', $resource_name) as $prop) {
            if (!property_exists($resources, $prop)) {
                $resources = null;
                break;
            }
            $resources = $resources->$prop;
        }
        if ($resources !== null) { //property_exists($rawResults->result, $resource_name)) {
            $data = is_array((array)$resources) //is_array((array)$rawResults->result->$resource_name)
                 ? (array)$resources // (array)$rawResults->result->$resource_name
                 : [];
            $models = array_map([$this, 'createModel'], $data);

            return new \Illuminate\Support\Collection($models);
        }

        if (is_object($results)) {
            return $this->createModel($results);
        }

        return $results;
    }

    /**
     * 
     */
    public function find($id)
    {
        return $this->view($id);
    }

    public function findExt($id)
    {
        return $this->view('ext/' . $id);
    }

    public function get()
    {
        return $this->list();
    }


    public function first()
    {
        return $this->paginate(0, 1)->first();
    }

    public function all()
    {
        return $this->listAll();
    }

    public function list(array $queryParams = array())
    {
        throw new MethodNotImplemented();
    }

    public function listAll(array $queryParams = array())
    {
        throw new MethodNotImplemented();
    }

    public function view($schoologyId, array $queryParams = array())
    {
        throw new MethodNotImplemented();
    }

    public function create($item, array $queryParams = array())
    {
        throw new MethodNotImplemented();
    }

    public function update($schoologyId, $item, array $queryParams = array())
    {
        throw new MethodNotImplemented();
    }

    public function delete($schoologyId, array $queryParams = array())
    {
        throw new MethodNotImplemented();
    }

    public function bulkCreate(array $items, array $queryParams = array())
    {
        throw new MethodNotImplemented();
    }

    public function bulkCopy(array $items, array $queryParams = array())
    {
        throw new MethodNotImplemented();
    }

    public function bulkUpdate(array $items, array $queryParams = array())
    {
        throw new MethodNotImplemented();
    }

    public function bulkDelete(array $items)
    {
        throw new MethodNotImplemented();
    }
}