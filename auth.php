<?php

// 1. Incluimos el nuevo archivo de configuración y funciones
require_once('conf.php');


/*=========================================
= INICIAR SESIÓN
=========================================*/
if(session_status() == PHP_SESSION_NONE){
    session_start();
}


/*=========================================
= VALIDAR SI EL USUARIO INICIÓ SESIÓN
=========================================*/
if(!isset($_SESSION['id_usuario'])){
    header("Location: login.php");
    exit();
}


/*=========================================
= EVITAR CACHE
=========================================*/
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");


/*=========================================
= OBTENER DATOS DE SESIÓN
=========================================*/
$id_usuario=$_SESSION['id_usuario'];


/*=========================================
= CONECTAR A BASE DE DATOS
=========================================*/
include('conexion.php');


/*=========================================
= CONSULTAR USUARIO + ROL
=========================================*/
$sql="
SELECT 
u.nombre,
u.nombre_s,
u.apellido_p,
u.apellido_m,
u.correo,
u.id_rol,
u.id_carrera,
u.id_modalidad,
r.nombre AS rol
FROM usuario u
INNER JOIN rol r ON u.id_rol = r.id_rol
WHERE u.id_usuario=?
";

$stmt=$conn->prepare($sql);
$stmt->bind_param("i",$id_usuario);
$stmt->execute();
$result=$stmt->get_result();

if($result && $fila=$result->fetch_assoc()){

    $nombreCompleto=trim(
        $fila['nombre']." ".
        $fila['nombre_s']." ".
        $fila['apellido_p']." ".
        $fila['apellido_m']
    );

    $correo=$fila['correo'];
    $rol=$fila['rol'];         // Nombre del rol (ej: "Administrador")
    $id_rol=$fila['id_rol'];   // ID del rol (ej: 1, 3, 4)
    $id_carrera=$fila['id_carrera'];
    $id_modalidad=$fila['id_modalidad'];

    /*=========================================
    = ¡AQUÍ EJECUTAMOS LA FUNCIÓN DE CONF.PHP!
    =========================================*/
    // Mandamos la conexión actual y el ID del rol que acabamos de obtener
    cargarPermisosUsuario($conn, $id_rol);

}else{
    $nombreCompleto="Usuario";
    $rol="Sin rol";
    $id_rol=0; // Valor por defecto seguro
}

$stmt->close();
?>