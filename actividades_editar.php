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

// ================= VALIDAR ID =================
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "ID de Actividad inválido."
    ];
    header("Location: actividades.php");
    exit();
}

$id_actividad = (int)$_GET['id'];

// ================= OBTENER DATOS DE ACTIVIDAD =================
$stmt = $conn->prepare("SELECT * FROM actividad WHERE id_actividad = ?");
$stmt->bind_param("i", $id_actividad);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['mensaje'] = [
        "tipo" => "error",
        "texto" => "La actividad no existe."
    ];
    header("Location: actividades.php");
    exit();
}

$actividades = $result->fetch_assoc();
$stmt->close();

// ================= OBTENER SELECTS =================
$departamentos = [];
$result_cat = $conn->query("SELECT id_departamento, nombre FROM departamento ORDER BY nombre ASC");
if ($result_cat) while ($row = $result_cat->fetch_assoc()) $departamentos[] = $row;

$semestres = [];
$result_cat = $conn->query("SELECT id_semestre, nombre FROM semestre ORDER BY nombre ASC");
if ($result_cat) while ($row = $result_cat->fetch_assoc()) $semestres[] = $row;

// ================= PROCESAR FORMULARIO =================
if (isset($_POST['guardar'])) {

    $nombre       = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
    $hora_inicio  = htmlspecialchars(trim($_POST['hora_inicio']), ENT_QUOTES, 'UTF-8');
    $hora_fin     = htmlspecialchars(trim($_POST['hora_fin']), ENT_QUOTES, 'UTF-8');
    $fecha        = htmlspecialchars(trim($_POST['fecha']), ENT_QUOTES, 'UTF-8');
    $lugar        = htmlspecialchars(trim($_POST['lugar']), ENT_QUOTES, 'UTF-8');
    $ponente      = htmlspecialchars(trim($_POST['ponente']), ENT_QUOTES, 'UTF-8');
    $cupo      = htmlspecialchars(trim($_POST['cupo']), ENT_QUOTES, 'UTF-8');
    $descripcion  = htmlspecialchars(trim($_POST['descripcion']), ENT_QUOTES, 'UTF-8');
    $id_departamento = (int)$_POST['id_departamento'];
    $id_semestre    = (int)$_POST['id_semestre'];

    // ================= VALIDAR CAMPOS =================
    if (empty($nombre) || empty($hora_inicio) || empty($hora_fin) || empty($fecha) ||
        empty($lugar)  || empty($ponente) || empty($cupo) || empty($descripcion) || !$id_departamento || !$id_semestre) {
        $_SESSION['mensaje'] = [
            "tipo" => "error",
            "texto" => "Por favor complete todos los campos obligatorios ❌"
        ];
        header("Location: actividades_editar.php?id=$id_actividad");
        exit();
    }

    // ================= VERIFICAR NOMBRE DUPLICADO =================
    $stmt_check = $conn->prepare("SELECT id_actividad FROM actividad WHERE nombre = ? AND id_actividad != ?");
    $stmt_check->bind_param("si", $nombre, $id_actividad);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check && $result_check->num_rows > 0) {
        $_SESSION['mensaje'] = [
            "tipo" => "error",
            "texto" => "Este nombre ya existe. Por favor ingrese otro nombre ❌"
        ];
        $stmt_check->close();
        header("Location: actividades_editar.php?id=$id_actividad");
        exit();
    }
    $stmt_check->close();

    // ================= PROCESAR IMAGEN =================
    $imagen_nombre = $actividades['imagen']; // Mantener la actual si no se sube nueva

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $archivo = $_FILES['imagen'];
        $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);

        $carpeta = 'img_actividades/';
        if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

        $nueva_imagen = uniqid('img_') . '.' . $ext;
        $destino = $carpeta . $nueva_imagen;

        if (move_uploaded_file($archivo['tmp_name'], $destino)) {
            // Borrar imagen anterior
            if (!empty($imagen_nombre) && file_exists($carpeta . $imagen_nombre)) {
                unlink($carpeta . $imagen_nombre);
            }
            $imagen_nombre = $nueva_imagen;
        } else {
            $_SESSION['mensaje'] = [
                "tipo" => "error",
                "texto" => "Error al subir la imagen ❌"
            ];
            header("Location: actividades_editar.php?id=$id_actividad");
            exit();
        }
    }

    // ================= ACTUALIZAR ACTIVIDAD =================
    $sql = "UPDATE actividad SET 
                nombre=?, hora_inicio=?, hora_fin=?, fecha=?, lugar=?, ponente=?, cupo=?, descripcion=?, imagen=?, id_departamento=?, id_semestre=?
            WHERE id_actividad=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssissiii",
        $nombre, $hora_inicio, $hora_fin, $fecha, $lugar, $ponente, $cupo, $descripcion, $imagen_nombre, $id_departamento, $id_semestre, $id_actividad
    );

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = [
            "tipo" => "success",
            "texto" => "Actividad actualizada correctamente ☑️"
        ];
    } else {
        $_SESSION['mensaje'] = [
            "tipo" => "error",
            "texto" => "Error al actualizar actividad ❌ " . $conn->error
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
    <title>Editar Actividades - GEHAC </title>
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
                <h3>Editar Actividad - GEHAC</h3>
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
                            <input type="hidden" name="id_actividad" value="<?= $actividades['id_actividad'] ?>">

                            <div class="form-group">
                                <label>Nombre:</label>
                                <input type="text" name="nombre" required value="<?= htmlspecialchars($actividades['nombre']) ?>">
                            </div>    

                            <div class="form-group">
                                <label>Hora de Inicio:</label>
                                <input type="time" name="hora_inicio" required value="<?= htmlspecialchars($actividades['hora_inicio']) ?>">
                            </div>

                            <div class="form-group">
                                <label>Hora de Fin:</label>
                                <input type="time" name="hora_fin" required value="<?= htmlspecialchars($actividades['hora_fin']) ?>">
                            </div>

                            <div class="form-group">
                                <label>Fecha:</label>
                                <input type="date" name="fecha" required value="<?= htmlspecialchars($actividades['fecha']) ?>">
                            </div>

                            <div class="form-group">
                                <label>Lugar:</label>
                                <input type="text" name="lugar" required value="<?= htmlspecialchars($actividades['lugar']) ?>">
                            </div>

                            <div class="form-group">
                                <label>Ponente:</label>
                                <input type="text" name="ponente" required value="<?= htmlspecialchars($actividades['ponente']) ?>">
                            </div>

                            <div class="form-group">
                                <label>Cupo:</label>
                                <input type="numbre" name="cupo" required value="<?= htmlspecialchars($actividades['cupo']) ?>">
                            </div>

                            <div class="form-group full">
                                <label>Descripción:</label>
                                <input type="text" name="descripcion" required value="<?= htmlspecialchars($actividades['descripcion']) ?>">
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
                                        <option value="<?= $cat['id_departamento'] ?>"
                                            <?= $actividades['id_departamento'] == $cat['id_departamento'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>



                            <div class="form-group">
                                <label>Semestre:</label>
                                <select name="id_semestre" required>
                                    <option value="">-- Seleccione Semestre --</option>
                                    <?php foreach($semestres as $cat): ?>
                                        <option value="<?= $cat['id_semestre'] ?>"
                                            <?= $actividades['id_semestre'] == $cat['id_semestre'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Imagen actual:</label><br>

                                <?php if ($actividades['imagen']) { ?>
                                    <img src="img_actividades/<?php echo $actividades['imagen']; ?>" 
                                        alt="Imagen actual" 
                                        style="max-width:150px; display:block; margin-bottom:10px;">
                                <?php } else { ?>
                                    <p>Sin imagen</p>
                                <?php } ?>

                                <label>Cambiar imagen:</label>
                                <input type="file" name="imagen" accept="image/*" onchange="previewImage(event)">
                                <img id="preview" style="display:none; max-width:150px;">

                                <!--<img id="preview" src="#" alt="Preview" style="max-width:150px; display:none; margin-top:10px;">-->
                            </div>

                          
                            <br><br>
                            

                            <div class="form-buttons">
                                <button type="submit" name="guardar">
                                    <i class="fas fa-save"></i> Actualizar 
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