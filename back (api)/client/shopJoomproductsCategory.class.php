<?php

class shopJoomproductsCategory extends shopJoomproducts{

    public function addCategories(): array{
        $cat_ids = array();
        if($cat_tree = $this->getCategoryTree()){
            if(is_array($cat_tree)){
                foreach ($cat_tree as $cat){
                    $cat_id = $this->addCategory($cat);
                    $cat_ids[] .=$cat_id;
                }
            }
        }
        return $cat_ids;
    }

    public function getCategoryTree() : stdClass{
        $tree = self::getLinkDataSSL(self::BASE_URL.'get_category_tree'.self::$token);
        if(isset($tree->error)){
            throw new Exception($tree->error);
        }else{
            foreach($tree as $category){
                if($slug = $this->transliterate($category->name)){
                    $category->slug = $slug;
                }
            }
            return $tree;
        }
    }

    public function getCategoryById(int $id) : stdClass{
        if(!$id){
            return false;
        }
        if($category = self::getLinkDataSSL(self::BASE_URL.'get_category_by_id/'.$id.self::$token)){
            if(isset($category->error)){
                throw new Exception($category->error);
            }else{
                return $category;
            }
        }
    }

    public function addCategory(stdClass $category) : int{
        static $category_model;
        $category_id = array();
        if (empty($category_model)) {
            $category_model = new shopCategoryModel();
        }
        if($category->parent != 0){
            $base_parent = $category_model->select('id')->where('base_id = '.(int)$category->parent)->fetchField('id');
            if($base_parent){
                $category->parent = $base_parent;
            }
        }
        $cat_info = array(
            'name' => $category->name,
            'url' => $category->slug,
            'base_id' => $category->id
        );
        if($category_id = $category_model->add($cat_info, $category->parent)){
            return $category_id;
        }
    }

    public function getNewCategory(int $cat_id){
        if($new_cat = $this->getCategoryById($cat_id)){
            if($slug = $this->transliterate($new_cat->name)){
                $new_cat->slug = $slug;
            }
            if($new_cat_id = $this->addCategory($new_cat)){
                return $new_cat_id;
            }else{
                return $this->getNewCategory($new_cat->parent);
            }
        }
    }

    public function addProductToCategory(array $data, int $id) : int{
        $category_model = new shopJoomproductsCategoryModel();
        if(!$cat_id = $category_model->getByBaseId($data->category_id)){
            $cat_id = $this->getNewCategory($data->category_id);
            while($cat_id != $category_model->getByBaseId($data->category_id)){
                $cat_id = $this->getNewCategory($data->category_id);
            }
        }
        $product_category_model = new shopCategoryProductsModel();
        if($product_category_model->add($id, $cat_id) == false){
            return $cat_id;
        }
    }


}