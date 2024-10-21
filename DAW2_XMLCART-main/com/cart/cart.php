<?php

$cart_file = 'xmldb/cart.xml';
$catalog_file = 'xmldb/catalog.xml'; // Archivo con el stock del catálogo

////////////xmldb/cart.xml//////////////////////////////////////////
function AddToCart($id_prod, $quantity) {
    echo "AddToCart <br>";
    echo "$id_prod <br>";

    // Primero, verificamos si hay suficiente stock en el catálogo
    if (!CheckStock($id_prod, $quantity)) {
        echo "No hay suficiente stock para el producto $id_prod.<br>";
        return;
    }

    // Obtener el precio del producto desde el catálogo
    $price = GetProductPriceFromCatalog($id_prod);
    if ($price === null) {
        echo "El producto $id_prod no tiene un precio en el catálogo.<br>";
        return;
    }

    // Si el producto ya existe en el carrito, actualizamos la cantidad
    if (ExistProduct($id_prod)) {
        UpdateProductQuantity($id_prod, $quantity);
    } else {
        // Si no existe, lo añadimos como nuevo producto
        _ExecuteAddToCart($id_prod, $quantity, $price);
    }

    // Después de añadir al carrito, reducimos el stock en el catálogo
    ReduceStock($id_prod, $quantity);
}


//////////////////////////////////////////////////////
function _ExecuteAddToCart($id_prod, $quantity, $price) {
    global $cart_file;
    $iva_percentage = 21; // Definir el porcentaje de IVA, por ejemplo, 21%

    // Calcular el subtotal y el total con IVA
    $subtotal = $price; // Precio sin IVA
    $iva_amount = ($subtotal * $iva_percentage) / 100; // IVA en función del subtotal
    $total_with_iva = $subtotal + $iva_amount; // Precio con IVA incluido

    // Obtener el carrito actual
    $cart = GetCart();
  
    // Añadir un nuevo producto al carrito
    $item = $cart->addChild('product_item');
    $item->addChild('id_product', $id_prod);
    $item->addChild('quantity', $quantity);

    // Añadir los precios al carrito (subtotal y total con IVA)
    $item_price = $item->addChild('price_item');
    $item_price->addChild('subtotal', number_format($subtotal, 2)); // Subtotal sin IVA
    $item_price->addChild('total_with_iva', number_format($total_with_iva, 2)); // Total con IVA
    $item_price->addChild('currency', 'EU');

    // Guardar el carrito actualizado
    $cart->asXML($cart_file);

    echo "$id_prod añadido al carrito con un subtotal de $subtotal EUR y total con IVA de $total_with_iva EUR.<br>";
}



//////////////////////////////////////////////////////
function UpdateProductQuantity($id_prod, $quantity) {
    global $cart_file;

    // Obtener el carrito actual
    $cart = GetCart();

    // Buscar el producto en el carrito y actualizar su cantidad
    foreach ($cart->product_item as $product) {
        if ((string)$product->id_product == (string)$id_prod) {
            // Actualizar la cantidad si el producto ya existe
            $product->quantity = (int)$product->quantity + $quantity;
            echo "Cantidad actualizada: $product->quantity para el producto $id_prod.<br>";
        }
    }

    // Guardar el carrito actualizado
    $cart->asXML($cart_file);
}

//////////////////////////////////////////////////////
function GetCart() {
    global $cart_file;

    // Si el archivo existe, lo cargamos; si no, creamos un nuevo carrito vacío
    if (file_exists($cart_file)) {
        echo 'El archivo del carrito existe <br>';
        $cart = simplexml_load_file($cart_file);
    } else {
        $cart = new SimpleXMLElement('<cart></cart>');
    }

    return $cart;
}

//////////////////////////////////////////////////////
function ExistProduct($id_prod) {
    global $cart_file;

    // Obtener el carrito actual
    $cart = GetCart();

    // Verificar si el producto existe en el carrito
    foreach ($cart->product_item as $product) {
        if ((string)$product->id_product == (string)$id_prod) {
            echo "$id_prod ya existe en el carrito.<br>";
            return true; // El producto ya existe
        }
    }

    echo "$id_prod no encontrado en el carrito.<br>";
    return false; // El producto no existe
}

//////////////////////////////////////////////////////
// Función para verificar el stock en catalog.xml
function CheckStock($id_prod, $quantity) {
    global $catalog_file;

    // Si el archivo del catálogo existe, lo cargamos
    if (file_exists($catalog_file)) {
        $catalog = simplexml_load_file($catalog_file);

        // Buscar el producto en el catálogo
        foreach ($catalog->product_item as $product) {
            if ((string)$product->id_product == (string)$id_prod) {
                $available_stock = (int)$product->quantity; // Usamos quantity aquí
                if ($available_stock >= $quantity) {
                    echo "Stock disponible para el producto $id_prod: $available_stock.<br>";
                    return true;
                } else {
                    echo "Stock insuficiente para el producto $id_prod. Stock disponible: $available_stock.<br>";
                    return false;
                }
            }
        }
    }

    echo "El producto $id_prod no se encontró en el catálogo.<br>";
    return false;
}

//////////////////////////////////////////////////////
// Función para reducir el stock en catalog.xml
function ReduceStock($id_prod, $quantity) {
    global $catalog_file;

    // Cargar el catálogo
    if (file_exists($catalog_file)) {
        $catalog = simplexml_load_file($catalog_file);

        // Buscar el producto en el catálogo y reducir su stock
        foreach ($catalog->product_item as $product) {
            if ((string)$product->id_product == (string)$id_prod) {
                $product->quantity = (int)$product->quantity - $quantity;
                echo "Nuevo stock para el producto $id_prod: $product->quantity.<br>";
                break;
            }
        }

        // Guardar el catálogo actualizado
        $catalog->asXML($catalog_file);
    }
}
function DeleteProductQuantityFromCart($id_prod, $quantity_to_remove) {
    global $cart_file;

    // Obtener el carrito actual
    $cart = GetCart();

    // Buscar el producto en el carrito
    foreach ($cart->product_item as $product) {
        if ((string)$product->id_product == (string)$id_prod) {
            $current_quantity = (int)$product->quantity;

            // Verificar si la cantidad actual es mayor que la cantidad a eliminar
            if ($current_quantity > $quantity_to_remove) {
                // Reducir la cantidad
                $product->quantity = $current_quantity - $quantity_to_remove;
                echo "Cantidad reducida: " . $product->quantity . " para el producto $id_prod.<br>";
            } else {
                // Si la cantidad a eliminar es mayor o igual, eliminar el producto
                $index = 0;
                foreach ($cart->product_item as $p) {
                    if ((string)$p->id_product == (string)$id_prod) {
                        unset($cart->product_item[$index]);
                        echo "Producto $id_prod eliminado del carrito.<br>";
                        
                    }
                    $index++;
                }
            }

            // Guardar el carrito actualizado
            $cart->asXML($cart_file);
            return;
        }
    }

    // Si el producto no se encuentra
    echo "Producto $id_prod no encontrado en el carrito.<br>";
}
function CalculateCartTotal() {
    global $cart_file;

    // Obtener el carrito actual
    $cart = GetCart();
    $subtotal_total = 0;
    $total_with_iva_total = 0;

    // Recorrer los productos del carrito
    foreach ($cart->product_item as $product) {
        $quantity = (int)$product->quantity;
        $subtotal = (float)$product->price_item->subtotal;
        $total_with_iva = (float)$product->price_item->total_with_iva;

        // Sumar los subtotales y totales con IVA
        $subtotal_total += $quantity * $subtotal;
        $total_with_iva_total += $quantity * $total_with_iva;
    }

    // Mostrar los totales
    echo "Subtotal del carrito (sin IVA): " . number_format($subtotal_total, 2) . " EUR<br>";
    echo "Total del carrito (con IVA): " . number_format($total_with_iva_total, 2) . " EUR<br>";

    return [
        'subtotal' => $subtotal_total,
        'total_with_iva' => $total_with_iva_total
    ];
}




?>