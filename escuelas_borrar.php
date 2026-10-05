<?php

include("auth.php"); // Protege la página
include("conexion.php");


/*=========================================
= DOBLE VALIDACIÓN DE SEGURIDAD (¡Agregado!)
=========================================*/
// Validamos en el backend que el rol tenga permiso de 'BORRAR' en 'usuarios.php'
if (!tiene_permiso('escuelas.php', 'BORRAR')) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "No tienes autorización para realizar esta acción. ❌"
    ];
    header("Location: escuelas.php");
    exit();
}


/*=========================================
= VALIDAR ID
=========================================*/
if(!isset($_GET['id']) || empty($_GET['id'])){

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"ID de escuela inválido."

    ];

    header("Location: escuelas.php");
    exit();
}


/*=========================================
= OBTENER ID
=========================================*/
$id_escuela=(int)$_GET['id'];


/*=========================================
= VERIFICAR SI EXISTE LA ESCUELA
=========================================*/
$sqlVerificar="
SELECT nombre
FROM escuela
WHERE id_escuela=?
";

$stmt=$conn->prepare($sqlVerificar);

$stmt->bind_param("i",$id_escuela);

$stmt->execute();

$result=$stmt->get_result();


if($result->num_rows==0){

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"La escuela no existe."

    ];

    header("Location: escuelas.php");
    exit();
}


$escuela=$result->fetch_assoc();

$stmt->close();


/*=========================================
= ELIMINAR ESCUELA
=========================================*/
$sqlEliminar="
DELETE FROM escuela
WHERE id_escuela=?
";

$stmt=$conn->prepare($sqlEliminar);

$stmt->bind_param("i",$id_escuela);


/*=========================================
= RESULTADO
=========================================*/
if($stmt->execute()){

    $_SESSION['mensaje']=[

        "tipo"=>"success",
        "texto"=>"Escuela '".$escuela['nombre']."' eliminada correctamente. ☑️"

    ];

}else{

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"Error al eliminar la escuela. ❌"

    ];

}


$stmt->close();

$conn->close();


header("Location: escuelas.php");
exit();

?>