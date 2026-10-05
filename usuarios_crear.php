<?php
session_start();
include("auth.php");
include("conexion.php");

// ================= MENSAJE =================
$mensaje = $_SESSION['mensaje'] ?? ["tipo" => "", "texto" => ""];
unset($_SESSION['mensaje']);


// ================= FUNCION PARA CONSULTAS =================
function obtenerLista($conn, $sql) {
    $data = [];
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

// ================= OBTENER SELECTS =================
$carreras = obtenerLista($conn, "SELECT id_carrera, nombre FROM carrera ORDER BY nombre ASC");
$modalidads = obtenerLista($conn, "SELECT id_modalidad, nombre FROM modalidad ORDER BY nombre ASC");
$roles = obtenerLista($conn, "SELECT id_rol, nombre FROM rol WHERE id_rol != 3 ORDER BY nombre ASC");


// ================= PROCESAR FORMULARIO =================
if (isset($_POST['guardar'])) {

    // ================= SANITIZAR =================
    $nombre     = trim($_POST['nombre']);
    $nombre_s   = trim($_POST['nombre_s']);
    $apellido_p = trim($_POST['apellido_p']);
    $apellido_m = trim($_POST['apellido_m']);

    $correo     = strtolower(trim($_POST['correo']));
    $password   = trim($_POST['password']);

    $matricula  = !empty(trim($_POST['matricula']))
        ? trim($_POST['matricula'])
        : NULL;

    $id_carrera   = !empty($_POST['id_carrera']) ? (int)$_POST['id_carrera'] : NULL;
    $id_modalidad = !empty($_POST['id_modalidad']) ? (int)$_POST['id_modalidad'] : NULL;
    $id_rol       = !empty($_POST['id_rol']) ? (int)$_POST['id_rol'] : NULL;

    // ================= VALIDAR OBLIGATORIOS =================
    if (empty($nombre) || empty($correo) || empty($password)) {

        $_SESSION['mensaje'] = [
            "tipo" => "error",
            "texto" => "Nombre, correo y contraseña son obligatorios ❌"
        ];

        header("Location: usuarios_crear.php");
        exit();
    }

    // ================= VALIDAR EMAIL =================
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

        $_SESSION['mensaje'] = [
            "tipo" => "error",
            "texto" => "Correo inválido ❌"
        ];

        header("Location: usuarios_crear.php");
        exit();
    }

    // ================= VERIFICAR CORREO =================
    $sql_check = "SELECT id_usuario FROM usuario WHERE correo = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $correo);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {

        $_SESSION['mensaje'] = [
            "tipo" => "error",
            "texto" => "El correo ya está registrado ❌"
        ];

        $stmt_check->close();
        header("Location: usuarios_crear.php");
        exit();
    }

    $stmt_check->close();

    // ================= PASSWORD HASH =================
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // ================= INSERT =================
    $sql = "INSERT INTO usuario (
                nombre, nombre_s, apellido_p, apellido_m,
                matricula, correo, password,
                id_carrera, id_modalidad, id_rol, estado
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "sssssssiii",
        $nombre,
        $nombre_s,
        $apellido_p,
        $apellido_m,
        $matricula,
        $correo,
        $password_hash,
        $id_carrera,
        $id_modalidad,
        $id_rol
    );

    if ($stmt->execute()) {

        $_SESSION['mensaje'] = [
            "tipo" => "success",
            "texto" => "Usuario creado correctamente ☑️"
        ];

    } else {

        $_SESSION['mensaje'] = [
            "tipo" => "error",
            "texto" => "Error al crear usuario ❌ " . $conn->error
        ];
    }

    $stmt->close();

    header("Location: usuarios.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Usuario - GEHAC </title>
    <link rel="stylesheet" href="css/usuarios_crear.css">
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

    .label-required::after{
    content: " *";
    color: red;
    font-weight: bold;
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
                <a href="usuarios.php" class="active">
                    <i class="fas fa-users" style="color:#4031a1"></i>
                    <span>Usuarios</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('roles.php', 'VER')): ?>
            <li>
                <a href="roles.php">
                    <i class="fas fa-user-shield"></i>
                    <span>Roles</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('blogs.php', 'VER')): ?>
            <li>
                <a href="blogs.php">
                    <i class="fas fa-blog"></i>
                    <span>Blogs</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('escuelas.php', 'VER')): ?>
            <li>
                <a href="escuelas.php">
                    <i class="fas fa-school"></i>
                    <span>Escuelas</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('modalidades.php', 'VER')): ?>
            <li>
                <a href="modalidades.php">
                    <i class="fas fa-laptop-house"></i>
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
                <h3>Crear Usuario - GEHAC</h3>
            </div>

        </section>
        

   

        <!-- ÁREA DE TRABAJO -->
        <section class="content-wrapper">


            <div class="card">

                

                <div class="estado-section">

                        
                        <p>
                            🟢 Ingrese los datos.
                        </p>

                        <!-- Ingresar el Form -->
                         <!-- Formulario con valores retenidos -->

                        <form action="" method="POST" enctype="multipart/form-data" class="form-producto">

                            <div class="form-group">
                                <label class="label-required">Nombre:</label>
                                <input type="text" name="nombre" required>
                            </div>    

                            <div class="form-group">
                                <label>Segundo Nombre:</label>
                                <input type="text" name="nombre_s" >
                            </div>

                            <div class="form-group">
                                <label>Apellido Paterno:</label>
                                <input type="text" name="apellido_p" >
                            </div>

                            <div class="form-group">
                                <label>Apellido Materno:</label>
                                <input type="text" name="apellido_m" >
                            </div>

                            <div class="form-group">
                                <label>Matricula:</label>
                                <input type="text" name="matricula" >
                            </div>

                            <div class="form-group">
                                <label class="label-required">Correo:</label>
                                <input type="text" name="correo" required>
                            </div>

                            <div class="form-group">
                                <label class="label-required">Contraseña:</label>
                                <input type="password" name="password" required>
                            </div>

                            <div class="form-group">
                                <label>Carrera:</label>
                                <select name="id_carrera" >
                                    <option value="">-- Seleccione Carrera --</option>
                                    <?php foreach($carreras as $cat): ?>
                                        <option value="<?php echo $cat['id_carrera']; ?>">
                                            <?php echo htmlspecialchars($cat['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>



                            <div class="form-group">
                                <label>Modalidad:</label>
                                <select name="id_modalidad" >
                                    <option value="">-- Seleccione Modalidad --</option>
                                    <?php foreach($modalidads as $cat): ?>
                                        <option value="<?php echo $cat['id_modalidad']; ?>">
                                            <?php echo htmlspecialchars($cat['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="label-required">Rol:</label>
                                <select name="id_rol" required>
                                    <option value="">-- Seleccione Rol --</option>
                                    <?php foreach($roles as $cat): ?>
                                        <option value="<?php echo $cat['id_rol']; ?>">
                                            <?php echo htmlspecialchars($cat['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>


                            

                            <div class="form-buttons">
                                <button type="submit" name="guardar">
                                    <i class="fas fa-save"></i> Guardar 
                                </button>
                                <a href="usuarios.php">
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