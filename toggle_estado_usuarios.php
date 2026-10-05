<?php
include("conexion.php");

if (isset($_GET['id']) && isset($_GET['estado'])) {

    $id = intval($_GET['id']);
    $estado_actual = intval($_GET['estado']);

    // Cambiar estado (1 → 0, 0 → 1)
    $nuevo_estado = ($estado_actual == 1) ? 0 : 1;

    $sql = "UPDATE usuario SET estado = $nuevo_estado WHERE id_usuario = $id";

    if ($conn->query($sql)) {
        header("Location: alumnos.php");
    } else {
        echo "Error al actualizar estado";
    }
}

$conn->close();
?>