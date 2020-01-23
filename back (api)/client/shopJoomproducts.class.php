<?php

class shopJoomproducts{
    
    const BASE_URL = 'some_url';
    const BASE_CURRENCY = 'some_currency';
    const TEMP_IMG_DIR = 'some_dir';
    const INTER_LOGIN = 'some_login';
    const INTER_PASS = 'some_pass';
    
    public static $token;
    public $images_model;
    public $update_model;

    public function __construct() {
        $this->images_model = new shopJoomproductsImagesModel();
        $this->update_model = new shopJoomproductsUpdateModel();
    }
    
    public static function getLinkDataSSL(string $url, $json_data=false) : stdClass{
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_USERAGENT      => "spider",
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_MAXREDIRS      => 4,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, 
        );

        if($json_data){
            $options += array(
                CURLOPT_POSTFIELDS 	=> $json_data,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Content-Length: ' . strlen($json_data))		
            );
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $header  = curl_getinfo($ch);
        $err = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        curl_close( $ch );
        
        if($header['http_code'] = 200){
            return json_decode($content);
        }
    }
    
    public static function getToken() : bool {
        $token = self::getLinkDataSSL(self::BASE_URL.'get_token?login='.self::INTER_LOGIN.'&password='.self::INTER_PASS);
        if(isset($token->error)){
            throw new Exception($token->error);
        }else{
            self::$token = '?token='.$token;
            return true;
        }    
    }

    public function prepareProductToSave(stdClass $base_product) : array {
        $data['name'] = trim($base_product->product_name);
        $data['summary'] = trim($base_product->product_s_desc);
        $data['base_id'] = $base_product->product_id;
        $data['ef_model'] = ifempty($base_product->ef_model, NULL);
        $data['ef_view'] = ifempty($base_product->ef_view, NULL);
        $data['ef_brend'] = ifempty($base_product->ef_brend, NULL);
        $data['ef_tail'] = ifempty($base_product->ef_tail, NULL);
        $data['ef_type'] = ifempty($base_product->ef_type, NULL);
        $data['type_id'] = 4;        
        if($base_product->product_in_stock && $base_product->product_in_stock <= 0){
            $stock = 0;                
        }elseif($base_product->product_in_stock){
            $stock = $base_product->product_in_stock;
        }else{
            $stock = 100;
        }
        if($base_product->product_publish && $base_product->product_publish == 'N'){
            $data['status'] = 0;
        }else{
            $data['status'] = 1;
        }
        $skus = array();
        $sku_id = 0;
        if (empty($skus)) {
            $skus[--$sku_id] = $this->formatPrice($base_product);
            $skus[$sku_id] += array(
                'sku'       => ifempty($base_product->product_sku, ''),
                'stock'     => array(
                    0 => $stock,
                ),
                'available' => 1
            );
        }
        $data['currency'] = self::BASE_CURRENCY;
        $data['sku_id'] = $sku_id;
        $data['skus'] = $skus;

        return $data;
    }

    public function formatPrice(stdClass $data) : array{
        return array(
            'price' => $data->product_trade,
            'primary_price' => $data->product_trade,
            'purchase_price' => $data->product_purchase
        );
    }    
    
    public function transliterate(string $str) : string{
        $strTransliterated = shopHelper::transliterate($str);
        return $strTransliterated;
    }

}