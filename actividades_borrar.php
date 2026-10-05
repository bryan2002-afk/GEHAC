<?php
include("auth.php");
include("conexion.php");


/*=========================================
= DOBLE VALIDACIÓN DE SEGURIDAD (¡Agregado!)
=========================================*/
// Validamos en el backend que el rol tenga permiso de 'BORRAR' en 'usuarios.php'
if (!tiene_permiso('actividades.php', 'BORRAR')) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "No tienes autorización para realizar esta acción. ❌"
    ];
    header("Location: actividades.php");
    exit();
}


/*=========================================
= VALIDAR ID
=========================================*/
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "ID de Actividad inválido."
    ];
    header("Location: actividades.php");
    exit();
}

/*=========================================
= OBTENER ID
=========================================*/
$id_actividad = (int)$_GET['id'];

/*=========================================
= VERIFICAR SI EXISTE LA ACTIVIDAD
=========================================*/
$sqlVerificar = "SELECT * FROM actividad WHERE id_actividad = ?";
$stmt = $conn->prepare($sqlVerificar);
$stmt->bind_param("i", $id_actividad);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "La Actividad no existe."
    ];
    header("Location: actividades.php");
    exit();
}

$actividad = $result->fetch_assoc();
$stmt->close();

/*=========================================
= ELIMINAR IMAGEN DE LA CARPETA
=========================================*/
if (!empty($actividad['imagen'])) {
    $rutaImagen = 'img_actividades/' . $actividad['imagen'];
    if (file_exists($rutaImagen)) {
        unlink($rutaImagen); // Borra la imagen física
    }
}

/*=========================================
= ELIMINAR ACTIVIDAD
=========================================*/
$sqlEliminar = "DELETE FROM actividad WHERE id_actividad = ?";
$stmt = $conn->prepare($sqlEliminar);
$stmt->bind_param("i", $id_actividad);

if ($stmt->execute()) {
    $_SESSION['mensaje'] = [
        "tipo" => "success",
        "texto" => "Actividad '" . $actividad['nombre'] . "' eliminada correctamente. ☑️"
    ];
} else {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "Error al eliminar actividad. ❌"
    ];
}

$stmt->close();
$conn->close();

header("Location: actividades.php");
exit();
?>