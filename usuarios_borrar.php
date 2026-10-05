<?php

include("auth.php"); // Protege la página, inicia sesión y carga las funciones de conf.php
include("conexion.php");


/*=========================================
= DOBLE VALIDACIÓN DE SEGURIDAD (¡Agregado!)
=========================================*/
// Validamos en el backend que el rol tenga permiso de 'BORRAR' en 'usuarios.php'
if (!tiene_permiso('usuarios.php', 'BORRAR')) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "No tienes autorización para realizar esta acción. ❌"
    ];
    header("Location: usuarios.php");
    exit();
}


/*=========================================
= VALIDAR ID
=========================================*/
if(!isset($_GET['id']) || empty($_GET['id'])){

    $_SESSION['mensaje']=[
        "tipo"=>"error",
        "texto"=>"ID de Usuario inválido."
    ];

    header("Location: usuarios.php");
    exit();
}


/*=========================================
= OBTENER ID
=========================================*/
$id_usuario=(int)$_GET['id'];


/*=========================================
= VERIFICAR SI EXISTE EL USUARIO
=========================================*/
$sqlVerificar="
SELECT *
FROM usuario
WHERE id_usuario=?
";

$stmt=$conn->prepare($sqlVerificar);
$stmt->bind_param("i",$id_usuario);
$stmt->execute();
$result=$stmt->get_result();

if($result->num_rows==0){

    $_SESSION['mensaje']=[
        "tipo"=>"error",
        "texto"=>"El usuario no existe."
    ];

    $stmt->close();
    header("Location: usuarios.php");
    exit();
}

$usuarios=$result->fetch_assoc();
$stmt->close();


/*=========================================
= ELIMINAR USUARIO
=========================================*/
$sqlEliminar="
DELETE FROM usuario
WHERE id_usuario=?
";

$stmt=$conn->prepare($sqlEliminar);
$stmt->bind_param("i",$id_usuario);


/*=========================================
= RESULTADO
=========================================*/
if($stmt->execute()){

    $_SESSION['mensaje']=[
        "tipo"=>"success",
        "texto"=>"Usuario '".$usuarios['nombre']."' eliminado correctamente. ☑️"
    ];

}else{

    $_SESSION['mensaje']=[
        "tipo"=>"error",
        "texto"=>"Error al eliminar usuario. El registro podría estar vinculado a otras tablas. ❌"
    ];

}

$stmt->close();
$conn->close();

header("Location: usuarios.php");
exit();

?>