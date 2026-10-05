<?php
// conf.php

/*=========================================
= CONSTANTES DE ROLES
=========================================*/
define('ROL_ADMIN', 1);
define('ROL_ALUMNO', 3);
define('ROL_COORDINADOR', 4);

/*=========================================
= FUNCIÓN PARA CARGAR PERMISOS EN SESIÓN
=========================================*/
function cargarPermisosUsuario($conn, $id_rol) {
    $_SESSION['permisos'] = []; // Inicializamos el contenedor en la sesión

    $sql_permisos = "
        SELECT m.archivo_php, p.nombre AS permiso
        FROM rol_permiso rpm
        INNER JOIN modulo m ON rpm.id_modulo = m.id_modulo
        INNER JOIN permiso p ON rpm.id_permiso = p.id_permiso
        WHERE rpm.id_rol = ?
    ";

    if ($stmt_p = $conn->prepare($sql_permisos)) {
        $stmt_p->bind_param("i", $id_rol);
        $stmt_p->execute();
        $res_p = $stmt_p->get_result();

        while ($row = $res_p->fetch_assoc()) {
            // Estructura: $_SESSION['permisos']['usuarios.php'][] = 'VER';
            $_SESSION['permisos'][$row['archivo_php']][] = $row['permiso'];
        }
        $stmt_p->close();
    }
}

/*=========================================
= FUNCIÓN HELPER PARA VERIFICAR PERMISOS VISTAS
=========================================*/
function tiene_permiso($archivo_php, $accion = 'VER') {
    if (!isset($_SESSION['permisos'])) {
        return false;
    }
    
    if (isset($_SESSION['permisos'][$archivo_php])) {
        return in_array($accion, $_SESSION['permisos'][$archivo_php]);
    }
    
    return false;
}