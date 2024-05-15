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

class AdminContificoSettingController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'configuration';
        $this->lang = true;

        parent::__construct();
        $this->toolbar_title = $this->l('General Settings');
    }

    public function initContent()
    {
        parent::initContent();
        $this->initToolbar();
        $this->display = '';
        $this->content .= $this->renderForm();
        $this->content .= $this->renderCronBlock();

        $this->context->smarty->assign(array(
            'content' => $this->content,
        ));
    }

    /**
     * Render price block
     *
     * @return void
     */
    public function renderPriceBlock()
    {
        $this->context->smarty->assign(array(
            'form_action' => $this->context->link->getAdminLink('AdminContificoSetting'),
            'price' => Configuration::get('CONTIFICO_AMOUNT'),
            'porcentaje' => Configuration::get('CONTIFICO_AMOUNT_PRICE_POR'),
            'fijo' => Configuration::get('CONTIFICO_AMOUNT_PRICE_FIJO'),
        ));


        if (Tools::version_compare(_PS_VERSION_, '1.7.6', '<')) {
            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'contifico/views/templates/admin/price_block.tpl'
            );
        } else {
            return $this->context->smarty->fetch(
                'module:contifico/views/templates/admin/price_block.tpl'
            );
        }
    }

    /**
     * Render cron block
     *
     * @return void
     */
    public function renderCronBlock()
    {

        $cronLink = $this->context->link->getModuleLink(
            'contifico',
            'cron',
            array(
                'token' => Configuration::get('CONTIFICO_CRON_TOKEN'),
                'action' => "setDataCatalgContifico"
            )
        );

        $cronLinkUpdate = $this->context->link->getModuleLink(
            'contifico',
            'cron',
            array(
                'token' => Configuration::get('CONTIFICO_CRON_TOKEN'),
                'action' => "updateDataProdutcs"
            )
        );

        $this->context->smarty->assign(array(
            'form_action' => $this->context->link->getAdminLink('AdminContificoSetting'),
            'cron_link' => $cronLink,
            'cron_link_up' => $cronLinkUpdate,
            'cron_token' => Configuration::get('CONTIFICO_CRON_TOKEN')
        ));

        if (Tools::version_compare(_PS_VERSION_, '1.7.6', '<')) {
            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'contifico/views/templates/admin/cron_block.tpl'
            );
        } else {
            return $this->context->smarty->fetch(
                'module:contifico/views/templates/admin/cron_block.tpl'
            );
        }
    }



    public function renderForm()
    {
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Configuracion'),
                'icon' => 'icon-image',
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Costo de envio'),
                    'name' => 'CONTIFICO_COST',
                    'required' => true,
                    'is_bool' => true,
                    'hint' => $this->l('Adicionar costo de envió'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Si')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Api Key de Contifico'),
                    'name' => 'CONTIFICO_API_KEY',
                    'required' => true,
                    'class' => 'fixed-width-xxl',
                    // 'size' => 5,
                    'hint' => $this->l('Coloque la api key proporcionada por contifico'),
                    // 'lang' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Token secreto'),
                    'name' => 'CONTIFICO_API_SECRET',
                    'required' => true,
                    'class' => 'fixed-width-xxl',
                    // 'size' => 3,
                    'hint' => $this->l('Coloque la api secreta proporcionada por contifico'),
                    // 'lang' => true,
                ),

                array(
                    'type' => 'text',
                    'label' => $this->l('codigo de sucursal'),
                    'name' => 'CONTIFICO_SUCURSAL_CODE',
                    'required' => true,
                    'class' => 'fixed-width-xxl',
                    // 'size' => 3,
                    'hint' => $this->l('Coloque los primeros 3 digitos de su codigo de facturación 000-***-*******'),
                    // 'lang' => true,
                ),


                array(
                    'type' => 'text',
                    'label' => $this->l('Codigo de facturación'),
                    'name' => 'CONTIFICO_FACT_CODE',
                    'required' => true,
                    'class' => 'fixed-width-xxl',
                    // 'size' => 3,
                    'hint' => $this->l('Coloque los 3 digitos medios de su codigo de facturación ***-000-*******'),
                    // 'lang' => true,
                ),

                array(
                    'type' => 'text',
                    'label' => $this->l('Ultimo Codigo de facturación'),
                    'name' => 'CONTIFICO_LAST_CODE',
                    'required' => true,
                    'class' => 'fixed-width-xxl',
                    // 'size' => 3,
                    'hint' => $this->l('Coloque el ultimo codigo de facturación registrado ***-***-0000000001'),
                    // 'lang' => true,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Estado del pedido Contifico'),
                    'name' => 'CONTIFICO_STATUS',
                    'required' => true,
                    'class' => 'form-control',
                    'hint' => $this->l('Seleccione el estado que debe tener la orden para crear el pedido a contifico.'),
                    'options' => array(
                        'query' => OrderState::getOrderStates((int) $this->context->language->id),
                        'id' => 'id_order_state',
                        'name' => 'name'
                    )
                ),
            ),

            'submit' => array(
                'title' => $this->l('Guardar'),
                'name' => 'submitDisplaySettings',
            ),
        );

        $this->fields_value = array(
            'CONTIFICO_STATUS' => Tools::getValue(
                'CONTIFICO_STATUS',
                Configuration::get('CONTIFICO_STATUS')
            ),

            'CONTIFICO_LAST_CODE' => Tools::getValue(
                'CONTIFICO_LAST_CODE',
                Configuration::get('CONTIFICO_LAST_CODE')
            ),

            'CONTIFICO_COST' => Tools::getValue(
                'CONTIFICO_COST',
                Configuration::get('CONTIFICO_COST')
            ),
            'CONTIFICO_FACT_CODE' => Tools::getValue(
                'CONTIFICO_FACT_CODE',
                Configuration::get('CONTIFICO_FACT_CODE')
            ),
            'CONTIFICO_SUCURSAL_CODE' => Tools::getValue(
                'CONTIFICO_SUCURSAL_CODE',
                Configuration::get('CONTIFICO_SUCURSAL_CODE')
            ),
            'CONTIFICO_API_KEY' => Tools::getValue(
                'CONTIFICO_STATUS',
                Configuration::get('CONTIFICO_API_KEY')
            ),
            'CONTIFICO_API_SECRET' => Tools::getValue(
                'CONTIFICO_API_SECRET',
                Configuration::get('CONTIFICO_API_SECRET')
            ),
            'CONTIFICO_CATEGORY_DEFECT' => Tools::getValue(
                'CONTIFICO_CATEGORY_DEFECT',
                Configuration::get('CONTIFICO_CATEGORY_DEFECT')
            ),
            'CONTIFICO_BODEGA_DEFECT' => Tools::getValue(
                'CONTIFICO_BODEGA_DEFECT',
                Configuration::get('CONTIFICO_BODEGA_DEFECT')
            ),
        );

        if ($apiKey = Configuration::get('CONTIFICO_API_KEY')) {
            $categorys = $this->getCategorysContifico($apiKey);
            $bodegas = $this->getBodegasContifico($apiKey);
            if (isset($categorys) && isset($bodegas)) {
                $this->fields_form['input'][] = array(
                    'type' => 'select',
                    'label' => $this->l('Categoria por defecto.'),
                    'name' => 'CONTIFICO_CATEGORY_DEFECT',
                    'required' => true,
                    'class' => 'fixed-width-xxl',
                    'hint' => $this->l('Coloque una categoria por defecto para los productos que se creen automaticamente.'),
                    'options' => array(
                        'query' => $categorys,
                        'id' => 'id',
                        'name' => 'nombre'
                    )
                );

                $this->fields_form['input'][] = array(
                    'type' => 'select',
                    'label' => $this->l('Bodega por defecto.'),
                    'name' => 'CONTIFICO_BODEGA_DEFECT',
                    'required' => true,
                    'class' => 'fixed-width-xxl',
                    'hint' => $this->l('Coloque una bodega por defecto para los productos que se creen automaticamente.'),
                    'options' => array(
                        'query' => $bodegas,
                        'id' => 'id',
                        'name' => 'nombre'
                    )
                );
            }
        }

        return parent::renderForm();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitDisplaySettings')) {
            Configuration::updateValue(
                'CONTIFICO_LAST_CODE',
                Tools::getValue('CONTIFICO_LAST_CODE')
            );

            Configuration::updateValue(
                'CONTIFICO_STATUS',
                Tools::getValue('CONTIFICO_STATUS')
            );

            Configuration::updateValue(
                'CONTIFICO_COST',
                Tools::getValue('CONTIFICO_COST')
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

            Tools::redirectAdmin(self::$currentIndex . '&conf=4&token=' . $this->token);
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
