<?php

namespace Leyden\Schoology;

use Exception;
use Leyden\Schoology\API\SchoologyApi;
use Illuminate\Support\Str;

class Schoology {

    protected $api;
    protected $auth_mode;
	protected $domain;
	private $consumer_key;
    private $consumer_secret;
    private $token_key = '';
    private $token_secret = '';

    public function __construct(){
        $this->setAuthMode(config('schoology.auth.default_mode','two_legged'));
    }
    
    /**
     * Schoology First-Party Package setup.
     */
    protected function setupApi(){
        $authBaseConfig = 'schoology.auth.'.$this->auth_mode;

        $this->domain = config($authBaseConfig.'.domain');
        $this->consumer_key = config($authBaseConfig.'.consumer_key');
        $this->consumer_secret = config($authBaseConfig.'.consumer_secret');

        $this->api = strcasecmp($this->auth_mode, 'two_legged') === 0
            ? new SchoologyApi($this->consumer_key, $this->consumer_secret, $this->domain, '','', TRUE)
            : new SchoologyApi($this->consumer_key, $this->consumer_secret);

        return $this;
    }

    public function api() {
        return $this->api;
    }

    /**
     * Schoology Authentication Helpers
     */
    public function setAuthMode($mode) {
        $this->auth_mode = $mode;
        $this->setupApi();
        return $this;
    }

    public function validateLogin(){
        return $this->api->validateLogin();
    }

    protected function loadResource($realm){
        $realm = '\\Leyden\\Schoology\\Models\\'.$realm;
        return new $realm();
    }

    /**
     * Schoology Realms
     */
	public function schools() {
        return $this->loadResource('School');
    }
    
	public function buildings() {
        return $this->loadResource('Building');
    }

	public function users() {
        return $this->loadResource('User');
    }

	public function groups() {
        return $this->loadResource('Group');
    }

	public function courses() {
        return $this->loadResource('Course');
    }
    
	public function sections() {
        return $this->loadResource('Section');
    }
}

?>