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

class ContificoOrderModel extends ObjectModel
{
    public $id_order;
    public $id_cart;
    public $code;
    public $id_order_contifico;
    public $total;
    public $currency;
    public $status;
    public $message;

    public static $definition = array(
        'table' => 'contifico_doc',
        'primary' => 'id_order',
        'multilang' => false,
        'fields' => array(
            'id_cart' => array(
                'type' => self::TYPE_INT,
            ),
            'code' => array(
                'type' => self::TYPE_STRING,
            ),
            'id_order_contifico' => array(
                'type' => self::TYPE_STRING,
            ),
            'total' => array(
                'type' => self::TYPE_FLOAT,
            ),
            'status' => array(
                'type' => self::TYPE_STRING,
            ),
            'currency' => array(
                'type' => self::TYPE_STRING
            ),
            'message' => array(
                'type' => self::TYPE_STRING
            ),
            'date_add' => array(
                'type' => self::TYPE_STRING,
            ),
            'date_upd' => array(
                'type' => self::TYPE_STRING,
            )
        ),
    );

    public static function has_fac_id_contifico($id_order)
    {
        $query = "SELECT a.id_order FROM `" . _DB_PREFIX_ . "contifico_doc` a WHERE a.id_cart = $id_order";
        return Db::getInstance()->getValue($query);
    }
}
