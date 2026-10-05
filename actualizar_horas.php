<?php
session_start();

include("auth.php");
include("conexion.php");

try{

    // Iniciar transacción
    $conn->begin_transaction();

    /*
    Buscar únicamente:
    - asistencia = 1
    - procesado = 0
    */

    $sql="

    SELECT

        d.id_disponible,
        d.id_usuario,

        h.horas,
        h.id_categoria

    FROM disponible d

    INNER JOIN hora h
    ON d.id_actividad=h.id_actividad

    WHERE d.asistencia=1
    AND d.procesado=0

    ";

    $result=$conn->query($sql);


    if($result && $result->num_rows>0){

        while($fila=$result->fetch_assoc()){

            $idDisponible=(int)$fila["id_disponible"];
            $idUsuario=(int)$fila["id_usuario"];

            $horas=(int)$fila["horas"];
            $idCategoria=(int)$fila["id_categoria"];


            // =====================================
            // categoría académica
            // =====================================

            if($idCategoria==3){

                $stmt=$conn->prepare("

                    UPDATE alumno_inscrito

                    SET horas_academicas=
                    horas_academicas+?

                    WHERE id_usuario=?

                ");

                $stmt->bind_param(
                    "ii",
                    $horas,
                    $idUsuario
                );

                $stmt->execute();

                $stmt->close();

            }


            // =====================================
            // categoría cultural
            // =====================================

            elseif($idCategoria==5){

                $stmt=$conn->prepare("

                    UPDATE alumno_inscrito

                    SET horas_culturales=
                    horas_culturales+?

                    WHERE id_usuario=?

                ");

                $stmt->bind_param(
                    "ii",
                    $horas,
                    $idUsuario
                );

                $stmt->execute();

                $stmt->close();

            }


            // =====================================
            // Marcar asistencia procesada
            // =====================================

            $stmt=$conn->prepare("

                UPDATE disponible
                SET procesado=1
                WHERE id_disponible=?

            ");

            $stmt->bind_param(
                "i",
                $idDisponible
            );

            $stmt->execute();

            $stmt->close();

        }


        $_SESSION["mensaje"]=[

            "tipo"=>"success",
            "texto"=>"Horas actualizadas correctamente"

        ];

    }else{

        $_SESSION["mensaje"]=[

            "tipo"=>"error",
            "texto"=>"No existen asistencias pendientes por actualizar"

        ];

    }


    // confirmar
    $conn->commit();

}catch(Exception $e){

    $conn->rollback();

    $_SESSION["mensaje"]=[

        "tipo"=>"error",
        "texto"=>"Error: ".$e->getMessage()

    ];

}

$conn->close();

header("Location: alumnos_inscritos.php");
exit;

?>