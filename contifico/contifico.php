<?php

/**
 * 2007-2020 PrestaShop
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
 *  @copyright 2007-2020 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}


include_once _PS_MODULE_DIR_ . 'contifico/classes/class-request.php';
include_once _PS_MODULE_DIR_ . 'contifico/classes/class-products.php';
include_once _PS_MODULE_DIR_ . 'contifico/classes/class-validatedocs.php';
include_once _PS_MODULE_DIR_ . 'contifico/classes/ContificoOrderModel.php';

class Contifico extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'contifico';
        $this->tab = 'administration';
        $this->version = '2.0.2';
        $this->author = 'LRobles';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Contifico');
        $this->description = $this->l('Administración y logística contifico');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('CONTIFICO_CRON_TOKEN', uniqid('INTC'));

        include(dirname(__FILE__) . '/sql/install.php');
        $moduleTabs = Tab::getCollectionFromModule('confitico');
        if (!empty($moduleTabs)) {
            foreach ($moduleTabs as $moduleTab) {
                $moduleTab->delete();
            }
        }
        $this->callInstallTab();
        $this->setDefaultValues();

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('actionOrderStatusPostUpdate') &&
            $this->registerHook('actionCartSave') &&
            $this->registerHook('displayProductActions') &&
            $this->registerHook('displayProductPriceBlock') &&
            $this->registerHook('displayAdminProductsExtra') &&
            // $this->registerHook('actionCustomerAccountAdd') &&
            // $this->registerHook('additionalCustomerFormFields') &&
            $this->registerHook('actionProductUpdate') &&
            $this->registerHook('actionProductAdd') &&
            $this->registerHook('actionCarrierProcess') &&
            $this->registerHook('backOfficeHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('CONTIFICO_ACTIVATE');
        include(dirname(__FILE__) . '/sql/uninstall.php');
        $this->uninstallTab();
        return parent::uninstall();
    }

    /**
     * Uninstall admin tabs
     *
     * @return bool
     */
    public function uninstallTab()
    {
        $moduleTabs = Tab::getCollectionFromModule($this->name);
        if (!empty($moduleTabs)) {
            foreach ($moduleTabs as $moduleTab) {
                $moduleTab->delete();
            }
        }
        return true;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        include(dirname(__FILE__) . '/sql/install.php');

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminContificoSetting'));
    }

    public function getHookController($hook_name)
    {
        // Include the controller file
        require_once dirname(__FILE__) . '/controllers/hook/' . $hook_name . '.php';

        // Build dynamically the controller name
        $controller_name = $this->name . $hook_name . 'Controller';

        // Instantiate controller
        $controller = new $controller_name($this, __FILE__, $this->_path);

        // Return the controller
        return $controller;
    }

    /**
     * Set default values while installing the module
     */
    public function setDefaultValues()
    {
        Configuration::updateValue('CONTIFICO_ACTIVATE', false);
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        $controller = $this->getHookController('hookActionOrderStatusPostUpdate');
        return $controller->run($params);
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        $controller = $this->getHookController('hookDisplayAdminProductsExtra');

        return $controller->run($params);
    }

    public function hookAdditionalCustomerFormFields($params)
    {
        $controller = $this->getHookController('hookAdditionalCustomerFormFields');

        return $controller->run($params);
    }

    public function hookActionCustomerAccountAdd($params)
    {

        $controller = $this->getHookController('hookActionCustomerAccountAdd');

        return $controller->run($params);
    }

    public function hookActionProductAdd($params)
    {
        $controller = $this->getHookController('hookActionProductAdd');

        return $controller->run($params);
    }

    public function hookActionProductUpdate($params)
    {
        $controller = $this->getHookController('hookActionProductUpdate');

        return $controller->run($params);
    }

    public function hookActionCarrierProcess($params)
    {
        $controller = $this->getHookController('hookActionCarrierProcess');
        return $controller->run($params);
    }


    /**
     * Create admin tabs
     */
    public function callInstallTab()
    {
        // Parent hidden class
        $this->installTab('AdminContifico', 'Contifico');
        // Main Tab
        $this->installTab('AdminContificoModule', 'Contifico Module', 'AdminContifico');
        // Manage Subscribed Products Tab
        $this->installTab('AdminContificoConfig', 'Configuración', 'AdminContificoModule');



        // Create sub tabs under configuration
        $this->installTab('AdminContificoSetting', 'Configuración General', 'AdminContificoConfig');
        $this->installTab('AdminContificoOrders', 'Facturas', 'AdminContificoModule');

        $this->installTab('AdminContificoCustomers', 'Clientes', 'AdminContificoModule');

        $this->installTab('AdminContificoProducts', 'Productos', 'AdminContificoModule');
        $this->installTab('AdminContificoProductsCreate', 'Productos Creados', 'AdminContificoProducts');
        $this->installTab('AdminContificoProductsPending', 'Productos Pendientes', 'AdminContificoProducts');
        return true;
    }

    public function installTab($className, $tabName, $tabParentName = false)
    {
        // Create instance of Tab class
        $tab = new Tab;
        $tab->name = array();
        $tab->class_name = $className;
        $tab->active = 1;
        // Set tab name for all installed languages
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tabName;
        }

        // Set parent tab ID
        if ($tabParentName) {
            $tab->id_parent = (int) Tab::getIdFromClassName($tabParentName);
        } else {
            $tab->id_parent = 0;
        }

        if ($className == 'AdminContificoModule') {
            $tab->icon = 'today';
        }

        // Assing module name
        $tab->module = $this->name;
        return $tab->add();
    }


    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        $this->context->controller->addJS($this->_path . 'views/js/back.6.js');
        $this->context->controller->addCSS($this->_path . 'views/css/back.css');
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }
    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public static function required_data($dir)
    {
        require(dirname(__FILE__) . $dir);
    }
}
