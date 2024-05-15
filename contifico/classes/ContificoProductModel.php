<?php
/**
* 2010-2020 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through this link for complete license : https://store.webkul.com/license.html
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to https://store.webkul.com/customisation-guidelines/ for more information.
*
*  @author    Webkul IN <support@webkul.com>
*  @copyright 2010-2020 Webkul IN
*  @license   https://store.webkul.com/license.html
*/

class ContificoProductModel extends ObjectModel
{
    public $id;
    public $product_id;
    public $contifico_id;
    public $mpn;
    public $bodega;
    public $description;
    public $categoryId;
    public $currency;
    public $instock;
    public $active;
    public $price;
    public $category;

    public static $definition = array(
        'table' => 'contifico_products',
        'primary' => 'id',
        'multilang' => false,
        'fields' => array(
            'id' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId'
            ),  
            'product_id' => array(
                'type' => self::TYPE_STRING,
            ),
            'contifico_id' => array(
                'type' => self::TYPE_STRING,
            ),
            'mpn' => array(
                'type' => self::TYPE_STRING,
            ),
            'bodega' => array(
                'type' => self::TYPE_STRING,
            ),
            'description' => array(
                'type' => self::TYPE_STRING,
            ),
            'categoryId' => array(
                'type' => self::TYPE_STRING,
            ),
            'currency' => array(
                'type' => self::TYPE_STRING
            ),
            'instock' => array(
                'type' => self::TYPE_INT
            ),
            'active' => array(
                'type' => self::TYPE_INT
            ),
            'category' => array(
                'type' => self::TYPE_STRING,
            ),
            
        ),
    );

    public static function checkproductContifico($id){ // estamos usando codigo dentro de un "class" clase, debemos usar public function
        $sqlid_product = "select * from "._DB_PREFIX_."contifico_products where product_id = '".$id."'";
        $rowsidp = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sqlid_product);
        return $rowsidp;
    }


}
