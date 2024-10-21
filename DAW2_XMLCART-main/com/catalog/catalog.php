<?php

$catalog_file = 'xmldb/catalog.xml';

////////////xmldb/catalog.xml//////////////////////////////////////////

// Mostrar el catálogo completo
function DisplayCatalog() {
    global $catalog_file;

    if (file_exists($catalog_file)) {
        $catalog = simplexml_load_file($catalog_file);

        echo "<h2>Catálogo de Productos</h2>";
        foreach ($catalog->product_item as $product) {
            echo "ID: " . $product->id_product . "<br>";
            echo "Cantidad: " . $product->quantity . "<br>";
            echo "Precio: " . $product->price_item->price . " " . $product->price_item->currency . "<br>";
            echo "<hr>";
        }
    } else {
        echo "No hay productos en el catálogo.<br>";
    }
}

//////////////////////////////////////////////////////
// Añadir un nuevo producto al catálogo
function AddProductToCatalog($id_prod, $quantity, $price, $currency) {
    global $catalog_file;

    // Obtener el catálogo actual
    $catalog = GetCatalog();

    // Verificar si el producto ya existe
    if (ExistProductCatalog($id_prod)) {
        // Si existe, actualizar la cantidad
        UpdateCatalogProductQuantity($id_prod, $quantity);
    } else {
        // Añadir un nuevo producto
        $product = $catalog->addChild('product_item');
        $product->addChild('id_product', $id_prod);
        $product->addChild('quantity', $quantity);

        // Estructura del precio en el catálogo
        $price_item = $product->addChild('price_item');
        $price_item->addChild('price', $price);
        $price_item->addChild('currency', $currency);

        // Guardar el catálogo actualizado
        $catalog->asXML($catalog_file);

        echo "Producto con ID $id_prod añadido al catálogo.<br>";
    }

}

//////////////////////////////////////////////////////
// Obtener el catálogo
function GetCatalog() {
    global $catalog_file;

    // Si el archivo existe, lo cargamos; si no, creamos un nuevo catálogo vacío
    if (file_exists($catalog_file)) {
        $catalog = simplexml_load_file($catalog_file);
    } else {
        $catalog = new SimpleXMLElement('<catalog></catalog>');
    }

    return $catalog;
}

//////////////////////////////////////////////////////
// Verificar si el producto existe
function ExistProductCatalog($id_prod) {
    global $catalog_file;

    $catalog = GetCatalog();

    // Buscar el producto en el catálogo
    foreach ($catalog->product_item as $product) {
        if ((string)$product->id_product == (string)$id_prod) {
            return true; // Producto existe
        }
    }

    return false; // Producto no existe
}

//////////////////////////////////////////////////////
// Actualizar la cantidad de un producto en el catálogo
function UpdateCatalogProductQuantity($id_prod, $quantity) {
    global $catalog_file;

    // Obtener el catálogo actual
    $catalog = GetCatalog();

    // Buscar el producto en el catálogo y actualizar su cantidad
    foreach ($catalog->product_item as $product) {
        if ((string)$product->id_product == (string)$id_prod) {
            // Actualizar la cantidad si el producto ya existe
            $product->quantity = (int)$product->quantity + $quantity;
            echo "Cantidad actualizada: $product->quantity para el producto $id_prod.<br>";
            break; // Salir del bucle después de encontrar el producto
        }
    }

    // Guardar el catálogo actualizado
    $catalog->asXML($catalog_file);
}

//////////////////////////////////////////////////////
// Reducir el stock de un producto en el catálogo
function ReduceStockCatalog($id_prod, $quantity) {
    global $catalog_file;

    // Obtener el catálogo actual
    $catalog = GetCatalog();

    // Buscar el producto y reducir su stock
    foreach ($catalog->product_item as $product) {
        if ((string)$product->id_product == (string)$id_prod) {
            $new_quantity = (int)$product->quantity - (int)$quantity;
            if ($new_quantity >= 0) {
                $product->quantity = $new_quantity;
                echo "Stock reducido para el producto $id_prod. Nueva cantidad: $new_quantity.<br>";
            } else {
                echo "No hay suficiente stock para el producto $id_prod.<br>";
            }
            break; // Salir del bucle después de encontrar el producto
        }
    }

    // Guardar el catálogo actualizado
    $catalog->asXML($catalog_file);
}

//////////////////////////////////////////////////////
// Eliminar un producto del catálogo
function DeleteProductFromCatalog($id_prod) {
    global $catalog_file;

    // Obtener el catálogo
    $catalog = GetCatalog();

    // Encontrar y eliminar el producto del catálogo
    $index = 0;
    foreach ($catalog->product_item as $product) {
        if ((string)$product->id_product == (string)$id_prod) {
            unset($catalog->product_item[$index]); // Eliminar el producto
            echo "Producto $id_prod eliminado del catálogo.<br>";
            break;
        }
        $index++;
    }

    // Guardar el catálogo actualizado
    $catalog->asXML($catalog_file);
}
//////////////////////////////////////////////////////
function GetProductPriceFromCatalog($id_prod) {
    global $catalog_file;

    // Si el archivo del catálogo existe, lo cargamos
    if (file_exists($catalog_file)) {
        $catalog = simplexml_load_file($catalog_file);

        // Buscar el producto en el catálogo
        foreach ($catalog->product_item as $product) {
            if ((string)$product->id_product == (string)$id_prod) {
                return (float)$product->price_item->price; // Devolver el precio
            }
        }
    }

    echo "El producto $id_prod no se encontró en el catálogo.<br>";
    return null; // Si no se encuentra el producto, devuelve null
}



?>
