<?php

include("auth.php"); // Protege la página
include("conexion.php");


/*=========================================
= DOBLE VALIDACIÓN DE SEGURIDAD (¡Agregado!)
=========================================*/
// Validamos en el backend que el rol tenga permiso de 'BORRAR' en 'usuarios.php'
if (!tiene_permiso('modalidades.php', 'BORRAR')) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "No tienes autorización para realizar esta acción. ❌"
    ];
    header("Location: modalidades.php");
    exit();
}


/*=========================================
= VALIDAR ID
=========================================*/
if(!isset($_GET['id']) || empty($_GET['id'])){

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"ID de Modalidad inválida."

    ];

    header("Location: modalidades.php");
    exit();
}


/*=========================================
= OBTENER ID
=========================================*/
$id_modalidad=(int)$_GET['id'];


/*=========================================
= VERIFICAR SI EXISTE LA ESCUELA
=========================================*/
$sqlVerificar="
SELECT nombre
FROM modalidad
WHERE id_modalidad=?
";

$stmt=$conn->prepare($sqlVerificar);

$stmt->bind_param("i",$id_modalidad);

$stmt->execute();

$result=$stmt->get_result();


if($result->num_rows==0){

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"La modalidad no existe."

    ];

    header("Location: modalidades.php");
    exit();
}


$modalidad=$result->fetch_assoc();

$stmt->close();


/*=========================================
= ELIMINAR ESCUELA
=========================================*/
$sqlEliminar="
DELETE FROM modalidad
WHERE id_modalidad=?
";

$stmt=$conn->prepare($sqlEliminar);

$stmt->bind_param("i",$id_modalidad);


/*=========================================
= RESULTADO
=========================================*/
if($stmt->execute()){

    $_SESSION['mensaje']=[

        "tipo"=>"success",
        "texto"=>"Modalidad '".$modalidad['nombre']."' eliminada correctamente. ☑️"

    ];

}else{

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"Error al eliminar la Modalidad. ❌"

    ];

}


$stmt->close();

$conn->close();


header("Location: modalidades.php");
exit();

?>