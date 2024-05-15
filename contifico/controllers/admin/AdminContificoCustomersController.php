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

class AdminContificoCustomersController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'customers_contifico';
        $this->identifier = 'id';

        parent::__construct();
        $this->toolbar_title = $this->l('Clientes');

       
    }

    public function initContent()
    {
        parent::initContent();
        $this->initToolbar();
    }

    public function renderList()
    {
        $this->fields_list = array(
            'contifico_id' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'filter_key' => 'a!id',
                'remove_onclick' => true,
                'class' => 'fixed-width-xs',
            ),
            'razon_social' => array(
                'title' => $this->l('Nombre'),
                'remove_onclick' => true,
                'filter_key' => 'a!razon_social',
                'havingFilter' => true,
            ),
            'direccion' => array(
                'title' => $this->l('Dirección'),
                'remove_onclick' => true,
                'havingFilter' => true,
            ),
            'type_doc' => array(
                'title' => $this->l('Tipo de Doc'),
                'remove_onclick' => true,
                'havingFilter' => true,
                // 'callback' => 'setOrderCurrency',
            ),
            'customer_id' => array(
                'title' => $this->l('id cliente'),
                'remove_onclick' => true,
                'havingFilter' => true,
            ),
          
        );

        $this->processFilter();

        return parent::renderList();
    }
   

  
    public function postProcess()
    {
        if (Tools::isSubmit('submitDisplaySettings')) {
            Configuration::updateValue(
                'CONTIFICO_LAST_CODE',
                Tools::getValue('CONTIFICO_LAST_CODE')
            );

            Configuration::updateValue(
                'CONTIFICO_FACT_CODE',
                Tools::getValue('CONTIFICO_FACT_CODE')
            );
            Configuration::updateValue(
                'CONTIFICO_SUCURSAL_CODE',
                Tools::getValue('CONTIFICO_SUCURSAL_CODE')
            );
            Configuration::updateValue(
                'CONTIFICO_API_KEY',
                Tools::getValue('CONTIFICO_API_KEY')
            );

            Configuration::updateValue(
                'CONTIFICO_API_SECRET',
                Tools::getValue('CONTIFICO_API_SECRET')
            );

            Configuration::updateValue(
                'CONTIFICO_CATEGORY_DEFECT',
                Tools::getValue('CONTIFICO_CATEGORY_DEFECT')
            );

            Configuration::updateValue(
                'CONTIFICO_BODEGA_DEFECT',
                Tools::getValue('CONTIFICO_BODEGA_DEFECT')
            );

            Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
        }
        parent::postProcess();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $modulePath = _PS_MODULE_DIR_ . $this->module->name;
        $this->addCSS($modulePath . '/views/css/wkproductContifico_back.css');
        $this->addJS($modulePath . '/views/js/wkproductContifico_back.js');
    }

    public function getBodegasContifico($apiKey)
    {


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.contifico.com/sistema/api/v1/bodega/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $apiKey
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }

    public function getCategorysContifico($apiKey)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.contifico.com/sistema/api/v1/categoria/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $apiKey
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }
}
