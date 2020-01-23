<?php

class shopJoomproductsUpdate extends shopJoomproducts{
    
    protected $command = 'update_products_list';
    protected $time;
    protected $json = '';
    public $update_mark_model;
    
    public function __construct($time = false) {
        parent::__construct();
        $this->update_mark_model = new shopJoomproductsUpdateMarkModel();
        $this->time = $time ? $time : time() - 86400;
    }
    
    public function getCountFromTable() : int {
        return $this->update_model->getCountIdsFromTable();
    }

    public function addMarkForUpdate(){
        $this->update_mark_model->addMark($this->time);
    }
    
    public function checkMarkForUpdate() : ?int {
        return $this->update_mark_model->getMark();
    }
    
    public function deleteMarkForUpdate(){
        $this->update_mark_model->deleteMark($this->time);
    }
    
    public function updateProducts(){        
        if($products = $this->getProductsUpdateArr()){
            foreach($products as $product){
                $this->update_model->insertIdToUpdateTable($product->product_id);              
            }
        }
    }
    
    public function getProductsUpdateArr() : stdClass {
        $products = self::getLinkDataSSL(self::BASE_URL.$this->command.'/'.$this->time.self::$token, $this->json);
        if(isset($products->error)){
            if($products->error == 'No products found for these ids'){
                $this->update_model->clearTable();
            }
            die();  
        }else{
            return $products;
        }             
    }

    public function saveProduct($product) : bool{
        $model = new shopProductModel();
        $data = $this->prepareProductToSave($product);
        if($data){
            if($id = $model->select('id')->where('base_id = '.(int)$data['base_id'])->fetchField('id')){
                $result = $this->updateProduct($id, $data);
            }else{
                $joomproducts_add = new shopJoomproductsAdd();
                $result = $joomproducts_add->addProduct($data);
            }
        }
        return $result;
    }

    public function updateProduct(int $id, array $data) : bool{
        $skus_model = new shopProductSkusModel();
        $product_model = new shopProductModel();

        $sku_temp = $data['skus'];
        $sku['price'] = $sku_temp[-1]['price'];
        $sku['primary_price'] = $sku_temp[-1]['primary_price'];
        $sku['purchase_price'] = $sku_temp[-1]['purchase_price'];
        $sku['sku'] = $sku_temp[-1]['sku'];
        $sku['count'] = $sku_temp[-1]['stock'][0];

        unset($data['sku_id']);
        unset($data['skus']);

        $product_info = $product_model->getById($id);
        if (!$product_info) {
            throw new waAPIException('invalid_param', 'Product not found', 404);
        }

        $product = new shopProduct($product_info);
        if($product->save($data, true)){
            $id = $product->getId();
            if($sku_ids = $skus_model->select('id')->where('product_id = '.(int)$id)->fetchField('id')){
                if(is_array($sku_ids)){
                    foreach($sku_ids as $sku_id){
                        $skus_model->update($sku_id, $sku);
                    }
                }else{
                    $skus_model->update($sku_ids, $sku);
                }
            }
            return true;
        }
    }

}