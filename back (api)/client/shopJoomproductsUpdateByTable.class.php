<?php

class shopJoomproductsUpdateByTable extends shopJoomproductsUpdate{
    
    protected $command = 'update_products';
    protected $time = '';
    
    public function __construct(array $json = []) {
        parent::__construct();
        $this->json = $json ? json_encode($json) : '';
    }
    
    public function updateProducts(){
        if(!$this->json){
            $this->json = $this->getIdList();
        }    
        if($products = $this->getProductsUpdateArr()){
            foreach ($products as $product){
                if($this->saveProduct($product)){
                    $this->update_model->deleteFromTable($product->product_id);
                }
            }
        }          
    }
    
    function getIdList() : string {
        $id_list = $this->update_model->getIdListFromTable();
        foreach ($id_list as $key => $value){
            $order[$key] = $value['product_id'];
        }
        return json_encode($order);         
    }
    
    function updateProductsWithoutCategory(array $rules = []){
        if($products = $this->update_model->getProductsWithoutCategory($rules)){
            foreach($products as $key => $value){
                $base_ids[] = $value['base_id'];
            }
            $this->json = json_encode($base_ids);
            if($response = $this->getProductsUpdateArr()){
                $model = new shopProductModel();
                foreach ($response as $product) {
                    if(isset($product->category_id)){
                        if($inter_id = $model->select('id')->where('base_id = '.(int)$product->product_id)->fetchField('id')){
                            $joomproducts_category = new shopJoomproductsCategory();
                            $joomproducts_category->addProductToCategory($product, $inter_id);
                        }    
                    }else{
                        continue;
                    }
                }
            }           
        }        
    }
}