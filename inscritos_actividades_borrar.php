<?php

include("auth.php"); // Protege la página
include("conexion.php");


/*=========================================
= DOBLE VALIDACIÓN DE SEGURIDAD (¡Agregado!)
=========================================*/
// Validamos en el backend que el rol tenga permiso de 'BORRAR' en 'usuarios.php'
if (!tiene_permiso('inscritos_actividades.php', 'BORRAR')) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "No tienes autorización para realizar esta acción. ❌"
    ];
    header("Location: inscritos_actividades.php");
    exit();
}



/*=========================================
= VALIDAR ID
=========================================*/
if(!isset($_GET['id']) || empty($_GET['id'])){

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"ID de la inscripción inválido."

    ];

    header("Location: inscritos_actividades.php");
    exit();
}


/*=========================================
= OBTENER ID
=========================================*/
$id_disponible=(int)$_GET['id'];


/*=========================================
= VERIFICAR SI EXISTE LA ESCUELA
=========================================*/
$sqlVerificar="
SELECT *
FROM disponible
WHERE id_disponible=?
";

$stmt=$conn->prepare($sqlVerificar);

$stmt->bind_param("i",$id_disponible);

$stmt->execute();

$result=$stmt->get_result();


if($result->num_rows==0){

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"La Inscripción no existe."

    ];

    header("Location: inscritos_actividades.php");
    exit();
}


$disponibles=$result->fetch_assoc();

$stmt->close();


/*=========================================
= ELIMINAR ESCUELA
=========================================*/
$sqlEliminar="
DELETE FROM disponible
WHERE id_disponible=?
";

$stmt=$conn->prepare($sqlEliminar);

$stmt->bind_param("i",$id_disponible);


/*=========================================
= RESULTADO
=========================================*/
if($stmt->execute()){

    $_SESSION['mensaje']=[

        "tipo"=>"success",
        "texto"=>"Inscripción '".$disponibles['id_disponible']."' eliminada correctamente. ☑️"

    ];

}else{

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"Error al eliminar Inscripción. ❌"

    ];

}


$stmt->close();

$conn->close();


header("Location: inscritos_actividades.php");
exit();

?>