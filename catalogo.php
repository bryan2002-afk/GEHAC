<?php
session_start();

include("auth.php");
include("conexion.php");


// ================= USUARIO LOGEADO =================

$id_usuario = $_SESSION['id_usuario'];


// ================= MENSAJE =================

$mensaje = [
    "tipo" => "",
    "texto" => ""
];

if(isset($_SESSION['mensaje'])){

    $mensaje = $_SESSION['mensaje'];

    unset($_SESSION['mensaje']);
}


/*=========================================
= PROCESAR INSCRIPCIÓN
=========================================*/

if(isset($_GET['inscribir'])){

    $id_actividad = (int)$_GET['inscribir'];

    // =========================================
    // VERIFICAR SI YA ESTÁ INSCRITO
    // =========================================

    $sqlVerificar = "
        SELECT id_disponible
        FROM disponible
        WHERE id_usuario = ?
        AND id_actividad = ?
    ";

    $stmt = $conn->prepare($sqlVerificar);

    $stmt->bind_param(
        "ii",
        $id_usuario,
        $id_actividad
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    // Si ya existe
    if($resultado->num_rows > 0){

        $_SESSION['mensaje'] = [

            "tipo"=>"error",
            "texto"=>"Ya estás inscrito en esta actividad ⚠️"

        ];

    }else{

        // =========================================
        // VERIFICAR CUPO DISPONIBLE
        // =========================================

        $sqlCupo = "

        SELECT 
            a.cupo,
            COUNT(d.id_disponible) AS inscritos

        FROM actividad a

        LEFT JOIN disponible d
        ON a.id_actividad = d.id_actividad

        WHERE a.id_actividad = ?

        GROUP BY a.cupo

        ";

        $stmtCupo = $conn->prepare($sqlCupo);

        $stmtCupo->bind_param(
            "i",
            $id_actividad
        );

        $stmtCupo->execute();

        $resultCupo = $stmtCupo->get_result();

        $datosCupo = $resultCupo->fetch_assoc();

        // =========================================
        // SI YA NO HAY LUGARES
        // =========================================

        if($datosCupo['inscritos'] >= $datosCupo['cupo']){

            // Cambiar estado automáticamente
            $sqlCerrar = "

            UPDATE actividad
            SET estado = 0
            WHERE id_actividad = ?

            ";

            $stmtCerrar = $conn->prepare($sqlCerrar);

            $stmtCerrar->bind_param(
                "i",
                $id_actividad
            );

            $stmtCerrar->execute();

            $stmtCerrar->close();

            $_SESSION['mensaje'] = [

                "tipo"=>"error",
                "texto"=>"Ya no hay lugares disponibles ⚠️"

            ];

        }else{

            // =========================================
            // INSERTAR INSCRIPCIÓN
            // =========================================

            $sqlInsertar = "
                INSERT INTO disponible
                (
                    id_usuario,
                    id_actividad
                )
                VALUES
                (?,?)
            ";

            $stmtInsert = $conn->prepare($sqlInsertar);

            $stmtInsert->bind_param(
                "ii",
                $id_usuario,
                $id_actividad
            );

            if($stmtInsert->execute()){

                // =========================================
                // VOLVER A CONTAR INSCRITOS
                // =========================================

                $sqlRecontar = "

                SELECT 
                    a.cupo,
                    COUNT(d.id_disponible) AS inscritos

                FROM actividad a

                LEFT JOIN disponible d
                ON a.id_actividad = d.id_actividad

                WHERE a.id_actividad = ?

                GROUP BY a.cupo

                ";

                $stmtRecontar = $conn->prepare($sqlRecontar);

                $stmtRecontar->bind_param(
                    "i",
                    $id_actividad
                );

                $stmtRecontar->execute();

                $resultRecontar = $stmtRecontar->get_result();

                $datosFinales = $resultRecontar->fetch_assoc();

                // =========================================
                // SI EL CUPO YA SE LLENÓ
                // =========================================

                if($datosFinales['inscritos'] >= $datosFinales['cupo']){

                    $sqlActualizar = "

                    UPDATE actividad
                    SET estado = 0
                    WHERE id_actividad = ?

                    ";

                    $stmtUpdate = $conn->prepare($sqlActualizar);

                    $stmtUpdate->bind_param(
                        "i",
                        $id_actividad
                    );

                    $stmtUpdate->execute();

                    $stmtUpdate->close();
                }

                $stmtRecontar->close();

                $_SESSION['mensaje']=[

                    "tipo"=>"success",
                    "texto"=>"Inscripción realizada correctamente ☑️"

                ];

            }else{

                $_SESSION['mensaje']=[

                    "tipo"=>"error",
                    "texto"=>"Error al realizar la inscripción ❌"

                ];

            }

            $stmtInsert->close();

        }

        $stmtCupo->close();

    }

    $stmt->close();

    header("Location: catalogo.php");
    exit();

}



/*=========================================
= ACTIVIDADES YA INSCRITAS
=========================================*/

$actividadesInscritas=[];

$sqlInscritas="
SELECT id_actividad
FROM disponible
WHERE id_usuario=?
";

$stmt=$conn->prepare($sqlInscritas);

$stmt->bind_param(
    "i",
    $id_usuario
);

$stmt->execute();

$resultInscritas=$stmt->get_result();

while($fila=$resultInscritas->fetch_assoc()){

    $actividadesInscritas[]=$fila['id_actividad'];

}

$stmt->close();



/*=========================================
= CONSULTAR ACTIVIDADES
=========================================*/

$sql="

SELECT 

    a.id_actividad,
    a.nombre,
    a.hora_inicio,
    a.hora_fin,
    a.fecha,
    a.lugar,
    a.ponente,
    a.cupo,
    a.estado,
    a.imagen,

    d.nombre AS departamento,

    h.horas,

    cat.nombre AS categoria,

    COUNT(dis.id_disponible) AS inscritos

FROM actividad a

LEFT JOIN departamento d
ON a.id_departamento=d.id_departamento

LEFT JOIN hora h
ON a.id_actividad=h.id_actividad

LEFT JOIN categoria cat
ON h.id_categoria=cat.id_categoria

LEFT JOIN disponible dis
ON a.id_actividad = dis.id_actividad

WHERE a.estado=1

GROUP BY
    a.id_actividad,
    a.nombre,
    a.hora_inicio,
    a.hora_fin,
    a.fecha,
    a.lugar,
    a.ponente,
    a.cupo,
    a.estado,
    a.imagen,
    d.nombre,
    h.horas,
    cat.nombre

ORDER BY a.id_actividad DESC

";

$result=$conn->query($sql);



/*=========================================
= GUARDAR DATOS EN ARRAY
=========================================*/

$actividades=[];

if($result && $result->num_rows>0){

    while($fila=$result->fetch_assoc()){

        $actividades[]=[

            "id_actividad"=>$fila["id_actividad"],
            "nombre"=>$fila["nombre"],
            "hora_inicio"=>$fila["hora_inicio"],
            "hora_fin"=>$fila["hora_fin"],
            "fecha"=>$fila["fecha"],
            "lugar"=>$fila["lugar"],
            "ponente"=>$fila["ponente"],
            "cupo"=>$fila["cupo"],
            "estado"=>$fila["estado"],
            "imagen"=>$fila["imagen"],
            "departamento"=>$fila["departamento"],

            "horas"=>$fila["horas"],
            "categoria"=>$fila["categoria"],
            "inscritos"=>$fila["inscritos"]

        ];

    }

}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Actividades - GEHAC </title>
    <link rel="stylesheet" href="css/catalogo.css">
    <link rel="icon" href="img/logo.ico" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="js/catalogo.js"></script>
</head>



<style>
    #toast {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    color: white;
    font-weight: 500;
    z-index: 9999;
    opacity: 0;
    transform: translateY(-20px);
    transition: all 0.3s ease;
    }

    #toast.success {
        background-color: #2ecc71;
    }

    #toast.error {
        background-color: #e74c3c;
    }

</style>


<body>

    <!-- Toast -->
    <div id="toast" class="toast"></div>

    <script>
    window.addEventListener('DOMContentLoaded', () => {
        const toastDiv = document.getElementById('toast');

         // Usar la variable $mensaje que ya definimos en PHP
        const mensaje = <?php echo $mensaje ? json_encode($mensaje) : 'null'; ?>;

        if (mensaje) {
            toastDiv.classList.add(mensaje.tipo); // success o error
            toastDiv.textContent = mensaje.texto;
            //toastDiv.innerHTML = mensaje.texto;
            toastDiv.style.opacity = 1;
            toastDiv.style.transform = 'translateY(0)';

            setTimeout(() => {
                toastDiv.style.opacity = 0;
                toastDiv.style.transform = 'translateY(-20px)';
            }, 4000);
        }
    });
    </script>


    <div class="sidebar" id="sidebar">


        <a href="dashboard.php" class="logo-link">
    
            <div class="logo-container">
                <img src="img/uni_4.png" alt="Logo UMMA" class="logo">
            </div>

        </a>


      

        <ul>
            <li>
                <a href="dashboard.php" >
                    <i class="fas fa-th-large" ></i>
                    <span> Dashboard</span>
                </a>
            </li>

            <?php if (tiene_permiso('usuarios.php', 'VER')): ?>
            <li>
                <a href="usuarios.php" >
                    <i class="fas fa-users" ></i>
                    <span>Usuarios</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('roles.php', 'VER')): ?>
            <li>
                <a href="roles.php">
                    <i class="fas fa-user-shield" ></i>
                    <span>Roles</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('blogs.php', 'VER')): ?>
            <li>
                <a href="blogs.php"  >
                    <i class="fas fa-blog" ></i>
                    <span>Blogs</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('escuelas.php', 'VER')): ?>
            <li>
                <a href="escuelas.php" >
                    <i class="fas fa-school"></i>
                    <span>Escuelas</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('modalidades.php', 'VER')): ?>
            <li>
                <a href="modalidades.php" >
                    <i class="fas fa-laptop-house"  ></i>
                    <span>Modalidades</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('carreras.php', 'VER')): ?>
            <li>
                <a href="carreras.php" >
                    <i class="fas fa-graduation-cap" ></i>
                    <span>Carreras</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('semestres.php', 'VER')): ?>
            <li>
                <a href="semestres.php" >
                    <i class="fas fa-calendar-alt" ></i>
                    <span>Semestres</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('alumnos.php', 'VER')): ?>
            <li>
                <a href="alumnos.php" >
                    <i class="fas fa-user-graduate" ></i>
                    <span>Alumnos</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('departamentos.php', 'VER')): ?>
            <li>
                <a href="departamentos.php" >
                    <i class="fas fa-building" ></i>
                    <span>Departamentos</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('actividades.php', 'VER')): ?>
            <li>
                <a href="actividades.php">
                    <i class="fas fa-tasks" ></i>
                    <span>Actividades</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('categorias.php', 'VER')): ?>
            <li>
                <a href="categorias.php"  >
                    <i class="fas fa-tags" ></i>
                    <span>Categorias</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('horas.php', 'VER')): ?>
            <li>
                <a href="horas.php" >
                    <i class="fas fa-clock" ></i>
                    <span>Número de Horas</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('alumnos_inscritos.php', 'VER')): ?>
            <li>
                <a href="alumnos_inscritos.php" >
                    <i class="fas fa-user-check" ></i>
                    <span>Alumnos Inscritos</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('inscritos_actividades.php', 'VER')): ?>
            <li>
                <a href="inscritos_actividades.php">
                    <i class="fas fa-clipboard-check" ></i>
                    <span>Inscritos a Actividades</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('catalogo.php', 'VER')): ?>
            <li>
                <a href="catalogo.php"  class="active">
                    <i class="fas fa-book-open" style="color:#4031a1"></i>
                    <span>Catalogo de Actividades</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    
    <main class="contenido">

        <!-- Header superior -->
        <header class="top-header">

            <!-- Botón para ocultar sidebar -->
            <div class="header-left">

                <button id="toggle-btn">
                    <i class="fas fa-bars"></i>
                </button>

            </div>


            <!-- Menú derecho -->
            <div class="header-right">

                <ul>

                    <li class="submenu">

                        <a href="#" class="submenu-toggle">

                            <img src="img/gehac.png" alt="Usuario" class="menu-avatar">

                            <span>¡Hola! <strong> <?php echo $nombreCompleto; ?></strong></span>

                            <i class="fas fa-chevron-down submenu-arrow"></i>

                        </a>

                        <ul class="submenu-list" >

                           <li>
                            <a style="color: black;  justify-content:center;  " >
                                <span>Rol: <strong><?php echo $rol; ?> </strong> </span>
                            </a>
                           </li>

                            <li>
                                <a href="#">
                                    <i class="fas fa-user-circle"></i>
                                    <span>Editar Perfil de Usuario</span>
                                </a>
                            </li>

                            <li>
                                <a href="#">
                                    <i class="fas fa-key"></i>
                                    <span>Cambiar Contraseña</span>
                                </a>
                            </li>

                            <li>
                                <a href="logout.php" style="color: red;">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Cerrar sesión</span>
                                </a>
                            </li>

                        </ul>

                    </li>

                </ul>

            </div>

        </header>


        <!-- Sección debajo del header -->
        <section class="dashboard-section">

            <div class="header-title">
                <h3>Catálogo de Actividades - GEHAC</h3>
            </div>

        </section>
        

   

        <!-- ÁREA DE TRABAJO -->
        <section class="content-wrapper">


            <div class="card">

                <div class="products-header">

                    

                </div>

                <div class="estado-section">

                        <h2 class="estado-title title-proceso">
                            🟢 Disponibles 
                        </h2>

                        <!--<table id="tablaActividades" class="estado-table">

                            <thead>
                                <tr>
                                    <th>Img</th>
                                    <th>Nombre</th>
                                    <th>Horario</th>
                                    <th>Fecha</th>
                                    <th>Lugar</th>
                                    <th>Cupo</th>
                                    <th>Horas</th>
                                    <th>Categoria</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>

                            <tbody>

                                

                                <?php if(!empty($actividades)): ?>

                                    <?php foreach($actividades as $row): ?>

                                        <tr>

                                            <td>
                                                <img src="img_actividades/<?= $row['imagen']; ?>" 
                                                    alt="<?= htmlspecialchars($row['nombre']); ?>" 
                                                    class="product-img" style="width: 80px;  height: 80px;">

                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['nombre']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['hora_inicio']) ?>
                                                a 
                                                <?= htmlspecialchars($row['hora_fin']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['fecha']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['lugar']) ?>
                                            </td>

                                           

                                            <td>
                                                <?= htmlspecialchars($row['cupo']) ?>
                                            </td>


                                            <td>
                                                <?= !empty($row['horas']) 
                                                    ? htmlspecialchars($row['horas']) 
                                                    : 'Sin horas'
                                                ?>
                                            </td>


                                            <td>
                                                <?= !empty($row['categoria']) 
                                                    ? htmlspecialchars($row['categoria']) 
                                                    : 'Sin categoría'
                                                ?>
                                            </td>

                                            <td>
                                                <?php if ($row['estado'] == 1): ?>
                                                    <span style="color: green; font-weight: bold;">Activo</span>
                                                <?php else: ?>
                                                    <span style="color: red; font-weight: bold;">Inactivo</span>
                                                <?php endif; ?>
                                            </td>

                                           
                                            <td>

                                                <?php $id=(int)$row['id_actividad']; ?>

                                                    

                                                

                                                    <a href="actividades_editar.php?id=<?= $id ?>" class="btn-edit">
                                                        <i class="fas fa-pen"></i>
                                                        <span>Inscribirse</span>
                                                    </a>

                                                    

                                                

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>

                                        <td colspan="4" style="text-align: center">

                                            No existen actividades registradas.

                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>--> 
                        <div class="actividad-grid">

                            <?php if(!empty($actividades)): ?>

                                <?php foreach($actividades as $row): ?>

                                    <?php $id=(int)$row['id_actividad']; ?>

                                    <div class="actividad-card">

                                        <!-- Imagen -->
                                        <div class="actividad-img">

                                            <img src="img_actividades/<?= !empty($row['imagen']) ? $row['imagen'] : 'default.jpg' ?>" 
                                                alt="<?= htmlspecialchars($row['nombre']); ?>">

                                        </div>

                                        <!-- Contenido -->
                                        <div class="actividad-info">

                                            <h3>
                                                <?= htmlspecialchars($row['nombre']) ?>
                                            </h3>

                                            <p>
                                                <i class="fas fa-clock"></i>
                                                <?= htmlspecialchars($row['hora_inicio']) ?>
                                                -
                                                <?= htmlspecialchars($row['hora_fin']) ?>
                                            </p>

                                            <p>
                                                <i class="fas fa-calendar"></i>
                                                <?= htmlspecialchars($row['fecha']) ?>
                                            </p>

                                            <p>
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?= htmlspecialchars($row['lugar']) ?>
                                            </p>

                                        

                                            <p>
                                                <i class="fas fa-tag"></i>
                                                Categoría:
                                                <?= !empty($row['categoria']) 
                                                    ? htmlspecialchars($row['categoria']) 
                                                    : 'Sin categoría'
                                                ?>
                                            </p>

                                            <p>
                                                <i class="fas fa-hourglass-half"></i>
                                                Horas:
                                                <?= !empty($row['horas']) 
                                                    ? htmlspecialchars($row['horas']) 
                                                    : 'Sin horas'
                                                ?>
                                            </p>

                                            

                                            <p>
                                                <i class="fas fa-users"></i>
                                                Cupo Máx:
                                                <strong><?= htmlspecialchars($row['cupo']) ?></strong>
                                                <span>Lugares</span>
                                            </p>


                                            <?php
                                                $disponibles = $row['cupo'] - $row['inscritos'];

                                                // Evitar negativos
                                                if($disponibles < 0){
                                                    $disponibles = 0;
                                                }
                                            ?>

                                           

                                            <p>
                                                <i class="fas fa-user-check"></i>
                                                Inscritos:
                                                <strong><?= $row['inscritos'] ?></strong>
                                                <span>Alumnos</span>
                                            </p>

                                            <p>

                                                <?php if($row['estado']==1): ?>

                                                    <span class="estado activo">
                                                        Disponible
                                                    </span>

                                                <?php else: ?>

                                                    <span class="estado inactivo">
                                                        Inactivo
                                                    </span>

                                                <?php endif; ?>

                                            </p>

                                        </div>

                                         
                                        <div class="actividad-footer">

                                            <?php if(in_array($id,$actividadesInscritas)): ?>

                                                <button class="btn-inscrito" disabled>

                                                    <i class="fas fa-user-check"></i>
                                                    Inscrito

                                                </button>

                                            <?php else: ?>

                                                <?php if (tiene_permiso('catalogo.php', 'CREAR')): ?>
                                                    <a href="catalogo.php?inscribir=<?= $id ?>"
                                                    class="btn-inscribir"
                                                    onclick="return confirm('¿Deseas inscribirte a esta actividad?')">

                                                        <i class="fas fa-user-plus"></i>
                                                        Inscribirse

                                                    </a>
                                                <?php endif; ?>

                                            <?php endif; ?>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <p style="text-align:center">
                                    No existen actividades registradas
                                </p>

                            <?php endif; ?>

                        </div>

                </div>

            </div>

        </section>
        


        
        <footer class="footer">

            <p class="copy">Todos los derechos reservados por la Universidad Mundo Maya © 2026</p>

        </footer>


    </main>
    

</body>
</html>

<?php
$conn->close();
?>