<?php
session_start(); // 🔥 ESTO TE FALTA
include("auth.php"); // 👈 protege la página
include("conexion.php");


// ================= MENSAJE =================
$mensaje = [
    "tipo" => "",
    "texto" => ""
];

if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}


/*================= CONSULTA =================
$sql = "SELECT a.id_usuario, a.nombre, a.nombre_s, a.apellido_p, a.apellido_m, a.matricula, a.estado,
               c.nombre AS carrera,
               d.nombre AS modalidad
        FROM usuario a
        LEFT JOIN carrera c ON a.id_carrera = c.id_carrera
        LEFT JOIN modalidad d ON a.id_modalidad = d.id_modalidad
        WHERE a.id_rol = 3
        ORDER BY a.id_usuario DESC";


$result = $conn->query($sql);

// ================= GUARDAR DATOS EN ARRAY =================
$usuarios = [];

if($result && $result->num_rows > 0) {
    while($fila = $result->fetch_assoc()) {
        $usuarios[] = $fila;
    }
}*/





// ================= CONSULTA =================
$sql = "SELECT 
            ai.id_inscrito,
            ai.id_usuario,
            ai.id_semestre,
            ai.horas_academicas,
            ai.horas_culturales,

            u.nombre,
            u.nombre_s,
            u.apellido_p,
            u.apellido_m,
            u.matricula,
            u.estado,

            c.nombre AS carrera,
            d.nombre AS modalidad,

            s.nombre AS semestre

        FROM alumno_inscrito ai

        LEFT JOIN usuario u 
            ON ai.id_usuario = u.id_usuario

        LEFT JOIN semestre s 
            ON ai.id_semestre = s.id_semestre

        LEFT JOIN carrera c 
            ON u.id_carrera = c.id_carrera

        LEFT JOIN modalidad d 
            ON u.id_modalidad = d.id_modalidad

        WHERE u.id_rol = 3 and u.id_modalidad = 16
        ORDER BY ai.id_inscrito DESC";

$result = $conn->query($sql);

// ================= GUARDAR DATOS EN ARRAY =================
$alumnos_inscritos = [];

if($result && $result->num_rows > 0) {
    while($fila = $result->fetch_assoc()) {
        $alumnos_inscritos[] = $fila;
    }
}



//  Consulta 2

$sql = "SELECT 
            ai.id_inscrito,
            ai.id_usuario,
            ai.id_semestre,
            ai.horas_academicas,
            ai.horas_culturales,

            u.nombre,
            u.nombre_s,
            u.apellido_p,
            u.apellido_m,
            u.matricula,
            u.estado,

            c.nombre AS carrera,
            d.nombre AS modalidad,

            s.nombre AS semestre

        FROM alumno_inscrito ai

        LEFT JOIN usuario u 
            ON ai.id_usuario = u.id_usuario

        LEFT JOIN semestre s 
            ON ai.id_semestre = s.id_semestre

        LEFT JOIN carrera c 
            ON u.id_carrera = c.id_carrera

        LEFT JOIN modalidad d 
            ON u.id_modalidad = d.id_modalidad

        WHERE u.id_rol = 3 and u.id_modalidad = 15
        ORDER BY ai.id_inscrito DESC";

$result = $conn->query($sql);

// ================= GUARDAR DATOS EN ARRAY =================
$alumnos_inscritoss = [];

if($result && $result->num_rows > 0) {
    while($fila = $result->fetch_assoc()) {
        $alumnos_inscritoss[] = $fila;
    }
}

?>





<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alumnos Inscritos - GEHAC </title>
    <link rel="stylesheet" href="css/alumnos_inscritos.css">
    <link rel="icon" href="img/gehac.png" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="js/alumnos_inscritos.js"></script>
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
                <a href="alumnos_inscritos.php" class="active">
                    <i class="fas fa-user-check" style="color:#4031a1"></i>
                    <span>Alumnos Inscritos</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('inscritos_actividades.php', 'VER')): ?>
            <li>
                <a href="inscritos_actividades.php">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Inscritos a Actividades</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('catalogo.php', 'VER')): ?>
            <li>
                <a href="catalogo.php">
                    <i class="fas fa-book-open"></i>
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
                <h3>Alumnos Inscritos al Semestre - GEHAC</h3>
            </div>

        </section>
        

   

        <!-- ÁREA DE TRABAJO -->
        <section class="content-wrapper">


            <div class="card">

                <div class="products-header">

                    <?php if (tiene_permiso('alumnos_inscritos.php', 'CREAR')): ?>
                        <a href="alumnos_inscritos_crear.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> 
                            <span>Nuevo</span>    
                        </a>
                    <?php endif; ?>


                    <?php if (tiene_permiso('alumnos_inscritos.php', 'EDITAR')): ?>
                        <a href="actualizar_horas.php" class="btn btn-sec">
                            <i class="fas fa-sync-alt fa-spin"></i> 
                            <span>Actualizar</span>    
                        </a>
                    <?php endif; ?>

                </div>

                <div class="estado-section">

                        <h2 class="estado-title title-proceso">
                            🟢 Alumnos Inscritos - Escolarizado
                        </h2>

                        

                        <table id="tablaAlumnos" class="estado-table">

                            <thead>
                                <tr>
                                    <th>Alumno</th>   
                                    <th>Semestre</th>
                                    <th>Horas Acádemicas</th>
                                    <th>Horas Culturales</th>
                                    <th>Modalidad</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>

                            <tbody>

                                

                                <?php if(!empty($alumnos_inscritos)): ?>

                                    <?php foreach($alumnos_inscritos as $row): ?>

                                        <tr>

                                            <!-- NOMBRE -->
                                            <td>
                                                <?= htmlspecialchars($row['nombre']) ?>
                                                <?= !empty($row['nombre_s']) ? htmlspecialchars($row['nombre_s']) : '' ?>
                                                <?= !empty($row['apellido_p']) ? htmlspecialchars($row['apellido_p']) : '' ?>
                                                <?= !empty($row['apellido_m']) ? htmlspecialchars($row['apellido_m']) : '' ?>
                                            </td>

                                            

                                            <td>
                                                <?= htmlspecialchars($row['semestre']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['horas_academicas']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['horas_culturales']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['modalidad']) ?>
                                            </td>

                                           
                                            

                                            <!-- ACCIONES       -->
                                            <td>

                                                <?php $id = (int)$row['id_inscrito']; ?>


                                                    <?php if (tiene_permiso('alumnos_inscritos.php', 'EDITAR')): ?>
                                                        <a href="alumnos_inscritos_editar.php?id=<?= $id ?>" class="btn-edit">
                                                            <i class="fas fa-pen"></i>
                                                            <span>Editar</span>
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if (tiene_permiso('alumnos_inscritos.php', 'BORRAR')): ?>
                                                        <a href="alumnos_inscritos_borrar.php?id=<?= $id ?>"
                                                            class="btn-delete"
                                                            onclick="return confirm('¿Eliminar al Alumno: <?= htmlspecialchars($row['nombre'].' '.$row['apellido_p'], ENT_QUOTES, 'UTF-8') ?> \ndel <?= htmlspecialchars($row['semestre']) ?>?')">

                                                                <i class="fas fa-trash"></i>
                                                                <span>Borrar</span>

                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if (!tiene_permiso('alumnos_inscritos.php', 'EDITAR') && !tiene_permiso('alumnos_inscritos.php', 'BORRAR')): ?>         
                                                        <span class="text-muted">Lectura</span>                                            
                                                    <?php endif; ?>

                                                

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>

                                        <td colspan="4" style="text-align:center">

                                            No existen Alumnos Inscritos al Semestre

                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                </div>

                <div class="estado-section">

                        <h2 class="estado-title title-proceso">
                            🟠 Alumnos Inscritos - Sabatino
                        </h2>

                        

                        <table id="tablaAlumnos" class="estado-table">

                            <thead>
                                <tr>
                                    <th>Alumno</th>   
                                    <th>Semestre</th>
                                    <th>Horas Acádemicas</th>
                                    <th>Horas Culturales</th>
                                    <th>Modalidad</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>

                            <tbody>

                                

                                <?php if(!empty($alumnos_inscritoss)): ?>

                                    <?php foreach($alumnos_inscritoss as $row): ?>

                                        <tr>

                                            <!-- NOMBRE -->
                                            <td>
                                                <?= htmlspecialchars($row['nombre']) ?>
                                                <?= !empty($row['nombre_s']) ? htmlspecialchars($row['nombre_s']) : '' ?>
                                                <?= !empty($row['apellido_p']) ? htmlspecialchars($row['apellido_p']) : '' ?>
                                                <?= !empty($row['apellido_m']) ? htmlspecialchars($row['apellido_m']) : '' ?>
                                            </td>

                                            

                                            <td>
                                                <?= htmlspecialchars($row['semestre']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['horas_academicas']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['horas_culturales']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['modalidad']) ?>
                                            </td>

                                           
                                            

                                            <!-- ACCIONES       -->
                                            <td>

                                                <?php $id = (int)$row['id_inscrito']; ?>


                                                    <?php if (tiene_permiso('alumnos_inscritos.php', 'EDITAR')): ?>
                                                        <a href="alumnos_inscritos_editar.php?id=<?= $id ?>" class="btn-edit">
                                                            <i class="fas fa-pen"></i>
                                                            <span>Editar</span>
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if (tiene_permiso('alumnos_inscritos.php', 'BORRAR')): ?>
                                                        <a href="alumnos_inscritos_borrar.php?id=<?= $id ?>"
                                                            class="btn-delete"
                                                            onclick="return confirm('¿Eliminar al Alumno: <?= htmlspecialchars($row['nombre'].' '.$row['apellido_p'], ENT_QUOTES, 'UTF-8') ?> \ndel <?= htmlspecialchars($row['semestre']) ?>?')">

                                                                <i class="fas fa-trash"></i>
                                                                <span>Borrar</span>

                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if (!tiene_permiso('alumnos_inscritos.php', 'EDITAR') && !tiene_permiso('alumnos_inscritos.php', 'BORRAR')): ?>         
                                                        <span class="text-muted">Lectura</span>                                            
                                                    <?php endif; ?>

                                                

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>

                                        <td colspan="4" style="text-align:center">

                                            No existen Alumnos Inscritos al Semestre

                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

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