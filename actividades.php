<?php
session_start();
include("auth.php");
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

// ================= CONSULTAR USUARIOS =================

$sql = "SELECT a.id_actividad, a.nombre, a.hora_inicio, a.hora_fin, a.fecha, a.lugar, a.ponente, a.cupo, a.estado,
            c.nombre AS departamento
        FROM actividad a
        LEFT JOIN departamento c ON a.id_departamento = c.id_departamento
        ORDER BY a.id_actividad DESC";

$result = $conn->query($sql);




// ================= GUARDAR DATOS EN ARRAY =================
$actividades = [];

if($result && $result->num_rows > 0) {
    while($fila = $result->fetch_assoc()) {
        $actividades[] = $fila;
    }
}
?>





<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actividades - GEHAC </title>
    <link rel="stylesheet" href="css/actividades.css">
    <link rel="icon" href="img/logo.ico" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="js/actividades.js"></script>
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
                <a href="actividades.php" class="active">
                    <i class="fas fa-tasks" style="color:#4031a1"></i>
                    <span>Actividades</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('categorias.php', 'VER')): ?>
            <li>
                <a href="categorias.php">
                    <i class="fas fa-tags"></i>
                    <span>Categorias</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('horas.php', 'VER')): ?>
            <li>
                <a href="horas.php">
                    <i class="fas fa-clock"></i>
                    <span>Número de Horas</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('alumnos_inscritos.php', 'VER')): ?>
            <li>
                <a href="alumnos_inscritos.php">
                    <i class="fas fa-user-check"></i>
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
                <h3>Actividades - GEHAC</h3>
            </div>

        </section>
        

   

        <!-- ÁREA DE TRABAJO -->
        <section class="content-wrapper">


            <div class="card">

                <div class="products-header">

                    <?php if (tiene_permiso('actividades.php', 'CREAR')): ?>
                        <a href="actividades_crear.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> 
                            <span>Nuevo</span>    
                        </a>
                    <?php endif; ?>

                </div>

                <div class="estado-section">

                        <h2 class="estado-title title-proceso">
                            🟢 Actividades
                        </h2>

                        <table id="tablaActividades" class="estado-table">

                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Horario</th>
                                    <th>Fecha</th>
                                    <th>Lugar</th>
                                    <th>Ponente</th>
                                    <th>Cupo</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>

                            <tbody>

                                

                                <?php if(!empty($actividades)): ?>

                                    <?php foreach($actividades as $row): ?>

                                        <tr>

                                            <!-- NOMBRE -->
                                            <td>
                                                <?= htmlspecialchars($row['nombre']) ?>
                                            </td>

                                            <!-- DIRECCIÓN -->
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
                                                <?= htmlspecialchars($row['ponente']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['cupo']) ?>
                                            </td>

                                            <td>
                                                <?php if ($row['estado'] == 1): ?>
                                                    <span style="color: green; font-weight: bold;">Activo</span>
                                                <?php else: ?>
                                                    <span style="color: red; font-weight: bold;">Inactivo</span>
                                                <?php endif; ?>
                                            </td>

                                           

                                            <!-- ACCIONES -->
                                            <td>

                                                <?php $id=(int)$row['id_actividad']; ?>

                                                    <?php if (tiene_permiso('actividades.php', 'EDITAR')): ?>
                                                        <a href="toggle_estado_actividades.php?id=<?= $id ?>&estado=<?= $row['estado'] ?>" class="btn-edit">
                                                            🔄 
                                                        </a>

                                                    

                                                        <a href="actividades_editar.php?id=<?= $id ?>" class="btn-edit">
                                                            <i class="fas fa-pen"></i>
                                                            <span>Editar</span>
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if (tiene_permiso('actividades.php', 'BORRAR')): ?>
                                                        <a href="actividades_borrar.php?id=<?= $id ?>"
                                                        class="btn-delete"
                                                        onclick="return confirm('¿Eliminar la Actividad: <?= htmlspecialchars($row['nombre']) ?>?')">

                                                            <i class="fas fa-trash"></i>
                                                            <span>Borrar</span>

                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if (!tiene_permiso('actividades.php', 'EDITAR') && !tiene_permiso('actividades.php', 'BORRAR')): ?>         
                                                        <span class="text-muted">Lectura</span>                                            
                                                    <?php endif; ?>

                                                

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