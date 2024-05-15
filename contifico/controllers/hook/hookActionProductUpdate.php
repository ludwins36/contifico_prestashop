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


class contificoHookActionProductUpdateController
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
        $id_product = (int)$params['id_product'];
        $product = new Product($id_product);

        $qty = $params['product']->quantity > 0 ? $params['product']->quantity : StockAvailable::getQuantityAvailableByProduct($product->id);
        if ($params['product']->quantity == 0 && StockAvailable::getQuantityAvailableByProduct($product->id) == 0) {
            if (!isset($_POST['qty_0_shortcut'])) {
                return false;
            }
            $qty = $_POST['qty_0_shortcut'];
        }


        if (Tools::getIsset('allow_contifico')) {

            $contificoData['active'] = (int)Tools::getValue('allow_contifico');
            if (Tools::isSubmit('contifico_add_product')) {
                $contificoData = array();
                $producContifico = $this->checkproductContifico($id_product);
                if (Tools::getIsset('contifico_categoria')) {
                    $category = explode(':', Tools::getValue('contifico_categoria'));
                    $contificoData['categoryId'] = $category[0];
                    $contificoData['category'] = $category[1];
                } else {
                    $contificoData['categoryId'] = '';
                    $contificoData['category'] = '';
                }

                if (Tools::getIsset('contifico_bodega')) {
                    $contificoData['bodega'] = Tools::getValue('contifico_bodega');
                } else {
                    $contificoData['bodega'] = '';
                }

                $desc = strip_tags($product->description_short[1], '<p>');
                $contificoData['instock'] = $qty;
                $contificoData['price'] = $product->price;
                $contificoData['product_id'] = $product->id;
                $contificoData['description'] = strip_tags($desc, '<span>');
                if ($producContifico) {
                    // actualizar producto o inventario en contifico
                    // solo se actualiza el monto o la categoria

                    // $contificoProduct = new ContificoProductModel($producContifico->id);
                    // foreach ($contificoData as $key => $value) {
                    //     $contificoProduct->{$key} = $value;
                    // }

                    // $contificoProduct->update();
                    if ($producContifico['instock'] != StockAvailable::getQuantityAvailableByProduct($product->id)) {
                        // actualizar el inventario de este procuto

                    }
                } else {
                    // crear producto en contifico

                    $taxRule = new Tax($product->id_tax_rules_group);
                    $code = '';
                    if ($product->reference == '') {
                        $code = str_pad($product->id, 7, "0", STR_PAD_LEFT);
                    } else {
                        $code = str_pad($product->reference, 7, "0", STR_PAD_LEFT);
                    }
                    $postData = array(
                        "minimo" => 1,
                        "pvp1" => $product->price,
                        "nombre" => $product->name[1],
                        "estado" => "A",
                        "cantidad_stock" => $contificoData['instock'],
                        "porcentaje_iva" => (int)$taxRule->rate,
                        "codigo" => $code,
                        "descripcion" => $contificoData['description'],
                        "categoria_id" => $category[0]
                    );

                    $apiKey = Configuration::get('CONTIFICO_API_KEY');
                    $product_post = $this->postCreateProductInContifico($postData, $apiKey);

                    if (!array_key_exists('mensaje', $product_post)) {
                        $contificoData['contifico_id'] = $product_post['id'];
                        Db::getInstance()->insert('contifico_products', $contificoData);
                        // agregarlo al almacen.
                        $almacenPostData = array(
                            "tipo" => "ING",
                            "fecha" => date('d/m/Y'),
                            "bodega_id" =>  $contificoData['bodega'],
                            "detalles" => [
                                [
                                    "producto_id" => $product_post['id'],
                                    "precio" => $product->price,
                                    "cantidad" => $contificoData['instock']
                                ]
                            ],
                            "descripcion" => "Ingreso por medio de modulo Prestashop."
                        );
                        $mvAlmacen = $this->postIngresoInventarioAlmacen($almacenPostData, $apiKey);
                        if (array_key_exists('mensaje', $mvAlmacen)) {
                            PrestaShopLogger::addLog(
                                "Error al insertar Moviento Contifico ProductID " . $id_product . " " . $mvAlmacen['mensaje'],
                                3
                            );
                            // $this->context->controller->errors[] = array(
                            //     $this->module->l($product_post['mensaje'])
                            // );
                            // die;
                        }
                    } else {
                        PrestaShopLogger::addLog(
                            "Error al crear Producto Contifico ProductID " . $id_product . " " . $product_post['mensaje'],
                            3
                        );
                        // $this->context->controller->errors[] = array(
                        //     $this->module->l($product_post['mensaje'])
                        // );
                    }

                    // if ($this->context->controller->errors) {
                    //     http_response_code(400);
                    //     die(json_encode($this->context->controller->errors));
                    // }
                }
            }
        }
    }



    public function checkproductContifico($id)
    { // estamos usando codigo dentro de un "class" clase, debemos usar public function
        $sqlid_product = "select * from " . _DB_PREFIX_ . "contifico_products where product_id = '" . $id . "'";
        $rowsidp = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sqlid_product);
        return $rowsidp;
    }

    public function postIngresoInventarioAlmacen($data, $token)
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

    public function postCreateProductInContifico($data, $token)
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
}