<?php

// Archivo XML para almacenar los usuarios
$users_file = 'xmldb/users.xml';

// Función para registrar un nuevo usuario
function RegisterUser($username, $password) {
    global $users_file;

    // Cargar o crear el archivo de usuarios
    if (file_exists($users_file)) {
        $users = simplexml_load_file($users_file);
    } else {
        $users = new SimpleXMLElement('<users></users>');
    }

    // Verificar si el nombre de usuario ya existe
    foreach ($users->user as $user) {
        if ((string)$user->username == (string)$username) {
            echo "El usuario ya existe.<br>";
            return false;
        }
    }

    // Añadir un nuevo usuario
    $new_user = $users->addChild('user');
    $new_user->addChild('username', $username);
    $new_user->addChild('password', password_hash($password, PASSWORD_DEFAULT)); // Almacenar la contraseña encriptada

    // Guardar el archivo actualizado
    $users->asXML($users_file);

    echo "Usuario registrado exitosamente.<br>";
    return true;
}

// Función para iniciar sesión (validar credenciales)
function LoginUser($username, $password) {
    global $users_file;

    if (file_exists($users_file)) {
        $users = simplexml_load_file($users_file);

        // Buscar el usuario
        foreach ($users->user as $user) {
            if ((string)$user->username == (string)$username) {
                // Verificar la contraseña
                if (password_verify($password, $user->password)) {
                    // Iniciar sesión
                    session_start();
                    $_SESSION['username'] = $username;

                    echo "Inicio de sesión exitoso.<br>";
                    return true;
                } else {
                    echo "Contraseña incorrecta.<br>";
                    return false;
                }
            }
        }
    }

    echo "Usuario no encontrado.<br>";
    return false;
}

// Función para cerrar sesión
function Logout() {
    
    session_unset(); // Limpiar todas las variables de sesión
    session_destroy(); // Destruir la sesión
    echo "Has cerrado sesión.<br>";
}

?>
