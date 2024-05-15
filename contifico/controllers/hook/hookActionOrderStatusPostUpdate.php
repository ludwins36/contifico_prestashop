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


class contificoHookActionOrderStatusPostUpdateController
{

    public function __construct($module, $file, $path)
    {
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
        $newStatus = $params['newOrderStatus'];
        $status = Configuration::get('CONTIFICO_STATUS');
        $order = new Order($params['id_order']);
        $carrier = new Carrier($order->id_carrier);
        $cart = new Cart($order->id_cart);

        if ($status == $newStatus->id) {

            if (Configuration::get('CONTIFICO_LAST_FACT_' . $order->id)) {
                return false;
            }

            $id_contifico_model = ContificoOrderModel::has_fac_id_contifico($order->id);
            $apiKey = Configuration::get('CONTIFICO_API_KEY');
            $token = Configuration::get('CONTIFICO_API_SECRET');
            $id_customer = $order->id_customer;
            $customer = new Customer($id_customer);
            $this->context->controller->informations[] = "Productos creados correctamente";
            $total_discounts = $cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS);

            $customer_contifico = ContificoRequest::getCustomerContifico($customer, $apiKey, $order, $token);
            $is_active = false;
            $almacenPostData = array();
            $bodega = '';
            $key_bod = -1;
            $detalles = array();
            $tax_discount = 0;
            $is_discount = false;
            $sub_total_products_12 = 0;
            $sub_total_products_0 = 0;
            $envio_imp = 0;
            $envio_sinimp = 0;
            $shipping_cost = 0;
            $tax_shipping = 0;

            if ((float)$order->total_discounts > 0) {
                $tax_discount = $order->total_discounts_tax_incl - $order->total_discounts_tax_excl;
                $is_discount = true;
            }

            $category = Configuration::get('CONTIFICO_CATEGORY_DEFECT');
            $bodega_defect = Configuration::get('CONTIFICO_BODEGA_DEFECT');
            $id_lang = Configuration::get('PS_LANG_DEFAULT');

            foreach ($cart->getProducts() as $product) {
                $product_id = $product['id_product'];
                $ps_product = new Product($product_id);
                $code = $product['reference'];
                $product_name = $product['name'];
                $product_qty = $product['quantity'];

                if ($ps_product->hasAttributes()) {
                    foreach ($ps_product->getAttributesResume($id_lang) as $attr) {
                        if ($attr['id_product_attribute'] == $product['id_product_attribute']) {
                            if ($attr['reference'] != '') {
                                $code = $attr['reference'];
                            }
                            $product_qty = $product['quantity'];
                            break;
                        }
                    }
                }

                if ($product['rate'] > 0) {
                    $sub_total_products_12 += round($product['price'], 2) * $product_qty;
                } else {
                    $sub_total_products_0 += round($product['price'], 2) * $product_qty;
                }


                if ($code != '') {
                    $producContifico = ContificoRequest::checkproductContifico($code, $apiKey);
                    if (!$producContifico) {
                        // crear producto en contifico
                        $postData = array(
                            "minimo" => 1,
                            "pvp1" => $product['price'],
                            "nombre" => $product_name,
                            "estado" => "A",
                            "cantidad_stock" => $product_qty,
                            "porcentaje_iva" => $product['rate'],
                            "codigo" => $code,
                            "categoria_id" => $category
                        );

                        $producContifico = ContificoRequest::postCreateProductInContifico($postData, $apiKey);
                        if (array_key_exists('mensaje', $producContifico)) {
                            if ($producContifico['mensaje'] != 'Producto ya existe') {
                                PrestaShopLogger::addLog(
                                    "Error al crear Producto Contifico Order # $order->id ProductID " . $product['id_product'] . " " . $producContifico['mensaje']
                                );
                                return false;
                            }
                        }

                        $data_query = array(
                            'active' => 1,
                            'categoryId' => $postData['categoria_id'],
                            "nombre" => $product_name,
                            'category' => '',
                            'bodega' => $bodega_defect,
                            'cantidad_stock' => $product_qty,
                            'precio' => $postData['pvp1'],
                            'product_id' => $product['id_product'],
                            'codigo' => $code,
                            'contifico_id' => $producContifico['id'],
                        );

                        Db::getInstance()->insert('contifico_products', $data_query);

                        $almacenPostIng = array(
                            "tipo" => "ING",
                            "fecha" => date('d/m/Y'),
                            "bodega_id" =>  $data_query['bodega'],
                            "detalles" => [
                                [
                                    "producto_id" => $producContifico['id'],
                                    "precio" =>  $postData['pvp1'],
                                    "cantidad" => $postData['cantidad_stock']
                                ]
                            ],
                            "descripcion" => "Ingreso por medio de modulo Prestashop."
                        );

                        $mvAlmacen = ContificoRequest::postIngresoInventarioAlmacen($almacenPostIng, $apiKey);
                        if (array_key_exists('mensaje', $mvAlmacen)) {
                            PrestaShopLogger::addLog(
                                "Error al insertar Moviento Contifico ProductID " . $product_id . " " . $mvAlmacen['mensaje'],
                                3
                            );
                        } else {
                            $is_active = true;
                        }
                    } else {
                        $is_active = true;
                    }

                    $detalles[] = array(
                        "producto_id" => isset($producContifico['contifico_id']) ? $producContifico['contifico_id'] : $producContifico['id'],
                        "cantidad" =>  $product_qty,
                        "precio" => round($product['price'], 2),
                        "porcentaje_iva" => $product['rate'],
                        "base_cero" => 0,
                        "porcentaje_descuento" => 0.00,
                        "base_gravable" => $product['rate'] > 0 ? round($product['price'], 2) * $product_qty : 0.00,
                        "base_no_gravable" => $product['rate'] > 0 ? 0.00 : round($product['price'], 2) * $product_qty,
                    );
                }
            }

            if (Configuration::get('CONTIFICO_COST') && $order->total_shipping_tax_excl > 0) {
                // costo envio
                $carrier = new Carrier($order->id_carrier);
                $id_shipping_contifico = Configuration::get("cost_shipping_" . $carrier->id);
                $code_ = str_pad($carrier->id, 7, "0", STR_PAD_LEFT);
                $isCreate = true;

                if ($id_shipping_contifico) {
                    $produc_check = ContificoRequest::getProductConfitico($id_shipping_contifico, $apiKey);
                    if (array_key_exists('mensaje', $produc_check)) {
                        Configuration::deleteByName("cost_shipping_" . $carrier->id);
                    } else {
                        $isCreate = false;
                    }
                }

                if ($isCreate) {
                    $postData = array(
                        "minimo" => 1,
                        "pvp1" => round($order->total_shipping_tax_excl, 2),
                        "nombre" => $carrier->name,
                        "estado" => "A",
                        "cantidad_stock" => 1000,
                        "porcentaje_iva" => round($order->carrier_tax_rate, 2),
                        "codigo" => $code_,
                        "categoria_id" => $category
                    );

                    $product_carrier = ContificoRequest::postCreateProductInContifico($postData, $apiKey);

                    if (array_key_exists('mensaje', $product_carrier)) {
                        PrestaShopLogger::addLog(
                            "Error al crear Producto Contifico Costo de Envio " . $carrier->id . " " . $product_carrier['mensaje']
                        );
                        return false;
                    }
                    $id_shipping_contifico = $product_carrier['id'];
                    Configuration::updateValue("cost_shipping_" . $carrier->id, $id_shipping_contifico);
                }

                $tax_shipping = $order->total_shipping_tax_incl - $order->total_shipping_tax_excl;
                $data_detalle_shipping = array(
                    "producto_id" => $id_shipping_contifico,
                    "cantidad" => 1,
                    "precio" => round($order->total_shipping_tax_excl, 2),
                    "porcentaje_iva" => 0,
                    "porcentaje_descuento" => 0.00,
                    "base_cero" =>  0.00,
                    "base_gravable" => 0,
                    "base_no_gravable" => round($order->total_shipping_tax_excl, 2)
                );

                if ($tax_shipping > 0) {
                    $envio_imp = $order->total_shipping_tax_excl;
                    $data_detalle_shipping['porcentaje_iva'] = round($order->carrier_tax_rate, 2);
                    $data_detalle_shipping['base_no_gravable'] = 0;
                    $data_detalle_shipping['base_cero'] = 0;
                    $data_detalle_shipping['base_gravable'] = round($order->total_shipping_tax_excl, 2);
                } else {
                    $envio_sinimp = $order->total_shipping_tax_excl;
                }

                $detalles[] = $data_detalle_shipping;
            }
            if (!$id_contifico_model) {
                $model_contifo = new ContificoOrderModel();
                $model_contifo->id_cart = $order->id;
                $model_contifo->status = 1;
                $model_contifo->date_add = date("Y-m-d H:i:s");
                $model_contifo->date_upd = date("Y-m-d H:i:s");
                $model_contifo->id_order_contifico = 0;
                $model_contifo->add();

                // $model_contifo->code = $order->id;
            } else {
                $model_contifo = new ContificoOrderModel($id_contifico_model);
            }

            if ($is_active) {
                $razon = $total_discounts / ($sub_total_products_0 + $sub_total_products_12);
                $descuentoIVA = $sub_total_products_12 * $razon;
                $descuentoIVA0 = $sub_total_products_0 * $razon;
                $sub_total_products_12 = $sub_total_products_12 - $descuentoIVA + $envio_imp;;
                $sub_total_products_0 = $sub_total_products_0 - $descuentoIVA0 + $envio_sinimp;
                $tax = round($sub_total_products_12 * 0.15, 2);
                $amount = $sub_total_products_0 + $sub_total_products_12 + $tax;

                $code_sucur = Configuration::get('CONTIFICO_SUCURSAL_CODE');
                $code_facturero = Configuration::get('CONTIFICO_FACT_CODE');
                $type_doc = $customer_contifico['type_doc'] != null ? $customer_contifico['type_doc'] : "cedula";

                if ($descuentoIVA > 0 ||  $descuentoIVA0 > 0) {
                    foreach ($detalles as $key => $detelle) {
                        if ($detelle['base_gravable'] > 0) {
                            $discount_percent = ((float)$descuentoIVA / (float)$detelle['base_gravable'])  * 100;
                            if ($discount_percent < 100) {
                                $base_tmp =   abs($detalles[$key]['base_gravable'] - $descuentoIVA);
                                $detalles[$key]['base_gravable'] = round($base_tmp, 2);
                                $detalles[$key]['porcentaje_descuento'] = round($discount_percent, 2);
                            } else {
                                $descuentoIVA -= $detalles[$key]['base_gravable'];
                                $detalles[$key]['base_gravable'] = 0;
                                $detalles[$key]['porcentaje_descuento'] = 100;
                            }
                        } else {
                            $discount_percent = ((float)$descuentoIVA0 / (float)$detelle['base_no_gravable'])  * 100;
                            if ($discount_percent < 100) {
                                $base_tmp =   abs($detalles[$key]['base_no_gravable'] - $descuentoIVA0);
                                $detalles[$key]['base_no_gravable'] = round($base_tmp, 2);
                                $detalles[$key]['porcentaje_descuento'] = round($discount_percent, 2);
                            } else {
                                $descuentoIVA0 -= $detalles[$key]['base_no_gravable'];
                                $detalles[$key]['base_no_gravable'] = 0;
                                $detalles[$key]['porcentaje_descuento'] = 100;
                            }
                        }
                    }
                }

                $model_contifo->total = round($amount, 2);
                $model_contifo->date_upd = date("Y-m-d H:i:s");;


                $data_factura = array(
                    'pos' => $token,
                    'fecha_emision' => date('d/m/Y'),
                    'tipo_documento' => 'FAC',
                    'estado' =>  'F',
                    "electronico" => true,
                    "autorizacion" => "",
                    "caja_id" => null,
                    "cliente" => [
                        $type_doc => ($type_doc == "ruc" && $customer_contifico['ruc'] != null) ? $customer_contifico['ruc'] : $customer_contifico['cedula'],
                        "tipo" => $customer_contifico['tipo'] != null ? $customer_contifico['tipo'] : "N"
                    ],
                    "descripcion" => "REFERENCIA DE PEDIDO - " . $order->reference,
                    "subtotal_0" => round($sub_total_products_0, 2),
                    "subtotal_12" => round($sub_total_products_12, 2),
                    "iva" =>  round($tax, 2),
                    "servicio" => 0.00,
                    "total" => round($amount, 2),
                    "adicional1" => "",
                    "adicional2" => "",
                    "detalles" => $detalles
                );

                for ($i = 1; $i > 0;) {
                    $code = Configuration::get('CONTIFICO_LAST_CODE') + $i;
                    $code_factura = str_pad($code, 9, "0", STR_PAD_LEFT);
                    $data_factura['documento'] = "$code_sucur-$code_facturero-$code_factura";
                    $factura = ContificoRequest::postCreateFactura($data_factura, $apiKey);
                    if (array_key_exists('mensaje', $factura)) {
                        $model_contifo->message = $factura['mensaje'];
                        $model_contifo->status = 3;

                        if ($factura['mensaje'] == "Documento ya existe") {
                            $i++;
                            continue;
                        } else {
                            $i = 0;
                            PrestaShopLogger::addLog(
                                "Error en creación de documento Contifico Order " . $order->id . " " . $factura['mensaje'],
                                3
                            );

                            PrestaShopLogger::addLog(
                                "Request creacion de factura " . json_encode($data_factura)
                            );


                            break;
                        }
                    } else {
                        $model_contifo->id_order_contifico = $factura['id'];
                        $model_contifo->message = "Factura Contifico creada correctamente Order# $order->id factura # " . $factura['id'];
                        $model_contifo->code = $data_factura['documento'];
                        $model_contifo->status = 2;
                        Configuration::updateValue('CONTIFICO_LAST_CODE', $code);
                        Configuration::updateValue('CONTIFICO_LAST_FACT_' . $order->id, $factura['id']);
                        PrestaShopLogger::addLog("Factura Contifico creada correctamente Order# $order->id factura #"  . $factura['id']);
                        $i = 0;
                        break;
                    }
                }
                $model_contifo->save();
            }
        }
    }
}
