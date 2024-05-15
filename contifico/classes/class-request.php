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

class ContificoRequest
{
    public function getProductsContifico($apiKey)
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.contifico.com/sistema/api/v1/producto/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $apiKey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public static function getCustomerContifico($customer,  $apiKey, $order, $token)
    {
        $id_customer = $customer->id;
       
        $billingAddres = new Address((int)$order->id_address_invoice);

        $type_doc = Configuration::get("type_dni_" . $id_customer);
        $type_persona = Configuration::get("type_per_" . $id_customer);
        $nombre_empresa = Configuration::get("name_juridit_" . $id_customer);

        $customer_request = array(
            "tipo" => ($type_persona && $type_persona != '') ? $type_persona : "N",
            "razon_social" => ($nombre_empresa && $nombre_empresa != '') ? $nombre_empresa : $customer->firstname . " " . $customer->lastname,
            "es_cliente" => 1,
            "es_proveedor" => 0,
            "email" => $customer->email,
            "nombre_comercial" => $customer->firstname . " " . $customer->lastname,
            "direccion" => $billingAddres->address1,
            "telefonos" => $billingAddres->phone_mobile != '' ? $billingAddres->phone_mobile : $billingAddres->phone
        );

        if ($type_doc == "ruc") {
            $type_doc_fac = "ruc";
            $customer_request['ruc'] = $billingAddres->dni;
            $customer_request['cedula'] = substr($billingAddres->dni, 0, -3);
        } else {
            $type_doc_fac = "cedula";
            $customer_request['ruc'] = "";
            $customer_request['cedula'] = $billingAddres->dni;
        }

        $sqlid_product = "select * from " . _DB_PREFIX_ . "customers_contifico where customer_id = $id_customer";
        $isExist = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sqlid_product);

        $customer_contifico = array(
            "customer_id" => $id_customer,
            "tipo" => $customer_request['tipo'],
            "type_doc" => $type_doc_fac,
            "direccion" => $customer_request['direccion'],
            "email" => $customer_request['email'],
            "telefonos" => $customer_request['telefonos'],
            "razon_social" => $customer_request['razon_social'],
            "date_creation" => date("Y-m-d h:i"),
            "cedula" =>  $customer_request['cedula'],
            "ruc" =>  $customer_request['ruc'],
        );
        
        if ($isExist) {
            $online_customer = self::checkCustomerContificoOnline($isExist, $apiKey);
            if ($online_customer) {
                $customer_request = array(
                    "direccion" => $billingAddres->address1,
                    "telefonos" => $billingAddres->phone_mobile != '' ? $billingAddres->phone_mobile : $billingAddres->phone
                );
                self::postCreatePersoneInContifico($customer_request, $apiKey, $token, "PUT");
                return $customer_contifico;
            }
        }

        $post_customer = self::postCreatePersoneInContifico($customer_request, $apiKey, $token, "POST");

        if (array_key_exists('mensaje', $post_customer)) {
            if ($post_customer['mensaje'] != 'Persona ya existe') {
                PrestaShopLogger::addLog(
                    json_encode($post_customer),
                    3
                );

                PrestaShopLogger::addLog(
                    "Error al Crear cliente en contifico " . $order->id . " : CLiente #" . $id_customer,
                    3
                );

                return false;
            }
        }

        $customer_contifico["contifico_id"] = $post_customer['id'];
      

        Db::getInstance()->insert('customers_contifico', $customer_contifico);
        return $customer_contifico;
    }

    public static function checkCustomerContificoOnline($customer_contifico, $apiKey)
    {
        $idValid = true;
        if ($customer_contifico['contifico_id'] != null) {
            $customer_contifico_get = self::getCustomerConfitico($customer_contifico['contifico_id'], $apiKey);
            if (array_key_exists('mensaje', $customer_contifico_get)) {
                Db::getInstance()->delete('customers_contifico', "customer_id = " . $customer_contifico['customer_id']);
                $idValid = false;
            }
        } else {
            Db::getInstance()->delete('customers_contifico', "customer_id = " . $customer_contifico['customer_id']);
            $idValid = false;
        }
        return $idValid;
    }

    public static function getCustomerConfitico($id, $apiKey)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.contifico.com/sistema/api/v1/persona/' . $id,
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

    public static function isNecesarioActualizar($data_post, $data_local)
    {
        if (!$data_post) {
            return false;
        }

        foreach ($data_local as $key => $customer) {
            if (isset($data_post[$key]) && $key != 'id') {
                if ($data_post[$key] != $customer) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function postCreatePersoneInContifico($data, $apiKey, $token, $method = "POST")
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
            CURLOPT_CUSTOMREQUEST => $method,
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

    public static function checkproductContifico($code, $apiKey)
    {
        $sqlid_product = "select * from " . _DB_PREFIX_ . "contifico_products where codigo = '" . $code . "'";
        $rowsidp = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sqlid_product);
        if ($rowsidp) {
            $produc_check = self::getProductConfitico($rowsidp['contifico_id'], $apiKey);
            if (array_key_exists('mensaje', $produc_check)) {
                Db::getInstance()->delete('contifico_products', "codigo = '" . $code . "'");
                $rowsidp = false;
            }
        }

        return $rowsidp;
    }

    public static function getProductConfitico($id, $apiKey)
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.contifico.com/sistema/api/v1/producto/' . $id,
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

    public static function postEgresoInventarioAlmacen($data, $token)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.contifico.com/sistema/api/v1/movimiento-inventario/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $token,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }

    public static function getIdByReference($reference)
    {
        if (empty($reference)) {
            return 0;
        }

        $query = new DbQuery();
        $query->select('pa.id_product_attribute');
        $query->from('product_attribute', 'pa');
        $query->where('pa.reference = \'' . pSQL($reference) . '\'');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }



    public static function getIdProductByReference($reference)
    {
        if (empty($reference)) {
            return 0;
        }


        $query = new DbQuery();
        $query->select('p.id_product');
        $query->from('product', 'p');
        $query->where('p.reference LIKE \'%' . $reference . '%\'');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    public static function postCreateFactura($data, $apiKey)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.contifico.com/sistema/api/v1/documento/',
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

    public static function postCreateProductInContifico($data, $token)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.contifico.com/sistema/api/v1/producto/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $token,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }

    public static function postIngresoInventarioAlmacen($data, $token)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.contifico.com/sistema/api/v1/movimiento-inventario/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $token,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }
}
