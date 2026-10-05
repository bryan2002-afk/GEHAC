<?php

include("auth.php"); // Protege la página
include("conexion.php");


/*=========================================
= DOBLE VALIDACIÓN DE SEGURIDAD (¡Agregado!)
=========================================*/
// Validamos en el backend que el rol tenga permiso de 'BORRAR' en 'usuarios.php'
if (!tiene_permiso('blogs.php', 'BORRAR')) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "No tienes autorización para realizar esta acción. ❌"
    ];
    header("Location: blogs.php");
    exit();
}



/*=========================================
= VALIDAR ID
=========================================*/
if(!isset($_GET['id']) || empty($_GET['id'])){

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"ID de Blog inválido."

    ];

    header("Location: blogs.php");
    exit();
}


/*=========================================
= OBTENER ID
=========================================*/
$id_blog=(int)$_GET['id'];


/*=========================================
= VERIFICAR SI EXISTE LA ESCUELA
=========================================*/
$sqlVerificar="
SELECT *
FROM blog
WHERE id_blog=?
";

$stmt=$conn->prepare($sqlVerificar);

$stmt->bind_param("i",$id_blog);

$stmt->execute();

$result=$stmt->get_result();


if($result->num_rows==0){

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"El Blog no existe."

    ];

    header("Location: blogs.php");
    exit();
}


$role=$result->fetch_assoc();

$stmt->close();


/*=========================================
= ELIMINAR ESCUELA
=========================================*/
$sqlEliminar="
DELETE FROM blog
WHERE id_blog=?
";

$stmt=$conn->prepare($sqlEliminar);

$stmt->bind_param("i",$id_blog);


/*=========================================
= RESULTADO
=========================================*/
if($stmt->execute()){

    $_SESSION['mensaje']=[

        "tipo"=>"success",
        "texto"=>"Blog '".$blogs['nombre']."' eliminado correctamente. ☑️"

    ];

}else{

    $_SESSION['mensaje']=[

        "tipo"=>"error",
        "texto"=>"Error al eliminar Blog. ❌"

    ];

}


$stmt->close();

$conn->close();


header("Location: blogs.php");
exit();

?>