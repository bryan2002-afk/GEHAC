<?php

include("auth.php"); // Protege la página
include("conexion.php");


/*=========================================
= DOBLE VALIDACIÓN DE SEGURIDAD (¡Agregado!)
=========================================*/
// Validamos en el backend que el rol tenga permiso de 'BORRAR' en 'usuarios.php'
if (!tiene_permiso('semestres.php', 'BORRAR')) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "No tienes autorización para realizar esta acción. ❌"
    ];
    header("Location: semestres.php");
    exit();
}


/*=========================================
= VALIDAR ID
=========================================*/
if(!isset($_GET['id']) || empty($_GET['id'])){

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"ID de Semestre inválido."

    ];

    header("Location: semestres.php");
    exit();
}


/*=========================================
= OBTENER ID
=========================================*/
$id_semestre=(int)$_GET['id'];


/*=========================================
= VERIFICAR SI EXISTE LA ESCUELA
=========================================*/
$sqlVerificar="
SELECT *
FROM semestre
WHERE id_semestre=?
";

$stmt=$conn->prepare($sqlVerificar);

$stmt->bind_param("i",$id_semestre);

$stmt->execute();

$result=$stmt->get_result();


if($result->num_rows==0){

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"El Semestre no existe."

    ];

    header("Location: semestres.php");
    exit();
}


$semestress=$result->fetch_assoc();

$stmt->close();


/*=========================================
= ELIMINAR ESCUELA
=========================================*/
$sqlEliminar="
DELETE FROM semestre
WHERE id_semestre=?
";

$stmt=$conn->prepare($sqlEliminar);

$stmt->bind_param("i",$id_semestre);


/*=========================================
= RESULTADO
=========================================*/
if($stmt->execute()){

    $_SESSION['mensaje']=[

        "tipo"=>"success",
        "texto"=>"Semestre '".$semestress['nombre']."' eliminado correctamente. ☑️"

    ];

}else{

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"Error al eliminar Semestre. ❌"

    ];

}


$stmt->close();

$conn->close();


header("Location: semestres.php");
exit();

?>