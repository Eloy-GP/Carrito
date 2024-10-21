<?php

// Obtiene el archivo de carrito específico para el usuario actual
function GetUserCartFile() {
    if (!isset($_SESSION['username'])) {
        echo "No has iniciado sesión.<br>";
        return null;
    }
    $username = $_SESSION['username'];
    return "xmldb/cart_{$username}.xml";
}

// Obtener el carrito del usuario actual
function GetCart() {
    $cart_file = GetUserCartFile();
    if ($cart_file === null) return null;

    // Si el archivo existe, lo cargamos; si no, creamos un nuevo carrito vacío
    if (file_exists($cart_file)) {
        $cart = simplexml_load_file($cart_file);
    } else {
        $cart = new SimpleXMLElement('<cart></cart>');
    }

    return $cart;
}

// Guardar el carrito del usuario actual
function SaveCart($cart) {
    $cart_file = GetUserCartFile();
    if ($cart_file === null) return;

    // Guardar el carrito en el archivo del usuario
    $cart->asXML($cart_file);
}

// Añadir un producto al carrito
// Añadir un producto al carrito
function AddToCart($id_prod, $quantity) {
    echo "Añadir al carrito <br>";
    echo "$id_prod <br>";

    // Obtener el carrito actual del usuario
    $cart = GetCart();
    if ($cart === null) return;

    // Obtener el precio del catálogo
    $price = GetProductPriceFromCatalog($id_prod);
    if ($price === null) {
        echo "No se pudo obtener el precio del producto $id_prod.<br>";
        return;
    }

    // Si el producto ya existe en el carrito, actualizamos la cantidad
    if (ExistProduct($id_prod)) {
        UpdateProductQuantity($id_prod, $quantity);
    } else {
        // Si no existe, lo añadimos como nuevo producto
        _ExecuteAddToCart($id_prod, $quantity, $price);
    }

    // Guardar el carrito actualizado
    SaveCart($cart);
}

// Ejecuta la acción de añadir un producto al carrito
function _ExecuteAddToCart($id_prod, $quantity, $price) {
    // Obtener el carrito actual del usuario
    $cart = GetCart();
    if ($cart === null) return;

    // Añadir un nuevo producto al carrito
    $item = $cart->addChild('product_item');
    $item->addChild('id_product', $id_prod);
    $item->addChild('quantity', $quantity);

    // Estructura del precio en el carrito
    $item_price = $item->addChild('price_item');
    $item_price->addChild('price', $price); // Añadimos el precio del catálogo al producto
    $item_price->addChild('currency', 'EU');

    echo "$id_prod añadido al carrito con precio $price.<br>";

    // Guardar el carrito actualizado
    SaveCart($cart);
}

// Verificar si el producto ya está en el carrito
function ExistProduct($id_prod) {
    $cart = GetCart();
    if ($cart === null) return false;

    // Buscar el producto en el carrito
    foreach ($cart->product_item as $product) {
        if ((string)$product->id_product == (string)$id_prod) {
            return true; // El producto ya existe
        }
    }
    return false; // El producto no existe
}

// Actualizar la cantidad de un producto existente en el carrito
function UpdateProductQuantity($id_prod, $quantity) {
    $cart = GetCart();
    if ($cart === null) return;

    // Buscar el producto en el carrito y actualizar su cantidad
    foreach ($cart->product_item as $product) {
        if ((string)$product->id_product == (string)$id_prod) {
            $product->quantity = (int)$product->quantity + $quantity;
            echo "Cantidad actualizada para el producto $id_prod.<br>";
        }
    }

    // Guardar el carrito actualizado
    SaveCart($cart);
}

// Eliminar un producto del carrito
function DeleteFromCart($id_prod, $quantity = null) {
    $cart = GetCart();
    if ($cart === null) return;

    // Buscar el producto en el carrito y eliminar o reducir la cantidad
    foreach ($cart->product_item as $index => $product) {
        if ((string)$product->id_product == (string)$id_prod) {
            if ($quantity === null || $quantity >= (int)$product->quantity) {
                unset($cart->product_item[$index]); // Eliminar producto si no se especifica cantidad o si la cantidad es mayor o igual a la existente
                echo "Producto $id_prod eliminado del carrito.<br>";
            } else {
                $product->quantity = (int)$product->quantity - $quantity;
                echo "Cantidad actualizada para el producto $id_prod. Nueva cantidad: $product->quantity.<br>";
            }
            break;
        }
    }

    // Guardar el carrito actualizado
    SaveCart($cart);
}

// Calcular el total y subtotal del carrito
function CalculateCartTotal() {
    $cart = GetCart();
    if ($cart === null) return;

    $subtotal = 0;
    $tax_rate = 0.21; // IVA del 21%

    // Calcular el subtotal sumando los precios de los productos
    foreach ($cart->product_item as $product) {
        $price = (float)$product->price_item->price;
        $quantity = (int)$product->quantity;
        $subtotal += $price * $quantity;
    }

    // Calcular el total con IVA
    $total = $subtotal * (1 + $tax_rate);

    echo "Subtotal: €" . number_format($subtotal, 2) . "<br>";
    echo "Total (con IVA): €" . number_format($total, 2) . "<br>";
}

?>
