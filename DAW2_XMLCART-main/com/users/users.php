<?php

$users_file = 'xmldb/users.xml';

function RegisterUser($username, $password) {
    global $users_file;

    if (file_exists($users_file)) {
        $users = simplexml_load_file($users_file);
    } else {
        $users = new SimpleXMLElement('<users></users>');
    }

    foreach ($users->user as $user) {
        if ((string)$user->username == (string)$username) {
            echo "El usuario ya existe.<br>";
            return false;
        }
    }

    $new_user = $users->addChild('user');
    $new_user->addChild('username', $username);
    $new_user->addChild('password', password_hash($password, PASSWORD_DEFAULT));

    $users->asXML($users_file);

    echo "Usuario registrado exitosamente.<br>";
    return true;
}

function LoginUser($username, $password) {
    global $users_file;

    if (file_exists($users_file)) {
        $users = simplexml_load_file($users_file);

        foreach ($users->user as $user) {
            if ((string)$user->username == (string)$username) {
                if (password_verify($password, $user->password)) {
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

function Logout() {
    session_unset();
    session_destroy();
    echo "Has cerrado sesión.<br>";
}

?>
