<?php

class shopJoomproductsAdd extends shopJoomproducts{

    public function getProducts(){
        $exclusion_list = $this->checkAddedProducts();
        $json_data = $exclusion_list ? json_encode($exclusion_list) : false;
        if($base_products = $this->getProductArr($json_data)){
            return $base_products;
        }else{
            throw new Exception('Products not found!');
        }
    }

    public function checkAddedProducts(){
        static $product_model;
        if(empty($product_model)) {
            $product_model = new shopProductModel();
        }
        if($base_product_ids = $product_model->select('base_id')->where('base_id IS NOT NULL')->fetchAll()){
            for($i=0; $i<count($base_product_ids); $i++){
                $product_ids[$i] = $base_product_ids[$i]['base_id'];
            }
            return $product_ids;
        }
    }

    public function getProductArr($json_data = false){
        $products = self::getLinkDataSSL(self::BASE_URL.'get_products'.$this->token, $json_data);
        if(isset($products->error)){
            throw new Exception($products->error);
        }else{
            return $products;
        }
    }

    public function getProductById($id){
        if(!$id){
            return false;
        }
        $product = self::getLinkDataSSL(self::BASE_URL.'get_product/'.$id.$this->token);
        if(isset($product->error)){
            throw new Exception($product->error);
        }else{
            foreach ($product as $prod){
                if($id = $this->saveProduct($prod)){
                    return $id;
                }
            }
        }
    }

    public function saveProduct($product) : bool{
        $data = $this->prepareProductToSave($product);
        if($data){
            return $this->addProduct($data);
        }
    }

    public function addProduct(array $data, bool $insert = false) : bool{
        $product = new shopProduct();
        if($product->save($data, true)){
            $id = $product->getId();
            if($insert){
                $data = array('product_id' => $id);
                $this->images_model->insert($data);
            }
            return true;
        }
    }
}