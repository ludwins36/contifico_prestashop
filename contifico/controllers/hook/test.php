<?php

// algoritmo que hace los egresos.
// foreach ($almacenPostData as $postAlmacen) {
//     $egreso = ContificoRequest::postEgresoInventarioAlmacen($postAlmacen, $apiKey);
//     if (array_key_exists('mensaje', $egreso)) {
//         PrestaShopLogger::addLog(
//             "Error en egreso Moviento Contifico Order " . $order->id . " " . $egreso['mensaje'],
//             3
//         );
//     }
// }


// algoritmo que guarda el request para crear los ingresos si el producto ya esta creado en prestashop y en contifico
// if ($bodega == '' || ($producContifico['bodega'] != $bodega && !empty($producContifico['bodega']))) {
//     $bodega = !empty($producContifico['bodega']) ?  $producContifico['bodega'] : $bodega_defect;
//     $key_bod++;
//     $almacenPostData[$key_bod] = array(
//         "tipo" => "EGR",
//         "fecha" => date('d/m/Y'),
//         "bodega_id" => $bodega,
//         "detalles" => [
//             [
//                 "producto_id" =>  $producContifico['contifico_id'],
//                 "precio" => number_format($product['unit_price_tax_excl'], 2, ".", ""),
//                 "cantidad" => $product['product_quantity']
//             ]
//         ],
//         "descripcion" => "Egreso por medio de modulo Prestashop."
//     );
// } else {
//     $almacenPostData[$key_bod]['detalles'][] = array(
//         "producto_id" => $producContifico['contifico_id'],
//         "precio" => number_format($product['unit_price_tax_excl'], 2, ".", ""),
//         "cantidad" => $product['product_quantity']
//     );
// }

// algoritmo que guarda el request para crear los ingresos si el producto no esta creado en prestashop y en contifico
// if ($data_query['bodega'] != $bodega) {
//     $key_bod++;
//     $bodega = $data_query['bodega'];
//     $almacenPostData[$key_bod] = array(
//         "tipo" => "EGR",
//         "fecha" => date('d/m/Y'),
//         "bodega_id" => $bodega,
//         "detalles" => [
//             [
//                 "producto_id" => $data_query['contifico_id'],
//                 "precio" => $product['unit_price_tax_excl'],
//                 "cantidad" => $product['product_quantity']
//             ]
//         ],
//         "descripcion" => "Egreso por medio de modulo Prestashop."
//     );
// } else {
//     $almacenPostData[$key_bod]['detalles'][] = array(
//         "producto_id" => $data_query['contifico_id'],
//         "precio" => $product['unit_price_tax_excl'],
//         "cantidad" => $product['product_quantity']
//     );
// }