<?php
include("conexion.php");

if (isset($_GET['id']) && isset($_GET['asistencia'])) {

    $id = intval($_GET['id']);
    $estado_actual = intval($_GET['asistencia']);

    // Cambiar estado (1 → 0, 0 → 1)
    $nuevo_estado = ($estado_actual == 1) ? 0 : 1;

    $sql = "UPDATE disponible SET asistencia = $nuevo_estado WHERE id_disponible = $id";

    if ($conn->query($sql)) {
        header("Location: inscritos_actividades.php");
    } else {
        echo "Error al actualizar asistencia";
    }
}

$conn->close();
?>