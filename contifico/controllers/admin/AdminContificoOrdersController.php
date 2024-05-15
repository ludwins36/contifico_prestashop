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

class AdminContificoOrdersController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'contifico_doc';
        $this->identifier = 'id_order';
        $this->className = 'ContificoOrderModel';
        parent::__construct();
        $this->toolbar_title = $this->l('Facturas');
    }

    public function renderList()
    {
        // // AND a.`active` > 0 AND a.`cantidad_stock` > 0
        // $this->_where = 'AND NOT EXISTS (SELECT * FROM `' . _DB_PREFIX_ . 'product` p WHERE p.`reference` = a.`codigo` ) AND NOT EXISTS (SELECT * FROM `' . _DB_PREFIX_ . 'product_attribute` pt WHERE pt.`reference` = a.`codigo` )';
        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'remove_onclick' => true,
                'class' => 'fixed-width-xs',
            ),
            'id_order_contifico' => array(
                'title' => $this->l('Contifico ID'),
                'remove_onclick' => true,
                'havingFilter' => true,
            ),
            'code' => array(
                'title' => $this->l('Codigo Factura'),
                'remove_onclick' => true,
                'havingFilter' => true,
            ),
            'total' => array(
                'title' => $this->l('Precio'),
                'remove_onclick' => true,
                'havingFilter' => true,
                'align' => 'center',
                'type' => 'price',
                'callback' => 'displayFormattedPrice'

            ),
            'message' => array(
                'title' => $this->l('Mensaje'),
                'remove_onclick' => true,
                'havingFilter' => true,
            ),
            'date_add' => array(
                'title' => $this->l('Fecha creación'),
                'remove_onclick' => true,
                'havingFilter' => true,
                'type' => "datetime",

            ),
            'date_upd' => array(
                'title' => $this->l('Fecha Actualización'),
                'type' => "datetime",
                'remove_onclick' => true,
                'havingFilter' => true,
            ),
            'status' => array(
                'title' => $this->l('Estado'),
                'remove_onclick' => true,
                'havingFilter' => true,
                'callback' => 'displayFormattedStatus'


            ),
            'id_cart' => array(
                'title' => $this->l('Acción'),
                'remove_onclick' => true,
                'havingFilter' => true,
                'callback' => 'displayBotonAction',
            ),
        );

        // $this->bulk_actions = array(
        //     'create' => array(
        //         'text' => $this->l('Crear productos seleccionados'),
        //         'confirm' => $this->l('Crear los productos seleccionados en la tienda de prestashop?'),
        //         'icon' => 'icon-refresh'
        //     )
        // );

        return parent::renderList();
    }

    public function displayBotonAction($id_ps, $rowData)
    {
        $this->context->smarty->assign(array(
            'btnLabel' => $this->l('Crear'),
            'status' => $rowData['status'],
            'id_order' => $rowData['id_order'],
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/btn_update.tpl'
        );
    }

    public function displayFormattedStatus($id_ps, $rowData)
    {

        if ($rowData['status'] == 1) {
            return "PENDIENTE";
        } else if ($rowData['status'] == 2) {
            return "COMPLETADO";
        } else if ($rowData['status'] == 1) {
            return "ERROR";
        }
    }

    public function displayFormattedPrice($price)
    {
        return Tools::displayPrice($price);
    }

    public function postProcess()
    {
        parent::postProcess();
        if (Tools::getValue("submitAction") == "reloadDoc") {
            $order_model = new ContificoOrderModel(Tools::getValue("id"));

            if (Configuration::get('CONTIFICO_LAST_FACT_' . $order_model->id_cart)) {
                return false;
            }

            $order = new Order($order_model->id_cart);
            $carrier = new Carrier($order->id_carrier);
            $cart = new Cart($order->id_cart);
            $apiKey = Configuration::get('CONTIFICO_API_KEY');
            $token = Configuration::get('CONTIFICO_API_SECRET');
            $id_customer = $order->id_customer;
            $customer = new Customer($id_customer);
            $total_discounts = $cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS);
            $customer_contifico = ContificoRequest::getCustomerContifico($customer, $apiKey, $order, $token);
            $is_active = false;
            $almacenPostData = array();

            $detalles = array();
            $sub_total_products_12 = 0;
            $sub_total_products_0 = 0;
            $envio_imp = 0;
            $envio_sinimp = 0;
            $shipping_cost = 0;
            $tax_shipping = 0;

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
                } else {
                    $this->context->smarty->assign('error', "EL producto " . $product_name . " no tiene Referencia, sin ese dato no se puede crear el producto en contifico.");
                    return false;
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
                        $this->context->smarty->assign('error', "Error al crear Producto Contifico Costo de Envio " . $carrier->id . " " . $product_carrier['mensaje']);
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

                $order_model->total = round($amount, 2);
                $order_model->date_upd = date("Y-m-d H:i:s");;


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
                        if ($factura['mensaje'] == "Documento ya existe") {
                            $i++;
                            continue;
                        } else {
                            $order_model->message = $factura['mensaje'];
                            $order_model->status = 3;
                            PrestaShopLogger::addLog(
                                "Request creacion de factura " . json_encode($data_factura)
                            );
                            $this->context->smarty->assign('conf', "Error en creación de documento Contifico Order " . $order->id . " " . $factura['mensaje']);
                            break;
                        }
                    } else {
                        $order_model->id_order_contifico = $factura['id'];
                        $order_model->message = "Factura Contifico creada correctamente Order# $order->id factura # " . $factura['id'];
                        $order_model->code = $data_factura['documento'];
                        $order_model->status = 2;
                        Configuration::updateValue('CONTIFICO_LAST_CODE', $code);
                        Configuration::updateValue('CONTIFICO_LAST_FACT_' . $order->id, $factura['id']);
                        $this->context->smarty->assign('conf', $order_model->message);
                        break;
                    }
                }
                $order_model->save();
            }
        }

        // if (Tools::getIsset("contifico_productsBox")) {
        //     $count = 0;
        //     foreach (Tools::getValue("contifico_productsBox") as $key => $id) {
        //         if (ContificoProducts::add_producto($id)) {
        //             $count++;
        //         }
        //     }
        //     $this->context->controller->informations[] = "$count Productos creados correctamente";
        // }
    }
}
