<?php

/*=========================================
= INICIAR SESIÓN
=========================================*/
session_start();


/*=========================================
= ELIMINAR TODAS LAS VARIABLES DE SESIÓN
=========================================*/
$_SESSION = array();


/*=========================================
= DESTRUIR LA SESIÓN
=========================================*/
session_destroy();


/*=========================================
= ELIMINAR COOKIE DE SESIÓN (opcional)
=========================================*/
if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}


/*=========================================
= REDIRIGIR AL LOGIN
=========================================*/
header("Location: login.php");
exit();

?>