<?php
include("auth.php");
include("conexion.php");


/*=========================================
= DOBLE VALIDACIÓN DE SEGURIDAD (¡Agregado!)
=========================================*/
// Validamos en el backend que el rol tenga permiso de 'BORRAR' en 'usuarios.php'
if (!tiene_permiso('categorias.php', 'BORRAR')) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "No tienes autorización para realizar esta acción. ❌"
    ];
    header("Location: categorias.php");
    exit();
}



/*=========================================
= VALIDAR ID
=========================================*/
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "ID de Categoria inválido."
    ];
    header("Location: categorias.php");
    exit();
}

/*=========================================
= OBTENER ID
=========================================*/
$id_categoria = (int)$_GET['id'];

/*=========================================
= VERIFICAR SI EXISTE LA ACTIVIDAD
=========================================*/
$sqlVerificar = "SELECT * FROM categoria WHERE id_categoria = ?";
$stmt = $conn->prepare($sqlVerificar);
$stmt->bind_param("i", $id_categoria);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "La Categoria no existe."
    ];
    header("Location: categorias.php");
    exit();
}

$categorias = $result->fetch_assoc();
$stmt->close();


/*=========================================
= ELIMINAR ACTIVIDAD
=========================================*/
$sqlEliminar = "DELETE FROM categoria WHERE id_categoria = ?";
$stmt = $conn->prepare($sqlEliminar);
$stmt->bind_param("i", $id_categoria);

if ($stmt->execute()) {
    $_SESSION['mensaje'] = [
        "tipo" => "success",
        "texto" => "Categoria '" . $categorias['nombre'] . "' eliminada correctamente. ☑️"
    ];
} else {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "Error al eliminar Categoria. ❌"
    ];
}

$stmt->close();
$conn->close();

header("Location: categorias.php");
exit();
?>