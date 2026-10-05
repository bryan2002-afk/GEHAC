<?php

include("auth.php"); // Protege la página
include("conexion.php");


/*=========================================
= DOBLE VALIDACIÓN DE SEGURIDAD (¡Agregado!)
=========================================*/
// Validamos en el backend que el rol tenga permiso de 'BORRAR' en 'usuarios.php'
if (!tiene_permiso('departamentos.php', 'BORRAR')) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "No tienes autorización para realizar esta acción. ❌"
    ];
    header("Location: departamentos.php");
    exit();
}

/*=========================================
= VALIDAR ID
=========================================*/
if(!isset($_GET['id']) || empty($_GET['id'])){

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"ID de Departamento inválido."

    ];

    header("Location: departamentos.php");
    exit();
}


/*=========================================
= OBTENER ID
=========================================*/
$id_departamento=(int)$_GET['id'];


/*=========================================
= VERIFICAR SI EXISTE LA ESCUELA
=========================================*/
$sqlVerificar="
SELECT nombre
FROM departamento
WHERE id_departamento=?
";

$stmt=$conn->prepare($sqlVerificar);

$stmt->bind_param("i",$id_departamento);

$stmt->execute();

$result=$stmt->get_result();


if($result->num_rows==0){

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"El Depaartamento no existe."

    ];

    header("Location: departamentos.php");
    exit();
}


$departamento=$result->fetch_assoc();

$stmt->close();


/*=========================================
= ELIMINAR ESCUELA
=========================================*/
$sqlEliminar="
DELETE FROM departamento
WHERE id_departamento=?
";

$stmt=$conn->prepare($sqlEliminar);

$stmt->bind_param("i",$id_departamento);


/*=========================================
= RESULTADO
=========================================*/
if($stmt->execute()){

    $_SESSION['mensaje']=[

        "tipo"=>"success",
        "texto"=>"Departamento '".$departamento['nombre']."' eliminado correctamente. ☑️"

    ];

}else{

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"Error al eliminar el Departamento. ❌"

    ];

}


$stmt->close();

$conn->close();


header("Location: departamentos.php");
exit();

?>