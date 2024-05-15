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

class AdminContificoProductsCreateController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'contifico_products';
        $this->identifier = 'contifico_id';
        parent::__construct();
        $this->toolbar_title = $this->l('Productos Creados');
    }

    public function renderList()
    {
        // $this->_join = " JOIN `" . _DB_PREFIX_ . "product` p ON p.reference = a.codigo ";
        // $this->_join .= " JOIN `" . _DB_PREFIX_ . "product_attribute` pt ON pt.id_product = p.id_product ";
        // $this->_where = " AND a.codigo != '' AND p.reference != ''";
        $this->_where = "AND EXISTS (SELECT * FROM `" . _DB_PREFIX_ . "product` p WHERE p.`reference` = a.`codigo` ) OR EXISTS (SELECT * FROM `" . _DB_PREFIX_ . "product_attribute` pt WHERE pt.`reference` = a.`codigo` )";
        $this->fields_list = array(
            'contifico_id' => array(
                'title' => $this->l('id'),
                'align' => 'text-center',
                'remove_onclick' => true,
                'class' => 'fixed-width-xs',
            ),
            'codigo' => array(
                'title' => $this->l('Referencia'),
                'remove_onclick' => true,
                'havingFilter' => true,
            ),
            'nombre' => array(
                'title' => $this->l('Nombre'),
                'remove_onclick' => true,
                'havingFilter' => true,
            ),
            'description' => array(
                'title' => $this->l('Descripcion'),
                'remove_onclick' => true,
                'havingFilter' => true,
            ),
            'cantidad_stock' => array(
                'title' => $this->l('Stock'),
                'remove_onclick' => true,
                'havingFilter' => true,
            ),
            'precio' => array(
                'title' => $this->l('Precio'),
                'remove_onclick' => true,
                'havingFilter' => true,
            ),
            'category' => array(
                'title' => $this->l('Categoria'),
                'remove_onclick' => true,
                'havingFilter' => true,
            ),
        );
        return parent::renderList();
    }
}