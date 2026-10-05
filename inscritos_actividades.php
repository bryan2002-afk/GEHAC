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


// ================= CONSULTA =================

// ================= CONSULTA =================

$sql = "

SELECT

    act.id_actividad,
    act.nombre AS actividad,

    d.id_disponible,
    d.asistencia,

    CONCAT(
        IFNULL(u.nombre,''),
        ' ',
        IFNULL(u.nombre_s,''),
        ' ',
        IFNULL(u.apellido_p,''),
        ' ',
        IFNULL(u.apellido_m,'')
    ) AS usuario

FROM actividad act

LEFT JOIN disponible d
ON act.id_actividad = d.id_actividad

LEFT JOIN usuario u
ON d.id_usuario = u.id_usuario

ORDER BY act.id_actividad DESC,
         usuario ASC

";

$result = $conn->query($sql);


// ================= AGRUPAR POR ACTIVIDAD =================

$actividades=[];

if($result && $result->num_rows>0){

    while($fila=$result->fetch_assoc()){

        $actividad=$fila["actividad"];

        if(!isset($actividades[$actividad])){

            $actividades[$actividad]=[];
        }

        // Solo agregar alumno si existe
        if(!empty(trim($fila["usuario"]))){

            $actividades[$actividad][]=[

                "id_disponible"=>$fila["id_disponible"],
                "usuario"=>$fila["usuario"],
                "asistencia"=>$fila["asistencia"]

            ];
        }

    }

}
?>




<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alumnos Inscritos a Actividades - GEHAC </title>
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

    /* =====================================
    FILTRO ACTIVIDADES
    ===================================== */

    .filtro-actividad{
        display:flex;
        align-items:center;
        gap:10px;
        margin-bottom:25px;
        padding:15px;
        background:#f9f9f9;
        border-radius:10px;
    }

    .filtro-actividad label{
        font-weight:600;
        color:#4031a1;
    }

    .filtro-actividad select{
        padding:8px 14px;
        border:1px solid #ddd;
        border-radius:8px;
        outline:none;
        font-size:14px;
        min-width:250px;
        cursor:pointer;
    }

    .filtro-actividad select:focus{
        border-color:#0074b7;
    }

    /* =====================================
    HEADER DE ACTIVIDAD
    ===================================== */

    .actividad-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:20px;
    }

    /* =====================================
    BOTÓN PDF
    ===================================== */

    .btn-pdf{
        display:inline-flex;
        align-items:center;
        gap:8px;

        padding:8px 14px;

        background:#d32f2f;
        color:white;

        border-radius:8px;

        text-decoration:none;
        font-weight:600;

        transition:.3s;
    }

    .btn-pdf:hover{

        background:#b71c1c;

        transform:translateY(-2px);

        box-shadow:0 4px 12px rgba(211,47,47,.3);

    }

    .btn-pdf i{

        font-size:14px;

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
                <a href="alumnos_inscritos.php" >
                    <i class="fas fa-user-check" ></i>
                    <span>Alumnos Inscritos</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (tiene_permiso('inscritos_actividades.php', 'VER')): ?>
            <li>
                <a href="inscritos_actividades.php" class="active">
                    <i class="fas fa-clipboard-check" style="color:#4031a1"></i>
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
                <h3>Alumnos Inscritos a Actividades - GEHAC</h3>
            </div>

        </section>
        

   

        <!-- ÁREA DE TRABAJO -->
        <section class="content-wrapper">


            <div class="card">

                <div class="products-header">

                    

                </div>

                <!--
                <div class="estado-section">

                        <h2 class="estado-title title-proceso">
                            🟢 Alumnos inscritos
                        </h2>

                        <table id="tablaAlumnos" class="estado-table">

                            <thead>
                                <tr>
                                    <th>Alumno</th>   
                                    <th>Actividad</th>
                                    <th>Asistencia</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if(!empty($usuarios)): ?>

                                    <?php foreach($usuarios as $row): ?>

                                        <tr>

                                            <td>
                                                <?= htmlspecialchars($row['usuario']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['actividad']) ?>
                                            </td>

                                            <td>

                                                <?php if($row['asistencia']==1): ?>

                                                    <span style="color: green; font-weight: bold;">
                                                        Asistió
                                                    </span>

                                                <?php else: ?>

                                                    <span style="color: #4c3609de; font-weight: bold;">
                                                        Pendiente
                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                            <td>

                                                <?php $id=(int)$row['id_disponible']; ?>

                                                <a href="toggle_asistencia_usuarios.php?id=<?= $id ?>&asistencia=<?= $row['asistencia'] ?>" class="btn-edit">
                                                    🔄 
                                                </a>

                                                <a href="inscritos_actividades_borrar.php?id=<?= $id ?>"
                                                class="btn-delete"
                                                onclick="return confirm('¿Eliminar inscripción de <?= htmlspecialchars($row['usuario'], ENT_QUOTES) ?>?')">

                                                    <i class="fas fa-trash"></i>
                                                    <span>Borrar</span>

                                                </a>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>

                                        <td colspan="4" style="text-align:center">

                                            No existen alumnos inscritos en actividades

                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                </div>
                -->

                <div class="filtro-actividad">

                    <label for="actividadFiltro">
                        <i class="fas fa-filter"></i> Filtrar actividad:
                    </label>

                    <select id="actividadFiltro">

                        <option value="todas">
                            Todas las actividades
                        </option>

                        <?php foreach($actividades as $nombreActividad => $alumnos): ?>

                            <option value="<?= md5($nombreActividad) ?>">
                                <?= htmlspecialchars($nombreActividad) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                        <!-- tablas-->

                <?php if(!empty($actividades)): ?>

                    <?php foreach($actividades as $nombreActividad => $alumnos): ?>

                        <div class="estado-section actividad-tabla" 
                            data-actividad="<?= md5($nombreActividad) ?>"
                            style="margin-bottom:30px;">

                            <div class="actividad-header">

                                <h2 class="estado-title title-proceso">
                                    📚 <?= htmlspecialchars($nombreActividad) ?>
                                </h2>

                                <?php if (tiene_permiso('inscritos_actividades.php', 'CREAR')): ?>
                                    <a href="lista_pdf.php?actividad=<?= urlencode($nombreActividad) ?>" 
                                    class="btn-pdf">

                                        <i class="fas fa-file-pdf"></i>
                                        <span>Lista PDF</span>

                                    </a>
                                <?php endif; ?>

                            </div>

                            <table class="estado-table">

                                <thead>
                                    <tr>
                                        <th>Alumno</th>
                                        <th>Asistencia</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php if(!empty($alumnos)): ?>

                                        <?php foreach($alumnos as $row): ?>

                                        <tr>

                                            <td>
                                                <?= htmlspecialchars($row['usuario']) ?>
                                            </td>

                                            <td>

                                                <?php if($row['asistencia']==1): ?>

                                                    <span style="color:green;font-weight:bold;">
                                                        Asistió
                                                    </span>

                                                <?php else: ?>

                                                    <span style="color:#4c3609de;font-weight:bold;">
                                                        Pendiente
                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                            <td>

                                                <?php $id=(int)$row['id_disponible']; ?>

                                                <?php if (tiene_permiso('inscritos_actividades.php', 'EDITAR')): ?>
                                                    <a href="toggle_asistencia_usuarios.php?id=<?= $id ?>&asistencia=<?= $row['asistencia'] ?>"
                                                    class="btn-edit">
                                                        🔄
                                                    </a>
                                                <?php endif; ?>

                                                <?php if (tiene_permiso('inscritos_actividades.php', 'BORRAR')): ?>
                                                    <a href="inscritos_actividades_borrar.php?id=<?= $id ?>"
                                                    class="btn-delete"
                                                    onclick="return confirm('¿Eliminar inscripción de <?= htmlspecialchars($row['usuario'], ENT_QUOTES) ?>?')">

                                                        <i class="fas fa-trash"></i>
                                                        <span>Borrar</span>

                                                    </a>
                                                <?php endif; ?>

                                                <?php if (!tiene_permiso('inscritos_actividades.php', 'EDITAR') && !tiene_permiso('inscritos_actividades.php', 'BORRAR')): ?>         
                                                    <span class="text-muted">Lectura</span>                                            
                                                <?php endif; ?>

                                            </td>

                                        </tr>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                    <tr>

                                        <td colspan="3" style="text-align:center;color:#777;">
                                            No existen alumnos inscritos en esta actividad
                                        </td>

                                    </tr>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                            <div style="
                                margin-top:10px;
                                padding:12px;
                                background:#f5f7ff;
                                border-left:4px solid #4031a1;
                                border-radius:8px;
                                font-weight:600;
                                color:#4031a1;
                            ">
                                👥 Total de Alumnos Registrados: 
                                <?= count($alumnos) ?>
                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                <div class="estado-section">

                    <h2 style="text-align:center;">
                        No existen alumnos inscritos en actividades
                    </h2>

                </div>

                <?php endif; ?>

                
            </div>

        </section>
        


        
        <footer class="footer">

            <p class="copy">Todos los derechos reservados por la Universidad Mundo Maya © 2026</p>

        </footer>


    </main>
    

    <script>

        document.addEventListener("DOMContentLoaded",()=>{

            const filtro = document.getElementById("actividadFiltro");
            const tablas = document.querySelectorAll(".actividad-tabla");

            filtro.addEventListener("change",()=>{

                const valor = filtro.value;

                tablas.forEach(tabla=>{

                    if(valor==="todas"){

                        tabla.style.display="block";

                    }else{

                        if(tabla.dataset.actividad===valor){

                            tabla.style.display="block";

                        }else{

                            tabla.style.display="none";

                        }

                    }

                });

            });

        });

    </script>

</body>
</html>

<?php
$conn->close();
?>