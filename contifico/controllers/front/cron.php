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

class ContificoCronModuleFrontController extends ModuleFrontController
{
    public $todayDate;
    public $todayDateTime;
    public $_request;

    public function __construct()
    {
        parent::__construct();
        // Contifico::required_data("/sql/uninstall.php");
        // Contifico::required_data("/sql/install.php");
        $this->todayDate = date('Y-m-d');
        $this->todayDateTime = date('Y-m-d H:i:s');
        $this->context = Context::getContext();
        $this->_request = new contificoRequest();
    }

    /**
     * Initialize cron controller.
     *
     * @see ModuleFrontController::init()
     */
    public function init()
    {
        parent::init();
        $token = Tools::getValue('token', false);
        if ($token == Configuration::get('CONTIFICO_CRON_TOKEN')) {
            $action = Tools::getValue('action');
            $start_time = microtime(true);
            $this->$action();

            $end_time = microtime(true);
            $duration = $end_time - $start_time;
            $hours = (int)($duration / 60 / 60);
            $minutes = (int)($duration / 60) - $hours * 60;
            $seconds = (int)$duration - $hours * 60 * 60 - $minutes * 60;
            echo "Tiempo empleado para cargar: <strong>" . $hours . ' horas, ' . $minutes . ' minutos y ' . $seconds . ' segundos.</strong>';
            die;
        } else {
            die('Token invalido');
        }
    }

    public function updateDataProdutcs()
    {
        $all_products = ContificoProducts::get_products_contifico();
        $table_product_shop = _DB_PREFIX_ . "product_shop";
        $table_product = _DB_PREFIX_ . "product";
        $table_stock_avaible = _DB_PREFIX_ . "stock_available";
        foreach ($all_products as $product) {
            $reference =  $product["codigo"];
            // update precio
            // obtener el id atributo si existe
            if ($id_product = ContificoRequest::getIdProductByReference($reference)) {
                // NO Es una combinacion
                $qty = (int)$product['cantidad_stock'];
                $query_stock = "UPDATE $table_stock_avaible as st SET st.quantity = $qty WHERE st.id_product  = $id_product AND id_product_attribute = 0;";
                Db::getInstance()->execute($query_stock);

                $price_pro = (float)str_replace(',', '.', $product['precio']);
                $query = "UPDATE $table_product_shop as ps INNER JOIN $table_product as p ON ps.id_product = p.id_product SET ps.price = $price_pro, p.price = $price_pro, p.active = 1, ps.active = 1 WHERE p.reference = '" . $reference  . "';";
                Db::getInstance()->execute($query);
            } else {
                // Es una combinacion
                if ($id_attr = ContificoRequest::getIdByReference($reference)) {
                    $table_conbination = _DB_PREFIX_ . "product_attribute";
                    $comb = new Combination($id_attr);
                    $price_pro = (float)str_replace(',', '.', $product['precio']);
                    $qty = (int)$product['cantidad_stock'];

                    $comb->price = $price_pro;
                    $comb->quantity = $qty;
                    $comb->save();
                    $query_stock = "UPDATE $table_stock_avaible as st SET st.quantity = $qty WHERE st.id_product_attribute = $id_attr;";
                    Db::getInstance()->execute($query_stock);
                }
            }
        }
    }

    public function getAttributeByRe($reference)
    {
    }

    public function setDataCatalgContifico()
    {

        $all_products = $this->_request->getProductsContifico(Configuration::get('CONTIFICO_API_KEY'));
        if (count($all_products) > 0) {
            if (count($all_products) > 100) {
                $limits = array_chunk($all_products, 100);
                set_time_limit(240);
                foreach ($limits as $key => $products) {
                    $count = count($products) - 1;
                    $query = "INSERT INTO `" . _DB_PREFIX_ . "contifico_products` (`codigo`,`contifico_id`, `imagen`, `description`, `nombre`, `variantes`,`detalle_variantes`, `categoryId`, `cuenta_costo_id`, `fecha_creacion`, `pvp_manual`, `tipo_producto`, `cantidad_stock`, `precio`) VALUES ";

                    foreach ($products as $key_p => $product) {
                        $variantes = json_encode($product->variantes);
                        $detalle_variantes = json_encode($product->detalle_variantes);
                        $nombre =  pSQL(trim(str_replace("'", "", $product->nombre)));
                        $images = $product->imagen;
                        $descripcion =  pSQL(trim(str_replace("'", "", $product->descripcion)));
                        $query .= "('$product->codigo','$product->id', '$images','$descripcion','$nombre','$variantes','$detalle_variantes','$product->categoria_id','$product->cuenta_costo_id','$product->fecha_creacion','$product->pvp_manual','$product->tipo_producto','$product->cantidad_stock','$product->pvp1')";
                        if ($count != $key_p) {
                            $query .= ",";
                        }
                    }
                    $query .= "ON DUPLICATE KEY UPDATE codigo=VALUES(codigo),imagen=VALUES(imagen),description=VALUES(description),nombre=VALUES(nombre),variantes=VALUES(variantes),detalle_variantes=VALUES(detalle_variantes),categoryId=VALUES(categoryId),cuenta_costo_id=VALUES(cuenta_costo_id),fecha_creacion=VALUES(fecha_creacion),pvp_manual=VALUES(pvp_manual),tipo_producto=VALUES(tipo_producto),cantidad_stock=VALUES(cantidad_stock),precio=VALUES(precio);";
                    try {
                        Db::getInstance()->execute($query);
                        // var_dump(Db::getInstance()->execute($query));
                    } catch (Exception $e) {
                        // add log
                        print_r($e->getMessage());
                        return;
                    }
                }
            } else {
                $count = count($all_products) - 1;
                $query = "INSERT INTO `" . _DB_PREFIX_ . "contifico_products` (`codigo`,`contifico_id`, `imagen`, `description`, `nombre`, `variantes`,`detalle_variantes`, `categoryId`, `cuenta_costo_id`, `fecha_creacion`, `pvp_manual`, `tipo_producto`, `cantidad_stock`, `precio`) VALUES ";
                foreach ($all_products as $key_p => $product) {
                    $variantes = json_encode($product->variantes);
                    $detalle_variantes = json_encode($product->detalle_variantes);
                    $nombre =  pSQL(trim(str_replace("'", "", $product->nombre)));
                    $images = $product->imagen;
                    $descripcion =  pSQL(trim(str_replace("'", "", $product->descripcion)));
                    $query .= "('$product->codigo', '$product->id', '$images','$descripcion','$nombre','$variantes','$detalle_variantes','$product->categoria_id','$product->cuenta_costo_id','$product->fecha_creacion','$product->pvp_manual','$product->tipo_producto','$product->cantidad_stock','$product->pvp1')";
                    if ($count != $key_p) {
                        $query .= ",";
                    }
                }
                $query .= "ON DUPLICATE KEY UPDATE imagen=VALUES(imagen),description=VALUES(description),nombre=VALUES(nombre),variantes=VALUES(variantes),detalle_variantes=VALUES(detalle_variantes),categoryId=VALUES(categoryId),cuenta_costo_id=VALUES(cuenta_costo_id),fecha_creacion=VALUES(fecha_creacion),pvp_manual=VALUES(pvp_manual),tipo_producto=VALUES(tipo_producto),cantidad_stock=VALUES(cantidad_stock),precio=VALUES(precio);";
                try {
                    Db::getInstance()->execute($query);
                } catch (Exception $e) {
                    // add log
                    print_r($e->getMessage());
                    return;
                }
            }
        } else {
            echo "Ningun producto se descargo desde contifico <br>";
        }
    }
}