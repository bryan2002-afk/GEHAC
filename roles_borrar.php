<?php

include("auth.php"); // Protege la página
include("conexion.php");


/*=========================================
= DOBLE VALIDACIÓN DE SEGURIDAD (¡Agregado!)
=========================================*/
// Validamos en el backend que el rol tenga permiso de 'BORRAR' en 'usuarios.php'
if (!tiene_permiso('roles.php', 'BORRAR')) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "No tienes autorización para realizar esta acción. ❌"
    ];
    header("Location: roles.php");
    exit();
}


/*=========================================
= VALIDAR ID
=========================================*/
if(!isset($_GET['id']) || empty($_GET['id'])){

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"ID de Rol inválido."

    ];

    header("Location: roles.php");
    exit();
}


/*=========================================
= OBTENER ID
=========================================*/
$id_rol=(int)$_GET['id'];


/*=========================================
= VERIFICAR SI EXISTE LA ESCUELA
=========================================*/
$sqlVerificar="
SELECT *
FROM rol
WHERE id_rol=?
";

$stmt=$conn->prepare($sqlVerificar);

$stmt->bind_param("i",$id_rol);

$stmt->execute();

$result=$stmt->get_result();


if($result->num_rows==0){

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"El Rol no existe."

    ];

    header("Location: roles.php");
    exit();
}


$role=$result->fetch_assoc();

$stmt->close();


/*=========================================
= ELIMINAR ESCUELA
=========================================*/
$sqlEliminar="
DELETE FROM rol
WHERE id_rol=?
";

$stmt=$conn->prepare($sqlEliminar);

$stmt->bind_param("i",$id_rol);


/*=========================================
= RESULTADO
=========================================*/
if($stmt->execute()){

    $_SESSION['mensaje']=[

        "tipo"=>"success",
        "texto"=>"Rol '".$role['nombre']."' eliminado correctamente. ☑️"

    ];

}else{

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"Error al eliminar Rol. ❌"

    ];

}


$stmt->close();

$conn->close();


header("Location: roles.php");
exit();

?>