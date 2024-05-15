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

class AdminContificoProductsPendingController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'contifico_products';
        $this->identifier = 'codigo';
        $this->context = Context::getContext();

        parent::__construct();
        $this->toolbar_title = $this->l('Productos pendientes');
    }

    public function renderList()
    {
        // AND a.`active` > 0 AND a.`cantidad_stock` > 0
        $this->_where = 'AND NOT EXISTS (SELECT * FROM `' . _DB_PREFIX_ . 'product` p WHERE p.`reference` = a.`codigo` ) AND NOT EXISTS (SELECT * FROM `' . _DB_PREFIX_ . 'product_attribute` pt WHERE pt.`reference` = a.`codigo` )';
        $this->fields_list = array(
            'codigo' => array(
                'title' => $this->l('referencia'),
                'align' => 'text-center',
                'remove_onclick' => true,
                'class' => 'fixed-width-xs',
            ),
            'nombre' => array(
                'title' => $this->l('Nombre'),
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
            'contifico_id' => array(
                'title' => $this->l('ID'),
                'remove_onclick' => true,
                'havingFilter' => true,
            ),
            'category' => array(
                'title' => $this->l('Categoria'),
                'remove_onclick' => true,
                'havingFilter' => true,
            ),
        );

        $this->bulk_actions = array(
            'create' => array(
                'text' => $this->l('Crear productos seleccionados'),
                'confirm' => $this->l('Crear los productos seleccionados en la tienda de prestashop?'),
                'icon' => 'icon-refresh'
            )
        );

        return parent::renderList();
    }

    public function postProcess()
    {
        if (Tools::getIsset("contifico_productsBox")) {
            $count = 0;
            foreach (Tools::getValue("contifico_productsBox") as $key => $id) {
                if (ContificoProducts::add_producto($id)) {
                    $count++;
                }
            }
            $this->context->controller->informations[] = "$count Productos creados correctamente";
        }
    }
}