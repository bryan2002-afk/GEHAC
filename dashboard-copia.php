<?php
include("auth.php"); // 👈 protege la página

include("conexion.php");



// Obtener el id_empleado desde la sesión
$id_usuario = $_SESSION['id_usuario']; // Esto lo guardamos durante el login


//  suma de USUARIOS existentes
$sqlTotal = "SELECT COUNT(*) AS total FROM usuario";
$resTotal = $conn->query($sqlTotal);

if ($resTotal && $filaTotal = $resTotal->fetch_assoc()) {
    $totalUsuarios = $filaTotal['total'];
}


//  suma de CARRERAS existentes
$sqlTotal = "SELECT COUNT(*) AS total FROM carrera";
$resTotal = $conn->query($sqlTotal);

if ($resTotal && $filaTotal = $resTotal->fetch_assoc()) {
    $totalCarreras = $filaTotal['total'];
}


//  suma de ACTIVIDADES existentes
$sqlTotal = "SELECT COUNT(*) AS total FROM actividad";
$resTotal = $conn->query($sqlTotal);

if ($resTotal && $filaTotal = $resTotal->fetch_assoc()) {
    $totalActividades = $filaTotal['total'];
}


//  suma de ALUMNOS  existentes
$sqlTotal = "SELECT COUNT(*) AS total FROM usuario WHERE id_rol = 3 ";
$resTotal = $conn->query($sqlTotal);

if ($resTotal && $filaTotal = $resTotal->fetch_assoc()) {
    $totalAlum2 = $filaTotal['total'];
}


//  suma de ALUMNOS INSCRITOS AL SEMESTRE existentes
$sqlTotal = "SELECT COUNT(*) AS total FROM alumno_inscrito";
$resTotal = $conn->query($sqlTotal);

if ($resTotal && $filaTotal = $resTotal->fetch_assoc()) {
    $totalAlum = $filaTotal['total'];
}


//  suma de ACTIVIDADES existentes
$sqlTotal = "SELECT COUNT(*) AS total FROM actividad WHERE estado = 1 ";
$resTotal = $conn->query($sqlTotal);

if ($resTotal && $filaTotal = $resTotal->fetch_assoc()) {
    $totalActividades2 = $filaTotal['total'];
}

?>





<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - GEHAC </title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="icon" href="img/gehac.png" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="js/dashboard.js"></script>
</head>




<body>


    <div class="sidebar" id="sidebar">


        <a href="dashboard.php" class="logo-link">
    
            <div class="logo-container">
                <img src="img/uni_4.png" alt="Logo UMMA" class="logo">
            </div>

        </a>


      

        <ul>
            <li>
                <a href="dashboard.php" class="active">
                    <i class="fas fa-th-large" style="color:#4031a1"></i>
                    <span> Dashboard</span>
                </a>
            </li>

            <li>
                <a href="usuarios.php">
                    <i class="fas fa-users"></i>
                    <span>Usuarios</span>
                </a>
            </li>

            <li>
                <a href="roles.php">
                    <i class="fas fa-user-shield"></i>
                    <span>Roles</span>
                </a>
            </li>

            <li>
                <a href="blogs.php">
                    <i class="fas fa-blog"></i>
                    <span>Blogs</span>
                </a>
            </li>

            <li>
                <a href="escuelas.php">
                    <i class="fas fa-school"></i>
                    <span>Escuelas</span>
                </a>
            </li>

            <li>
                <a href="modalidades.php">
                    <i class="fas fa-laptop-house"></i>
                    <span>Modalidades</span>
                </a>
            </li>

            <li>
                <a href="carreras.php">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Carreras</span>
                </a>
            </li>

            <li>
                <a href="semestres.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Semestres</span>
                </a>
            </li>

            <li>
                <a href="alumnos.php">
                    <i class="fas fa-user-graduate"></i>
                    <span>Alumnos</span>
                </a>
            </li>

            <li>
                <a href="departamentos.php">
                    <i class="fas fa-building"></i>
                    <span>Departamentos</span>
                </a>
            </li>

            <li>
                <a href="actividades.php">
                    <i class="fas fa-tasks"></i>
                    <span>Actividades</span>
                </a>
            </li>

            <li>
                <a href="categorias.php">
                    <i class="fas fa-tags"></i>
                    <span>Categorias</span>
                </a>
            </li>

            <li>
                <a href="horas.php">
                    <i class="fas fa-clock"></i>
                    <span>Número de Horas</span>
                </a>
            </li>

            <li>
                <a href="alumnos_inscritos.php">
                    <i class="fas fa-user-check"></i>
                    <span>Alumnos Inscritos</span>
                </a>
            </li>

            <li>
                <a href="inscritos_actividades.php">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Inscritos a Actividades</span>
                </a>
            </li>

            <li>
                <a href="catalogo.php">
                    <i class="fas fa-book-open"></i>
                    <span>Catalogo de Actividades</span>
                </a>
            </li>

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
                <h3>Dashboard - GEHAC</h3>
            </div>

        </section>
        

   

        <!-- CONTENIDO PRINCIPAL DERECHA -->
        <section class="main-content">

            <div class="card-grid">

                <a href="usuarios.php" class="card-link">

                    <div class="card">

                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>

                        <div class="card-info">
                            <h4>Usuarios</h4>
                            <p><?= $totalUsuarios ?> registrados</p>
                        </div>

                    </div>

                </a>


                <a href="carreras.php" class="card-link">

                    <div class="card">

                        <div class="card-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>

                        <div class="card-info">
                            <h4>Carreras</h4>
                            <p><?= $totalCarreras ?> carreras</p>
                        </div>

                    </div>

                </a>


                <a href="alumnos.php" class="card-link">

                    <div class="card">

                        <div class="card-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>

                        <div class="card-info">
                            <h4>Alumnos</h4>
                            <p><?= $totalAlum2 ?> registrados</p>
                        </div>

                    </div>

                </a>


                <a href="actividades.php" class="card-link">

                    <div class="card">

                        <div class="card-icon">
                            <i class="fas fa-tasks"></i>
                        </div>

                        <div class="card-info">
                            <h4>Actividades</h4>
                            <p><?= $totalActividades ?> Existentes</p>
                        </div>

                    </div>

                </a>


                <a href="alumnos_inscritos.php" class="card-link">

                    <div class="card">

                        <div class="card-icon">
                            <i class="fas fa-user-check"></i>
                        </div>

                        <div class="card-info">
                            <h4>Alumnos Inscritos</h4>
                            <p><?= $totalAlum ?> inscritos</p>
                        </div>

                    </div>

                </a>

                <a href="catalogo.php" class="card-link">

                    <div class="card">

                        <div class="card-icon">
                            <i class="fas fa-book-open"></i>
                        </div>

                        <div class="card-info">
                            <h4>Catálogo de Actividades</h4>
                            <p><?= $totalActividades2 ?> disponibles</p>
                        </div>

                    </div>

                </a>

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