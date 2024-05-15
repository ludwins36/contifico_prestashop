<?php

/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2019 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */


class contificohookActionCarrierProcessController
{
    public function __construct($module, $file, $path)
    {
        require_once dirname($file) . '/classes/ContificoProductModel.php';

        $this->file = $file;
        $this->module = $module;
        $this->context = Context::getContext();
        $this->_path = $path;
    }

    /**
     * Set values for the inputs.
     */

    public function run($params)
    {
        $id_customer = $params['cart']->id_customer;
        $type = '';
        $per = '';
        $empresa  = "";
        $key = "type_doc_" . $id_customer;
        $key_per = "type_per_" . $id_customer;


        if ($this->context->cookie->$key) {
            $type = $this->context->cookie->$key;
        } else if (Configuration::get("type_dni_" . $id_customer)) {
            $type = Configuration::get("type_dni_" . $id_customer);
        }


        if ($this->context->cookie->$key_per) {
            $per = $this->context->cookie->$key_per;
        } else if (Configuration::get("type_per_" . $id_customer)) {
            $per = Configuration::get("type_per_" . $id_customer);
        }

        if (Configuration::get("name_juridit_" . $id_customer)) {
            $empresa = Configuration::get("name_juridit_" . $id_customer);
        }

        $this->context->smarty->assign(array(
            'def' => $type,
            'empresa' => $empresa,
            'persone' => $per

        ));

        $template = $this->context->smarty->fetch(
            'module:' . $this->module->name . '/views/templates/front/address_select.tpl'
        );

        Media::addJsDef(array(
            "select_temp" => $template
        ));

        if (Tools::getValue("type_dni")) {
            Configuration::updateValue("type_dni_" . $id_customer, Tools::getValue("type_dni"));
        }

        if (Tools::getValue("type_persona")) {
            Configuration::updateValue("type_per_" . $id_customer, Tools::getValue("type_persona"));
        }

        if (Tools::getValue("name_juridit")) {
            Configuration::updateValue("name_juridit_" . $id_customer, Tools::getValue("name_juridit"));
        }
    }
}