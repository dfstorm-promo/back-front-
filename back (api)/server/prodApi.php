<?php

require_once 'api.php';
require_once 'product_model.php';

class ProdApi extends Api
{
    public $apiName = 'prod';
    protected $product_model;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->product_model = new ProductModel();
    }

    public function getAction(){
        $attr = '';
        $func = array_shift($this->requestUri);
        if($this->requestUri){
            $attr = array_shift($this->requestUri);
        }
        switch($func){
            case 'get_token':
                $auth_info = array();
                $auth = explode('&', $this->param);
                foreach($auth as $value){
                    list($key, $val) = explode('=', $value);
                    $auth_info[$key] = $val;
                }
                if($auth_info['login'] == $this->base_log && $auth_info['password'] == $this->base_pass){
                    return $this->response($this->base_token, 200);
                }else{
                    return $this->response('Invalid login or pass', 403);
                }
                break;
            case 'get_category_tree':
                $catTree = $this->product_model->getCategoryTree();
                if($catTree){
                    return $this->response($catTree, 200);
                }else{
                    return $this->response('Category tree error', 404);
                }
                break;
            case 'get_category_by_id':
                $cat = $this->product_model->getCategoryById($attr);
                if($cat){
                    return $this->response($cat, 200);
                }else{
                    return $this->response('Category error', 404);
                }
                break;
            case 'get_product':
                $products = $this->product_model->getProductById($attr);
                if($products){
                    return $this->response($products, 200);
                }else{
                    return $this->response('No product found for this id', 404);
                }
                break;
            case 'update_products_list':
                $upd_products = $this->product_model->getLastUpdatedProductsList($attr);
                if($upd_products){
                    return $this->response($upd_products, 200);
                }else{
                    return $this->response('No products found for this timestamp', 404);
                }
                break;
            default:
                return $this->response($func, 404);
        }
    }

    public function postAction(){
        $param = file_get_contents('php://input') ? json_decode(file_get_contents('php://input')) : '';
        $func = array_shift($this->requestUri);
        if($this->requestUri){
            $attr = array_shift($this->requestUri);
        }

        switch($func){
            case 'get_products':
                $products = $this->product_model->getProductsArr($param);
                if($products){
                    return $this->response($products, 200);
                }else{
                    return $this->response('No products found for these ids', 404);
                }
                break;
            case 'update_products':
                $products = $this->product_model->getLastUpdatedProducts($attr, $param);
                if($products){
                    return $this->response($products, 200);
                }else{
                    return $this->response('No products found for these ids', 404);
                }
                break;
            default:
                return $this->response('Function not found', 404);
        }
    }

    public function putAction(){
        return $this->response('Data not found', 404);
    }

    public function deleteAction(){
        return $this->response('Data not found', 404);
    }
}