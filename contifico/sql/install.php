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
$sql = array();
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'contifico_products` (
    `contifico_id` VARCHAR(255) PRIMARY KEY,
    `codigo` VARCHAR(200) NOT NULL,
    `product_id` VARCHAR(255) NOT NULL,
    `imagen` TEXT NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `nombre` TEXT NULL DEFAULT NULL,
    `variantes` TEXT NULL DEFAULT NULL,
    `detalle_variantes` TEXT NULL DEFAULT NULL,
    `categoryId` VARCHAR(200)NULL DEFAULT NULL,
    `cuenta_costo_id` VARCHAR(200)NULL DEFAULT NULL,
    `fecha_creacion` VARCHAR(200)NULL DEFAULT NULL,
    `pvp_manual` VARCHAR(200)NULL DEFAULT NULL,
    `tipo` VARCHAR(200)NULL DEFAULT NULL,
    `tipo_producto` VARCHAR(200)NULL DEFAULT NULL,
    `cantidad_stock` VARCHAR(200)NULL DEFAULT NULL,
    `minimo` VARCHAR(200)NULL DEFAULT NULL,
    `bodega` VARCHAR(200)NULL DEFAULT NULL,
    `instock` int(200)NULL DEFAULT NULL,
    `active` tinyint(4) NOT NULL,
    `precio` float NULL DEFAULT NULL,
    `category` VARCHAR(200)NULL DEFAULT NULL
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';



$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'contifico_doc` (
    `id_order` int(100) NOT NULL AUTO_INCREMENT,
    `id_cart` int(100)NULL DEFAULT NULL,
    `code` int(100)NULL DEFAULT NULL,
    `id_order_contifico` VARCHAR(200) NULL DEFAULT NULL,
    `total` FLOAT NULL DEFAULT NULL,
    `currency` VARCHAR(10)NULL DEFAULT NULL,
    `status` VARCHAR(200)NULL DEFAULT NULL,
    `message` TEXT NULL DEFAULT NULL,
    `date_add` DATETIME NULL DEFAULT NULL,
    `date_upd` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id_order`)
    
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';


$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'customers_contifico` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `ruc`  VARCHAR(200) NOT NULL,
    `cedula`  VARCHAR(200) NOT NULL,
    `customer_id`  VARCHAR(200)NULL DEFAULT NULL,
    `tipo`  VARCHAR(200)NULL DEFAULT NULL,
    `type_doc`  VARCHAR(200)NULL DEFAULT NULL,
    `email`  VARCHAR(200)NULL DEFAULT NULL,
    `telefonos`  VARCHAR(200)NULL DEFAULT NULL,
    `razon_social`  VARCHAR(200)NULL DEFAULT NULL,
    `direccion`  VARCHAR(200)NULL DEFAULT NULL,
    `date_creation` DATETIME NULL DEFAULT NULL,
    `date_modification` DATETIME NULL DEFAULT NULL,
    `contifico_id` TEXT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
