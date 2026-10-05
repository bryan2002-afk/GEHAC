<?php
include("auth.php");
include("conexion.php");


/*=========================================
= DOBLE VALIDACIÓN DE SEGURIDAD (¡Agregado!)
=========================================*/
// Validamos en el backend que el rol tenga permiso de 'BORRAR' en 'usuarios.php'
if (!tiene_permiso('alumnos_inscritos.php', 'BORRAR')) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "No tienes autorización para realizar esta acción. ❌"
    ];
    header("Location: alumnos_inscritos.php");
    exit();
}


/*=========================================
= VALIDAR ID
=========================================*/
if (!isset($_GET['id']) || empty($_GET['id'])) {

    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "ID del Alumno inválido."
    ];

    header("Location: alumnos_inscritos.php");
    exit();
}


/*=========================================
= OBTENER ID
=========================================*/

$id_inscrito = (int)$_GET['id'];


/*=========================================
= VERIFICAR SI EXISTE Y TRAER DATOS
=========================================*/

$sqlVerificar = "
SELECT 
    ai.id_inscrito,
    ai.id_usuario,
    ai.id_semestre,
    u.nombre,
    u.apellido_p
FROM alumno_inscrito ai
INNER JOIN usuario u
    ON ai.id_usuario = u.id_usuario
WHERE ai.id_inscrito = ?
";

$stmt = $conn->prepare($sqlVerificar);

$stmt->bind_param(
    "i",
    $id_inscrito
);

$stmt->execute();

$result = $stmt->get_result();


if($result->num_rows == 0){

    $_SESSION['mensaje'] = [
        "tipo"=>"error",
        "texto"=>"El alumno no existe."
    ];

    header("Location: alumnos_inscritos.php");
    exit();
}


$inscrito = $result->fetch_assoc();

$stmt->close();


/*=========================================
= ELIMINAR INSCRIPCIÓN
=========================================*/

$sqlEliminar = "
DELETE FROM alumno_inscrito
WHERE id_inscrito=?
";

$stmt = $conn->prepare($sqlEliminar);

$stmt->bind_param(
    "i",
    $id_inscrito
);


if($stmt->execute()){

    $nombreCompleto = trim(
        $inscrito['nombre']." ".
        $inscrito['apellido_p']
    );

    $_SESSION['mensaje']=[

        "tipo"=>"success",

        "texto"=>"Alumno '".$nombreCompleto.
        "' eliminado correctamente ☑️"

    ];

}
else{

    $_SESSION['mensaje']=[

        "tipo"=>"error",

        "texto"=>"Error al eliminar al Alumno del Semestre ❌"

    ];

}


$stmt->close();

$conn->close();

header("Location: alumnos_inscritos.php");

exit();

?>