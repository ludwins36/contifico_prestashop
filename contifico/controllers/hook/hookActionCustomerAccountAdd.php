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


class contificoHookActionCustomerAccountAddController
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
       
        if(array_key_exists('cedula', $_POST)){
            $apiKey = Configuration::get('CONTIFICO_API_KEY');
            $token = Configuration::get('CONTIFICO_API_SECRET');
            $post_data = array(
                "tipo" => "N",
                "razon_social" => $_POST['firstname'] . " " . $_POST['lastname'],
                "es_cliente" => 1,
                "es_proveedor" => 0,
                "email" => $_POST['email'],
                "nombre_comercial" => $_POST['firstname'] . " " . $_POST['lastname'],
                "cedula" => $_POST['cedula']
            );

            $customer = $this->postCreatePersoneInContifico($post_data, $apiKey, $token);
            if (array_key_exists('mensaje', $customer)) {
                PrestaShopLogger::addLog(
                    "Error al crear cliente en contifico ClienteID" . $params['newCustomer']->id . " " . $customer['mensaje'],
                    3
                );
            }else{
                $customers_contifico = array(
                    "customer_id" => $params['newCustomer']->id,
                    "contifico_id" => $customer['id'],
                    "cedula" => $_POST['cedula']
                );
                Db::getInstance()->insert('customers_contifico', $customers_contifico);
            }
            // echo  "<pre>";
            // print_r($customer);
            // echo  "</pre>";
            // exit;

            
        }
    }

    public function postCreatePersoneInContifico($data, $apiKey, $token)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.contifico.com/sistema/api/v1/persona/?pos=' . $token,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $apiKey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }
}
