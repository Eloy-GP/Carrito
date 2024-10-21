<?php

echo "INIT EXECUTION<br><br>";


include_once("com/cart/cart.php");
include_once("com/catalog/catalog.php");
include_once("com/users/users.php");

// AddProductToCatalog('250',20,0,'EU');

// DeleteProductQuantityFromCart('5', 17); // Elimina 2 unidades del producto con ID 123 del carrito
//CalculateCartTotal(); // Mostrará y calculará el total del carrito
// AddToCart('20', 1);
//ExistProduct(10);
// RegisterUser('root3', 'pwd123');
if (LoginUser('root3', 'pwd123')) {
    // Usuario inicia sesión, se crea su carrito.
    // DeleteProductQuantityFromCart('5', 17); 
    // Elimina 2 unidades del producto con ID 123 del carrito
    CalculateCartTotal(); // Mostrará y calculará el total del carrito
    AddToCart('20', 1);
}
// DisplayCart(); // Mostrar solo el carrito del usuario logueado
Logout();




//UserRegister('DNI','NOmbre')





?>