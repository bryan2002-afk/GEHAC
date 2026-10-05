<?php
session_start();
include("auth.php");
include("conexion.php");

// ================= MENSAJE =================
$mensaje = $_SESSION['mensaje'] ?? null;
unset($_SESSION['mensaje']);

// ================= OBTENER ID DE LA CARRERA =================
if (!isset($_GET['id'])) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'No se especificó la carrera ❌'];
    header("Location: carreras.php");
    exit();
}

$id_carrera = (int)$_GET['id'];

// ================= OBTENER DATOS DE LA CARRERA =================
$sql_carrera = "SELECT * FROM carrera WHERE id_carrera = ?";
$stmt_carrera = $conn->prepare($sql_carrera);
$stmt_carrera->bind_param("i", $id_carrera);
$stmt_carrera->execute();
$result_carrera = $stmt_carrera->get_result();

if (!$result_carrera || $result_carrera->num_rows === 0) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Carrera no encontrada ❌'];
    header("Location: carreras.php");
    exit();
}

$carrera = $result_carrera->fetch_assoc();
$stmt_carrera->close();

// ================= PROCESAR FORMULARIO =================
if (isset($_POST['guardar'])) {
    // 🔐 Sanitizar datos
    $nombre = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
    $revoe = htmlspecialchars(trim($_POST['revoe']), ENT_QUOTES, 'UTF-8');

    // ❌ Validar campos obligatorios
    if (empty($nombre) || empty($revoe)) {
        $mensaje = ['tipo' => 'error', 'texto' => 'Todos los campos son obligatorios ❌'];
    } else {
        // ================= VERIFICAR DUPLICADOS =================
        $checkNombre = $conn->prepare("SELECT id_carrera FROM carrera WHERE nombre = ? AND id_carrera != ?");
        $checkNombre->bind_param("si", $nombre, $id_carrera);
        $checkNombre->execute();
        $checkNombre->store_result();

        if ($checkNombre->num_rows > 0) {
            $mensaje = ['tipo' => 'error', 'texto' => 'Ya existe una carrera con ese nombre ❌'];
        } else {
            // ================= ACTUALIZAR EN LA BD =================
            $sql_update = "UPDATE carrera SET nombre = ?, revoe = ? WHERE id_carrera = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssi", $nombre, $revoe, $id_carrera);

            if ($stmt_update->execute()) {
                $_SESSION['mensaje'] = [
                    'tipo' => 'success',
                    'texto' => "Carrera actualizada correctamente ☑️\nNombre: $nombre"
                ];
                header("Location: carreras.php");
                exit();
            } else {
                $mensaje = ['tipo' => 'error', 'texto' => 'Error al actualizar la carrera ❌'];
            }

            $stmt_update->close();
        }

        $checkNombre->close();
    }

    // Conservar valores en caso de error
    $carrera['nombre'] = $nombre;
    $carrera['revoe'] = $revoe;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Carreras - GEHAC </title>
    <link rel="stylesheet" href="css/carreras_editar.css">
    <link rel="icon" href="img/logo.ico" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="js/carreras.js"></script>
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
                <a href="carreras.php" class="active">
                    <i class="fas fa-graduation-cap" style="color:#4031a1"></i>
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
                <h3>Editar Carreras - GEHAC</h3>
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
                            <input type="hidden" name="id_carrera" value="<?= $carrera['id_carrera'] ?>">

                            <div class="form-group">
                                <label>Nombre:</label>
                                <input type="text" name="nombre" required value="<?= htmlspecialchars($carrera['nombre']) ?>">
                            </div>    

                            <div class="form-group">
                                <label>Revoe:</label>
                                <input type="text" name="revoe" required value="<?= htmlspecialchars($carrera['revoe']) ?>">
                            </div>

                          

                            

                            <div class="form-buttons">
                                <button type="submit" name="guardar">
                                    <i class="fas fa-save"></i> Actualizar 
                                </button>
                                <a href="carreras.php">
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