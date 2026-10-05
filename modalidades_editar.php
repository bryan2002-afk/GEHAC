<?php
include("auth.php"); // Protege la página
include("conexion.php");

$mensaje = null;
$modalidad = null;

// ================================
// OBTENER ID PARA EDITAR
// ================================
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: modalidades.php"); // Redirige si no hay ID
    exit();
}

$id_modalidad = (int) $_GET['id'];

// ================================
// CARGAR DATOS EXISTENTES
// ================================
$stmt = $conn->prepare("SELECT * FROM modalidad WHERE id_modalidad = ?");
$stmt->bind_param("i", $id_modalidad);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['mensaje'] = [
        'tipo' => 'error',
        'texto' => "Modalidad no encontrada ❌"
    ];
    header("Location: modalidades.php");
    exit();
}

$modalidad = $result->fetch_assoc();
$stmt->close();

// ================================
// PROCESAR FORMULARIO
// ================================
if (isset($_POST['guardar'])) {

    $nombre = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
    $horas_academicas = (int) $_POST['horas_academicas'];
    $horas_culturales = (int) $_POST['horas_culturales'];

    // Validación
    if (empty($nombre)) {
        $mensaje = [
            'tipo' => 'error',
            'texto' => "El campo Nombre es obligatorio ❌"
        ];
    } elseif ($horas_academicas < 0 || $horas_culturales < 0) {
        $mensaje = [
            'tipo' => 'error',
            'texto' => "Las horas no pueden ser negativas ❌"
        ];
    } else {
        // Evitar duplicados (excepto el mismo registro)
        $check = $conn->prepare("SELECT id_modalidad FROM modalidad WHERE nombre = ? AND id_modalidad != ?");
        $check->bind_param("si", $nombre, $id_modalidad);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $mensaje = [
                'tipo' => 'error',
                'texto' => "Ya existe una modalidad con ese nombre ❌"
            ];
        } else {
            // Actualizar en la BD
            $update = $conn->prepare("UPDATE modalidad SET nombre = ?, horas_academicas = ?, horas_culturales = ? WHERE id_modalidad = ?");
            $update->bind_param("siii", $nombre, $horas_academicas, $horas_culturales, $id_modalidad);

            if ($update->execute()) {
                $_SESSION['mensaje'] = [
                    'tipo' => 'success',
                    'texto' => "Modalidad actualizada correctamente ☑️"
                ];
                header("Location: modalidades.php");
                exit();
            } else {
                $mensaje = [
                    'tipo' => 'error',
                    'texto' => "Error al actualizar la modalidad ❌"
                ];
            }
            $update->close();
        }

        $check->close();
    }
}
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Modalidad - GEHAC </title>
    <link rel="stylesheet" href="css/modalidades_editar.css">
    <link rel="icon" href="img/gehac.png" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="js/modalidades.js"></script>
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
                <a href="modalidades.php" class="active">
                    <i class="fas fa-laptop-house"  style="color:#4031a1"></i>
                    <span>Modalidades</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('carreras.php', 'VER')): ?>
            <li>
                <a href="carreras.php">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Carreras</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('semestres.php', 'VER')): ?>
            <li>
                <a href="semestres.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Semestres</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('alumnos.php', 'VER')): ?>
            <li>
                <a href="alumnos.php">
                    <i class="fas fa-user-graduate"></i>
                    <span>Alumnos</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('departamentos.php', 'VER')): ?>
            <li>
                <a href="departamentos.php">
                    <i class="fas fa-building"></i>
                    <span>Departamentos</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('actividades.php', 'VER')): ?>
            <li>
                <a href="actividades.php">
                    <i class="fas fa-tasks"></i>
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
                <h3>Editar Modalidad - GEHAC</h3>
            </div>

        </section>
        

   

        <!-- ÁREA DE TRABAJO -->
        <section class="content-wrapper">


            <div class="card">

                

                <div class="estado-section">

                        
                        <p>
                            🟢 Edite los datos.
                        </p>

                        <!-- Ingresar el Form -->
                         <!-- Formulario con valores retenidos -->

                        <form action="" method="POST" enctype="multipart/form-data" class="form-producto">

                            <!-- ID oculto para actualizar -->
                            <input type="hidden" name="id_modalidad" value="<?= $modalidad['id_modalidad'] ?>">

                            <div class="form-group">
                                <label>Nombre:</label>
                                <input type="text" name="nombre" required value="<?= htmlspecialchars($modalidad['nombre']) ?>">
                            </div>    

                            <div class="form-group">
                                <label>Horas Academicas:</label>
                                <input type="number" name="horas_academicas"  required value="<?= htmlspecialchars($modalidad['horas_academicas']) ?>">
                            </div>

                            <div class="form-group">
                                <label>Horas Culturales:</label>
                                <input type="number" name="horas_culturales" required value="<?= htmlspecialchars($modalidad['horas_culturales']) ?>">
                            </div>

                            

                            <div class="form-buttons">
                                <button type="submit" name="guardar">
                                    <i class="fas fa-save"></i> Actualizar 
                                </button>
                                <a href="modalidades.php">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </form>

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