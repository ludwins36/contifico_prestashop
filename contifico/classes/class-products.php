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

class ContificoProducts
{
    public static function checkproduct($reference)
    {
        $sqlid_product = "select * from " . _DB_PREFIX_ . "product where reference = '" . $reference . "'";
        $rowsidp = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sqlid_product);
        if (!$rowsidp) {
            $sqlid_product = "select * from " . _DB_PREFIX_ . "product_attribute where reference = '" . $reference . "'";
            $rowsidp = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sqlid_product);
            return $rowsidp;
        }
        return $rowsidp;
    }

    public static function get_product_contifico($contifico_id)
    {
        $sqlid_product = "select * from " . _DB_PREFIX_ . "contifico_products where codigo = '" . $contifico_id . "'";
        $rowsidp = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sqlid_product);
        return $rowsidp;
    }

    public static function get_products_contifico()
    {

        $sqlid_product = "select * from " . _DB_PREFIX_ . "contifico_products a WHERE EXISTS (SELECT * FROM `" . _DB_PREFIX_ . "product` p WHERE p.`reference` = a.`codigo` ) OR EXISTS (SELECT * FROM `" . _DB_PREFIX_ . "product_attribute` pt WHERE pt.`reference` = a.`codigo` ) AND a.codigo != ''";
        $rowsidp = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sqlid_product);
        return $rowsidp;
    }

    public static function add_images($id_product, $images)
    {
        $id_shop = Configuration::get('PS_SHOP_DEFAULT');
        // $id_lang = Configuration::get('PS_LANG_DEFAULT');
        foreach ($images as $key => $image) {
            $img = '';
            if ($key < 5) {
                $img = new Image();
                $img->id_product = $id_product;
                $img->position = Image::getHighestPosition($id_product) + 1;

                if (($img->validateFields(false, true)) === true && ($img->validateFieldsLang(false, true)) === true && $img->add()) {
                    $copy = self::copyImg($id_product, $img->id, $image['url'], 'products', true);
                    if (!$copy) {
                        $img->delete();
                    }
                }
            }
        }
    }

    public static function copyImg($id_entity, $id_image = null, $url, $entity = 'products')
    {
        //añadimos la imagen que nos envia el codigo desde intcomex al prestashop
        $tmpfile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
        $watermark_types = explode(',', Configuration::get('WATERMARK_TYPES'));
        $image_obj = new Image($id_image);
        $path = $image_obj->getPathForCreation();
        $url = str_replace(' ', '%20', trim($url));
        if (!ImageManager::checkImageMemoryLimit($url)) {
            return false;
        }

        if (@copy($url, $tmpfile)) {
            ImageManager::resize($tmpfile, $path . '.jpg');
            $images_types = ImageType::getImagesTypes($entity);
            foreach ($images_types as $image_type) {
                ImageManager::resize($tmpfile, $path . '-' . stripslashes($image_type['name']) . '.jpg', $image_type['width'], $image_type['height']);
            }

            if (in_array($image_type['id_image_type'], $watermark_types)) {
                Hook::exec('actionWatermark', array('id_image' => $id_image, 'id_product' => $id_entity));
            }
        } else {
            unlink($tmpfile);
            return false;
        }
        unlink($tmpfile);
        return true;
    }

    public static function seo_friendly_url($string)
    {
        //De nombre del producto a url, limpieza
        $string = str_replace(array('[\', \']'), '', $string);
        $string = preg_replace('/\[.*\]/U', '', $string);
        $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string);
        $string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/'), '-', $string);
        return strtolower(trim($string, '-'));
    }

    public static function check_option($nameop, $nameg)
    {
        //Comprobamos el atributo/opcion del producto
        $id_lang = Configuration::get('PS_LANG_DEFAULT');
        $atributos = Attribute::getAttributes($id_lang, $not_null = false);
        $existe = false;
        if (count($atributos) > 0) {
            foreach ($atributos as $atributo) {

                if ($atributo['name'] == $nameop) {
                    $idattt = $atributo['id_attribute'];
                    $existe = true;
                    break;
                } else {
                    $existe = false;
                }
            }
        }
        if (!$existe) {
            //Si no existe lo añadimos
            $idattt = self::addatt($nameop, $nameg);
        }

        //devolvemos el id del atributo, que ya existia o añadido
        return $idattt;
    }

    public static function addatt($nameop, $nameg)
    {
        // Añadimos el atributo, comprobanco que el grupo de atributos exista o lo creamos
        $id_lang = Configuration::get('PS_LANG_DEFAULT');
        $idiomas = Language::getLanguages($active = true, $id_shop = false);
        $nameg = 'tamaño';
        $existegrupo = false;
        $gruposatt = AttributeGroup::getAttributesGroups($id_lang);
        foreach ($gruposatt as $grouptt) {

            if ($grouptt['name'] == $nameg) {
                $idgroupr = $grouptt["id_attribute_group"];
                $existegrupo = true;
                break;
            } else {
                $existegrupo = false;
            }
        }
        if (!$existegrupo) {
            //Si no existe el grupo lo añadimos
            $newGroup = new AttributeGroup();

            foreach ($idiomas as $idioma) {
                $id_lang = $idioma['id_lang'];
                $newGroup->name[$id_lang] = (string)$nameg;
                $newGroup->public_name[$id_lang] = (string)$nameg;
            }



            $newGroup->group_type = 'select';
            $newGroup->add();
            //una vez añadido lo asignamos al atributo que añadimos - Si no existe el grupo no debe existir el atributo
            $idgroupr = $newGroup->id;
            $newAttribute = new Attribute();
            foreach ($idiomas as $idioma) {
                $id_lang = $idioma['id_lang'];
                $newAttribute->name[$id_lang] = $nameop;
            }

            $newAttribute->id_attribute_group = $idgroupr;
            $newAttribute->add();
            $idattt = $newAttribute->id;
        } else {
            //Al existir el grupo añadimos el atributo
            $newAttribute = new Attribute();
            foreach ($idiomas as $idioma) {
                $id_lang = $idioma['id_lang'];
                $newAttribute->name[$id_lang] = $nameop;
            }

            $newAttribute->id_attribute_group = $idgroupr;
            $newAttribute->add();
            $idattt = $newAttribute->id;
        }

        return $idattt;
    }

    public static function add_producto($reference)
    {
        $isexist = self::checkproduct($reference);
        $catpro = 2;
        // Ya tenemos verificado si el producto existe, por lo que añadimos un if     
        if (!$isexist) {
            // principio de Add
            $producto = self::get_product_contifico($reference);
            $name = substr($producto['nombre'], 0, 128);
            $id_lang = Configuration::get('PS_LANG_DEFAULT'); // buscamos el ID del idioma
            // $shops = Shop::getShops($active = true, $id_shop_group = null, $get_as_list_id = false); // vemos las tiendas que tiene el prestashop
            $product = new Product(); //añadimos un nuevo producto
            $product->name = array($id_lang =>  $name);

            $seo = self::seo_friendly_url($name); // función externa para convertir el nombre en formato URL 
            $product->link_rewrite = array($id_lang =>  $seo);

            $images = [];
            if ($producto['imagen'] != '' && $producto['imagen'] != '""') {
                $images[] = $producto['imagen'];
            }

            if ($product->add()) {
                //seguimos con el código aqui
                $id_product = $product->id;
                $product->reference = $reference; //añadimos referencia
                $product->ean13 = ""; // añadimos ean13
                $product->weight = 1; // añadimos peso
                $product->price = (float)str_replace(',', '.', $producto['precio']); //añadimos precio cambiando la , por un .

                $product->addToCategories(array($catpro));
                $product->id_category = $catpro;
                $product->id_category_default = $catpro;
                $descp = $producto['description'] != '' ? $producto['description'] : 'Producto sin titulo.';
                $product->description_short = $descp;
                $product->description = $descp;

                $product->advanced_stock_management = 0;
                $product->show_price = 1;
                $product->on_sale = 0;
                $product->unit_price = (float)str_replace(',', '.', $producto['price']);;
                $product->weight = (float)(1);
                $product->redirect_type = '404';
                $product->minimal_quantity = 1;
                $product->available_for_order = 1;
                $product->online_only = 1;
                $product->active = 1; // dejamos el producto NO activado, mejor activar despues manualmente
                $product->save();  // Guardamos los datos del producto y continuamos
                StockAvailable::updateQuantity($id_product, 0, $producto['cantidad_stock']);
                if (count($images) > 0) {
                    self::add_images($id_product, $images);
                }

                return true;
            }
        }

        return false;
    }
}