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

// ================= OBTENER SELECTS =================
$departamentos = [];
$result_cat = $conn->query("SELECT id_departamento, nombre FROM departamento ORDER BY nombre ASC");
if ($result_cat) while ($row = $result_cat->fetch_assoc()) $departamentos[] = $row;

$semestres = [];
$result_cat = $conn->query("SELECT id_semestre, nombre FROM semestre ORDER BY nombre DESC");
if ($result_cat) while ($row = $result_cat->fetch_assoc()) $semestres[] = $row;

// ================= PROCESAR FORMULARIO =================
if (isset($_POST['guardar'])) {

    // 🔐 Sanitizar datos
    $nombre     = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
    $hora_inicio   = htmlspecialchars(trim($_POST['hora_inicio']), ENT_QUOTES, 'UTF-8');
    $hora_fin   = htmlspecialchars(trim($_POST['hora_fin']), ENT_QUOTES, 'UTF-8');
    $fecha = htmlspecialchars(trim($_POST['fecha']), ENT_QUOTES, 'UTF-8');
    $lugar = htmlspecialchars(trim($_POST['lugar']), ENT_QUOTES, 'UTF-8');
    $ponente  = htmlspecialchars(trim($_POST['ponente']), ENT_QUOTES, 'UTF-8');
    $cupo  = htmlspecialchars(trim($_POST['cupo']), ENT_QUOTES, 'UTF-8');
    $descripcion     = htmlspecialchars(trim($_POST['descripcion']), ENT_QUOTES, 'UTF-8');

    $id_departamento = (int)$_POST['id_departamento'];
    $id_semestre = (int)$_POST['id_semestre'];

    // ================= PROCESAR IMAGEN =================
    $imagen_nombre = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $archivo = $_FILES['imagen'];
        $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);

        $carpeta = 'img_actividades/';
        if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

        $imagen_nombre = uniqid('img_') . '.' . $ext;
        $destino = $carpeta . $imagen_nombre;

        if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
            $_SESSION['mensaje'] = [
                "tipo" => "error",
                "texto" => "Error al subir la imagen ❌"
            ];
            header("Location: actividades_crear.php");
            exit();
        }
    }

    // ❌ Validar campos obligatorios
    if (empty($nombre) || empty($hora_inicio) || empty($hora_fin) || empty($fecha) ||
        empty($lugar)  || empty($ponente) || empty($descripcion) || !$id_departamento || !$id_semestre) {
        $_SESSION['mensaje'] = [
            "tipo" => "error",
            "texto" => "Por favor complete todos los campos obligatorios ❌"
        ];
        header("Location: actividades_crear.php");
        exit();
    }

    // ================= VERIFICAR NOMBRE DUPLICADO =================
    $stmt_check = $conn->prepare("SELECT id_actividad FROM actividad WHERE nombre = ?");
    $stmt_check->bind_param("s", $nombre);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check && $result_check->num_rows > 0) {
        $_SESSION['mensaje'] = [
            "tipo" => "error",
            "texto" => "Este nombre ya existe. Por favor ingrese otro nombre ❌"
        ];
        $stmt_check->close();
        header("Location: actividades_crear.php");
        exit();
    }
    $stmt_check->close();

    // ================= INSERTAR ACTIVIDAD =================
    $sql = "INSERT INTO actividad 
        (nombre, hora_inicio, hora_fin, fecha, lugar, ponente, cupo, descripcion, imagen, id_departamento, id_semestre)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssissii",
        $nombre, $hora_inicio, $hora_fin, $fecha, $lugar, $ponente, $cupo, $descripcion, $imagen_nombre, $id_departamento, $id_semestre
    );

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = [
            "tipo" => "success",
            "texto" => "Actividad creada correctamente ☑️\nNombre: $nombre"
        ];
    } else {
        $_SESSION['mensaje'] = [
            "tipo" => "error",
            "texto" => "Error al crear actividad ❌ " . $conn->error
        ];
    }

    $stmt->close();
    header("Location: actividades.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Actividad - GEHAC </title>
    <link rel="stylesheet" href="css/actividades_crear.css">
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
                <h3>Crear Actividad - GEHAC</h3>
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
                                <label>Nombre:</label>
                                <input type="text" name="nombre" required>
                            </div>    

                            <div class="form-group">
                                <label>Hora de Inicio:</label>
                                <input type="time" name="hora_inicio" required>
                            </div>

                            <div class="form-group">
                                <label>Hora de Fin:</label>
                                <input type="time" name="hora_fin" required>
                            </div>

                            <div class="form-group">
                                <label>Fecha:</label>
                                <input type="date" name="fecha" required>
                            </div>

                            <div class="form-group">
                                <label>Lugar:</label>
                                <input type="text" name="lugar" required>
                            </div>

                            <div class="form-group">
                                <label>Ponente:</label>
                                <input type="text" name="ponente" required>
                            </div>

                            <div class="form-group">
                                <label>Cupo:</label>
                                <input type="number" name="cupo" required>
                            </div>

                            <div class="form-group">
                                <label>Descripción:</label>
                                <input type="text" name="descripcion" required>
                            </div>

                            
                            <!--<div class="form-group ">
                                <label>Imagen:</label>
                                <input type="file" name="imagen" accept="image/*" onchange="previewImage(event)">
                                <img id="preview" src="#" alt="Preview">
                            </div>-->

                            

                            <div class="form-group">
                                <label>Departamento:</label>
                                <select name="id_departamento" required>
                                    <option value="">-- Seleccione Departamento --</option>
                                    <?php foreach($departamentos as $cat): ?>
                                        <option value="<?php echo $cat['id_departamento']; ?>">
                                            <?php echo htmlspecialchars($cat['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>



                            <div class="form-group">
                                <label>Semestre:</label>
                                <select name="id_semestre" required>
                                    <option value="">-- Seleccione Semestre --</option>
                                    <?php foreach($semestres as $cat): ?>
                                        <option value="<?php echo $cat['id_semestre']; ?>">
                                            <?php echo htmlspecialchars($cat['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group ">
                                <label>Imagen:</label>
                                <input type="file" name="imagen" accept="image/*" onchange="previewImage(event)">
                                <img id="preview" src="#" alt="Preview">
                            </div>

                          
                            <br>
                            

                            <div class="form-buttons">
                                <button type="submit" name="guardar">
                                    <i class="fas fa-save"></i> Guardar 
                                </button>
                                <a href="actividades.php">
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