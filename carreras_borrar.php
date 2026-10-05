<?php

include("auth.php"); // Protege la página
include("conexion.php");



/*=========================================
= DOBLE VALIDACIÓN DE SEGURIDAD (¡Agregado!)
=========================================*/
// Validamos en el backend que el rol tenga permiso de 'BORRAR' en 'usuarios.php'
if (!tiene_permiso('carreras.php', 'BORRAR')) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "No tienes autorización para realizar esta acción. ❌"
    ];
    header("Location: carreras.php");
    exit();
}


/*=========================================
= VALIDAR ID
=========================================*/
if(!isset($_GET['id']) || empty($_GET['id'])){

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"ID de la Carrera inválido."

    ];

    header("Location: carreras.php");
    exit();
}


/*=========================================
= OBTENER ID
=========================================*/
$id_carrera=(int)$_GET['id'];


/*=========================================
= VERIFICAR SI EXISTE LA ESCUELA
=========================================*/
$sqlVerificar="
SELECT *
FROM carrera
WHERE id_carrera=?
";

$stmt=$conn->prepare($sqlVerificar);

$stmt->bind_param("i",$id_carrera);

$stmt->execute();

$result=$stmt->get_result();


if($result->num_rows==0){

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"La Carrera no existe."

    ];

    header("Location: carreras.php");
    exit();
}


$carrerass=$result->fetch_assoc();

$stmt->close();


/*=========================================
= ELIMINAR ESCUELA
=========================================*/
$sqlEliminar="
DELETE FROM carrera
WHERE id_carrera=?
";

$stmt=$conn->prepare($sqlEliminar);

$stmt->bind_param("i",$id_carrera);


/*=========================================
= RESULTADO
=========================================*/
if($stmt->execute()){

    $_SESSION['mensaje']=[

        "tipo"=>"success",
        "texto"=>"Carrera '".$carrerass['nombre']."' eliminada correctamente. ☑️"

    ];

}else{

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"Error al eliminar Carrera. ❌"

    ];

}


$stmt->close();

$conn->close();


header("Location: carreras.php");
exit();

?>