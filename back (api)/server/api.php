<?php

abstract class Api
{
    public $apiName = '';
    public $requestUri = [];
    public $uri = '';
    public $requestParams = [];
    protected $method = '';
    protected $action = '';
    protected $base_log = '';
    protected $base_pass= '';
    protected $base_token = '';
    protected $base_api_name = '';


    public function __construct(array $config = []) {
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        $this->base_log = $config['login'];
        $this->base_pass = $config['password'];
        $this->base_token = $config['token'];
        $this->base_api_name = $config['api_name'];

        $this->uri = trim($_SERVER['REQUEST_URI'], '/');

        if(strpos($this->uri, '?') !== false){
            $this->uri = explode('?', $this->uri);
            $this->param = end($this->uri);
            $this->uri = $this->uri[0];

        }
        $this->requestUri = explode('/', $this->uri);
        $this->requestParams = $_REQUEST;

        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }
    }

    public function run() {
        if(!$this->param){
            throw new RuntimeException('Unauthorized', 403);
        }else{
            $this->authorization();
        }

        if(array_shift($this->requestUri) !== $this->base_api_name || array_shift($this->requestUri) !== $this->apiName){
            throw new RuntimeException('API Not Found', 404);
        }

        $this->action = $this->chooseAction();

        if (method_exists($this, $this->action)) {
            return $this->{$this->action}();
        } else {
            throw new RuntimeException('Invalid Method', 405);
        }
    }

    protected function authorization(){
        if(strpos($this->param, 'login') !== false && strpos($this->param, 'password') !== false){
            return true;
        }elseif(strpos($this->param, 'token') !== false){
            $token = explode('=', $this->param);
            $token = end($token);
            if($token == $this->base_token){
                return true;
            }else{
                throw new RuntimeException('Invalid token', 403);
            }
        }else{
            throw new RuntimeException('Unauthorized', 403);
        }
    }

    protected function response($data, int $status = 500) {
        header("HTTP/1.1 " . $status . " " . $this->requestStatus($status));
        if($status == 200){
            return json_encode($data);
        }else{
            throw new RuntimeException($data, $status);
        }
    }

    private function requestStatus(int $code): string {
        $status = array(
            200 => 'OK',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );
        return ($status[$code]) ? $status[$code] : $status[500];
    }

    protected function chooseAction()
    {
        $method = $this->method;
        switch ($method) {
            case 'GET':
                return 'getAction';
                break;
            case 'POST':
                return 'postAction';
                break;
            case 'PUT':
                return 'putAction';
                break;
            case 'DELETE':
                return 'deleteAction';
                break;
            default:
                return null;
        }
    }

    abstract protected function getAction();
    abstract protected function postAction();
    abstract protected function putAction();
    abstract protected function deleteAction();
}

