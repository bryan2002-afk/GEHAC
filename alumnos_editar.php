<?php
session_start();
include("auth.php");
include("conexion.php");

// ================= MENSAJE =================
$mensaje = $_SESSION['mensaje'] ?? ["tipo" => "", "texto" => ""];
unset($_SESSION['mensaje']);

// ================= OBTENER SELECTS =================
$carreras = [];
$result_cat = $conn->query("SELECT id_carrera, nombre FROM carrera ORDER BY nombre ASC");
if ($result_cat) while ($row = $result_cat->fetch_assoc()) $carreras[] = $row;

$modalidads = [];
$result_cat = $conn->query("SELECT id_modalidad, nombre FROM modalidad ORDER BY nombre ASC");
if ($result_cat) while ($row = $result_cat->fetch_assoc()) $modalidads[] = $row;

$roles = [];
$result_cat = $conn->query("SELECT id_rol, nombre FROM rol WHERE id_rol = 3 ORDER BY nombre ASC");
if ($result_cat) while ($row = $result_cat->fetch_assoc()) $roles[] = $row;

// ================= OBTENER DATOS DEL USUARIO =================
if (!isset($_GET['id'])) {
    $_SESSION['mensaje'] = ["tipo" => "error", "texto" => "No se especificó un Alumno ❌"];
    header("Location: alumnos.php");
    exit();
}

$id_usuario = (int)$_GET['id'];
$sql_usuario = "SELECT * FROM usuario WHERE id_usuario = ?";
$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $id_usuario);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();

if (!$result_usuario || $result_usuario->num_rows == 0) {
    $_SESSION['mensaje'] = ["tipo" => "error", "texto" => "Alumno no encontrado ❌"];
    header("Location: alumnos.php");
    exit();
}

$usuario = $result_usuario->fetch_assoc();
$stmt_usuario->close();

// ================= PROCESAR FORMULARIO =================
if (isset($_POST['guardar'])) {

    $nombre       = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
    $nombre_s     = htmlspecialchars(trim($_POST['nombre_s']), ENT_QUOTES, 'UTF-8');
    $apellido_p   = htmlspecialchars(trim($_POST['apellido_p']), ENT_QUOTES, 'UTF-8');
    $apellido_m   = htmlspecialchars(trim($_POST['apellido_m']), ENT_QUOTES, 'UTF-8');
    $matricula    = htmlspecialchars(trim($_POST['matricula']), ENT_QUOTES, 'UTF-8');
    $correo       = htmlspecialchars(trim($_POST['correo']), ENT_QUOTES, 'UTF-8');
    $password     = $_POST['password'];
    $id_carrera   = (int)$_POST['id_carrera'];
    $id_modalidad = (int)$_POST['id_modalidad'];
    $id_rol       = (int)$_POST['id_rol'];
    $estado       = (int)$_POST['estado'];

    // Validar campos obligatorios
    if (empty($nombre) || empty($apellido_p) || empty($correo) || !$id_carrera || !$id_modalidad || !$id_rol) {
        $_SESSION['mensaje'] = [
            "tipo" => "error",
            "texto" => "Complete todos los campos obligatorios ❌"
        ];
        header("Location: alumnos_editar.php?id=$id_usuario");
        exit();
    }

    // ================= VERIFICAR CORREO Y MATRICULA =================
    $sql_check = "SELECT * FROM usuario WHERE (correo = ? OR matricula = ?) AND id_usuario != ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ssi", $correo, $matricula, $id_usuario);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check && $result_check->num_rows > 0) {
        $_SESSION['mensaje'] = [
            "tipo" => "error",
            "texto" => "La matrícula o el correo ya existen ❌"
        ];
        $stmt_check->close();
        header("Location: alumnos_editar.php?id=$id_usuario");
        exit();
    }
    $stmt_check->close();

    // ================= ACTUALIZAR USUARIO =================
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql_update = "UPDATE usuario SET nombre=?, nombre_s=?, apellido_p=?, apellido_m=?, matricula=?, correo=?, password=?, id_carrera=?, id_modalidad=?, id_rol=?, estado=? WHERE id_usuario=?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param(
            "sssssssiiiii",
            $nombre, $nombre_s, $apellido_p, $apellido_m, $matricula, $correo, $password_hash,
            $id_carrera, $id_modalidad, $id_rol, $estado, $id_usuario
        );
    } else {
        $sql_update = "UPDATE usuario SET nombre=?, nombre_s=?, apellido_p=?, apellido_m=?, matricula=?, correo=?, id_carrera=?, id_modalidad=?, id_rol=?, estado=? WHERE id_usuario=?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param(
            "sssssssiiii",
            $nombre, $nombre_s, $apellido_p, $apellido_m, $matricula, $correo,
            $id_carrera, $id_modalidad, $id_rol, $estado, $id_usuario
        );
    }

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = [
            "tipo" => "success",
            "texto" => "Alumno actualizado correctamente ☑️"
        ];
    } else {
        $_SESSION['mensaje'] = [
            "tipo" => "error",
            "texto" => "Error al actualizar Alumno ❌ " . $conn->error
        ];
    }

    $stmt->close();
    header("Location: alumnos.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Alumno - GEHAC </title>
    <link rel="stylesheet" href="css/usuarios_editar.css">
    <link rel="icon" href="img/logo.ico" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="js/usuarios.js"></script>
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
                <a href="alumnos.php" class="active">
                    <i class="fas fa-user-graduate" style="color:#4031a1"></i>
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
                <h3>Editar Alumno - GEHAC</h3>
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
                            <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">

                            <div class="form-group">
                                <label>Nombre:</label>
                                <input type="text" name="nombre" required value="<?= htmlspecialchars($usuario['nombre']) ?>">
                            </div>    

                            <div class="form-group">
                                <label>Segundo Nombre:</label>
                                <input type="text" name="nombre_s" value="<?= htmlspecialchars($usuario['nombre_s']) ?>">
                            </div>

                            <div class="form-group">
                                <label>Apellido Paterno:</label>
                                <input type="text" name="apellido_p" value="<?= htmlspecialchars($usuario['apellido_p']) ?>">
                            </div>

                            <div class="form-group">
                                <label>Apellido Materno:</label>
                                <input type="text" name="apellido_m" value="<?= htmlspecialchars($usuario['apellido_m']) ?>">
                            </div>

                            <div class="form-group">
                                <label>Matricula:</label>
                                <input type="text" name="matricula" value="<?= htmlspecialchars($usuario['matricula']) ?>">
                            </div>

                            <div class="form-group">
                                <label>Correo:</label>
                                <input type="text" name="correo" required value="<?= htmlspecialchars($usuario['correo']) ?>">
                            </div>

                            <div class="form-group">
                                <label>Contraseña:</label>
                                <input type="password" name="password" placeholder="Dejar vacío para mantener contraseña">
                            </div>

                            <div class="form-group">
                                <label>Carrera:</label>
                                <select name="id_carrera" required>
                                    <option value="">-- Seleccione Carrera --</option>
                                    <?php foreach($carreras as $cat): ?>
                                        <option value="<?= $cat['id_carrera'] ?>"
                                            <?= $usuario['id_carrera'] == $cat['id_carrera'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>



                            <div class="form-group">
                                <label>Modalidad:</label>
                                <select name="id_modalidad" required>
                                    <option value="">-- Seleccione Modalidad --</option>
                                    <?php foreach($modalidads as $cat): ?>
                                        <option value="<?= $cat['id_modalidad'] ?>"
                                            <?= $usuario['id_modalidad'] == $cat['id_modalidad'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Rol:</label>
                                <select name="id_rol" required>
                                    
                                    <?php foreach($roles as $cat): ?>
                                        <option value="<?= $cat['id_rol'] ?>"
                                            <?= $usuario['id_rol'] == $cat['id_rol'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Estado:</label>
                                <select name="estado" required>
                                    <option value="1" <?= $usuario['estado'] == 1 ? 'selected' : '' ?>>Activo</option>
                                    <option value="0" <?= $usuario['estado'] == 0 ? 'selected' : '' ?>>No Activo</option>
                                </select>
                            </div>


                            

                            <div class="form-buttons">
                                <button type="submit" name="guardar">
                                    <i class="fas fa-save"></i> Actualizar 
                                </button>
                                <a href="alumnos.php">
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