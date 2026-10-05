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

// ================= CONSULTAR HORAS =================

$sql = "SELECT a.id_hora, a.horas, 
            c.nombre AS actividad,
            d.nombre AS categoria
        FROM hora a
        LEFT JOIN actividad c ON a.id_actividad = c.id_actividad
        LEFT JOIN categoria d ON a.id_categoria = d.id_categoria
        ORDER BY a.id_hora DESC";

$result = $conn->query($sql);




// ================= GUARDAR DATOS EN ARRAY =================
$horas = [];

if($result && $result->num_rows > 0) {
    while($fila = $result->fetch_assoc()) {
        $horas[] = $fila;
    }
}
?>






<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Número de Horas - GEHAC </title>
    <link rel="stylesheet" href="css/horas.css">
    <link rel="icon" href="img/gehac.png" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="js/horas.js"></script>
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
                <a href="horas.php" class="active">
                    <i class="fas fa-clock" style="color:#4031a1"></i>
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
                <h3>Número de Horas - GEHAC</h3>
            </div>

        </section>
        

   

        <!-- ÁREA DE TRABAJO -->
        <section class="content-wrapper">


            <div class="card">

                <div class="products-header">

                    <?php if (tiene_permiso('horas.php', 'CREAR')): ?>
                        <a href="horas_crear.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> 
                            <span>Nuevo</span>    
                        </a>
                    <?php endif; ?>

                </div>

                <div class="estado-section">

                        <h2 class="estado-title title-proceso">
                            🟢 Número de Horas
                        </h2>

                        <table id="tablaHoras" class="estado-table">

                            <thead>
                                <tr>
                                    <th>Horas</th>
                                    <th>Categoria</th>
                                    <th>Actividad</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>

                            <tbody>

                                

                                <?php if(!empty($horas)): ?>

                                    <?php foreach($horas as $row): ?>

                                        <tr>

                                            <!-- NOMBRE -->
                                            <td>
                                                <?= htmlspecialchars($row['horas']) ?> 
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['categoria']) ?> 
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['actividad']) ?> 
                                            </td>

                                           

                                            

                                            <!-- ACCIONES -->
                                            <td>

                                                <?php $id=(int)$row['id_hora']; ?>

                                                

                                                    <?php if (tiene_permiso('horas.php', 'EDITAR')): ?>
                                                        <a href="horas_editar.php?id=<?= $id ?>" class="btn-edit">
                                                            <i class="fas fa-pen"></i>
                                                            <span>Editar</span>
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if (tiene_permiso('horas.php', 'BORRAR')): ?>
                                                        <a href="horas_borrar.php?id=<?= $id ?>"
                                                        class="btn-delete"
                                                        onclick="return confirm('¿Eliminar <?= htmlspecialchars($row['horas']) ?> de <?= htmlspecialchars($row['actividad']) ?>?')">

                                                            <i class="fas fa-trash"></i>
                                                            <span>Borrar</span>

                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if (!tiene_permiso('horas.php', 'EDITAR') && !tiene_permiso('horas.php', 'BORRAR')): ?>         
                                                        <span class="text-muted">Lectura</span>                                            
                                                    <?php endif; ?>

                                                

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>

                                        <td colspan="4" style="text-align:center">

                                            No existen Horas registradas

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