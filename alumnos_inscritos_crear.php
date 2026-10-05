<?php
include("auth.php");
include("conexion.php");


// ================= MENSAJE =================

$mensaje = null;

if(isset($_SESSION['mensaje'])){
    $mensaje=$_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}



// ================= TRAER SEMESTRES =================

$semestre=[];

$result_semestres=$conn->query("
    SELECT id_semestre,nombre
    FROM semestre
    ORDER BY nombre DESC
");

if($result_semestres){

    while($row=$result_semestres->fetch_assoc()){

        $semestre[]=$row;

    }

}



//================= CONSULTA =================

$sql="SELECT
            a.id_usuario,
            a.nombre,
            a.nombre_s,
            a.apellido_p,
            a.apellido_m,
            a.matricula,
            a.estado,
            c.nombre AS carrera,
            d.nombre AS modalidad
        FROM usuario a
        LEFT JOIN carrera c
            ON a.id_carrera=c.id_carrera
        LEFT JOIN modalidad d
            ON a.id_modalidad=d.id_modalidad
        WHERE a.id_rol=3
        AND a.estado=1
        ORDER BY a.id_usuario DESC";


$result=$conn->query($sql);



// ================= GUARDAR DATOS EN ARRAY =================

$usuarios=[];

if($result && $result->num_rows>0){

    while($fila=$result->fetch_assoc()){

        $usuarios[]=$fila;

    }

}



/*_______________________ PROCESAR FORM _____________________*/

if(isset($_POST['guardar'])){


    // obtener semestre seleccionado
    $id_semestre=(int)$_POST['id_semestre'];


    // validar semestre
    if(!$id_semestre){

        $_SESSION['mensaje']=[
            "tipo"=>"error",
            "texto"=>"Seleccione un semestre ❌"
        ];

        header("Location: alumnos_inscritos_crear.php");
        exit();
    }



    try{

        $conn->begin_transaction();


        // INSERT
        $stmt_insert=$conn->prepare("
            INSERT INTO alumno_inscrito
            (
                id_usuario,
                id_semestre
            )
            VALUES
            (
                ?,
                ?
            )
        ");


        if(!$stmt_insert){

            throw new Exception(
                "Error INSERT: ".$conn->error
            );
        }



        // CONSULTAR SI EXISTE
        $stmt_check=$conn->prepare("
            SELECT id_inscrito
            FROM alumno_inscrito
            WHERE id_usuario=?
            AND id_semestre=?
        ");


        if(!$stmt_check){

            throw new Exception(
                "Error CHECK: ".$conn->error
            );
        }



        $totalInsertados=0;

        $alumnosExistentes=[];



        foreach($usuarios as $alumno){

            $id_usuario=(int)$alumno['id_usuario'];


            // revisar existencia
            $stmt_check->bind_param(
                "ii",
                $id_usuario,
                $id_semestre
            );

            $stmt_check->execute();

            $resultado=$stmt_check->get_result();


            // Si ya existe
            if($resultado->num_rows>0){


                $nombreCompleto=trim(

                    $alumno['nombre']." ".
                    (!empty($alumno['nombre_s']) ? $alumno['nombre_s'] : "")." ".
                    (!empty($alumno['apellido_p']) ? $alumno['apellido_p'] : "")." ".
                    (!empty($alumno['apellido_m']) ? $alumno['apellido_m'] : "")

                );


                $alumnosExistentes[]=$nombreCompleto;

            }
            else{


                // insertar alumno
                $stmt_insert->bind_param(
                    "ii",
                    $id_usuario,
                    $id_semestre
                );


                if(!$stmt_insert->execute()){

                    throw new Exception(
                        $stmt_insert->error
                    );
                }


                $totalInsertados++;

            }

        }



        $stmt_insert->close();
        $stmt_check->close();


        $conn->commit();




        // mostrar repetidos
        if(!empty($alumnosExistentes)){

            $_SESSION['mensaje']=[

                "tipo"=>"success",

                "texto"=>"Alumno's faltantes inscritos. ☑️\n\n"
                /*.implode("\n",$alumnosExistentes)*/

            ];

            header("Location: alumnos_inscritos.php");
            exit();

        }



        // éxito
        $_SESSION['mensaje']=[

            "tipo"=>"success",

            "texto"=>"Se inscribieron correctamente "
            .$totalInsertados.
            " alumno's ☑️"

        ];


        header("Location: alumnos_inscritos.php");
        exit();



    }catch(Exception $e){


        $conn->rollback();


        $_SESSION['mensaje']=[

            "tipo"=>"error",

            "texto"=>"Error: ".$e->getMessage()

        ];


        header("Location: alumnos_inscritos_crear.php");
        exit();

    }


}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inscribir Alumnos al Semestre - GEHAC </title>
    <link rel="stylesheet" href="css/alumnos_inscritos_crear.css">
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

    /*__________ TABLA DENTRO DEL FORM___________*/ 

    .card .estado-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
    font-size: 14px;
    }

    .card .estado-table thead {
        background: #4031a1;
        color: #fff;
    }

    .card .estado-table th,
    .card .estado-table td {
        padding: 12px 15px;
        text-align: center;
        border-bottom: 1px solid #e0e0e0;
    }

    .card .estado-table tbody tr:hover {
        background: #f1f3f6;
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
                <a href="alumnos_inscritos.php" class="active">
                    <i class="fas fa-user-check" style="color:#4031a1"></i>
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
                <h3>Inscribir Alumnos al Semestre - GEHAC</h3>
            </div>

        </section>
        

   

        <!-- ÁREA DE TRABAJO -->
        <section class="content-wrapper">


            <div class="card">

                

                <div class="estado-section">

                        
                        <p>
                            🟢 Seleccione el Semestre.
                        </p>

                            <table id="tablaAlumnos" class="estado-table">

                                <thead>
                                    <tr>
                                        <th>Nombre</th> 
                                    
                                        <th>Apellido Paterno</th>
                                        <th>Apellido Materno</th>
                                        <th>Carrera</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    

                                    <?php if(!empty($usuarios)): ?>

                                        <?php foreach($usuarios as $row): ?>

                                            <tr>

                                                <!-- NOMBRE -->
                                                <td>
                                                    <?= htmlspecialchars($row['nombre']) ?>
                                                    <?= !empty($row['nombre_s']) ? htmlspecialchars($row['nombre_s']) : '' ?>
                                                    
                                                    
                                                </td>

                                                

                                            

                                                <td><?= !empty($row['apellido_p']) ? htmlspecialchars($row['apellido_p']) : '' ?>
                                                </td>

                                                <td>
                                                    <?= !empty($row['apellido_m']) ? htmlspecialchars($row['apellido_m']) : '' ?>
                                                </td>

                                                <td>
                                                    <?= !empty($row['carrera']) ? htmlspecialchars($row['carrera']) : '' ?>
                                                </td>

                                            
                                                

                                                <!-- ACCIONES 
                                                <td>

                                                    <?php $id = (int)$row['id_usuario']; ?>

                                                    <a href="toggle_estado_usuarios.php?id=<?= $id ?>&estado=<?= $row['estado'] ?>" class="btn-edit">
                                                        🔄 
                                                    </a>

                                                    

                                                </td>-->

                                            </tr>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <tr>

                                            <td colspan="4" style="text-align:center">

                                                No hay Alumnos "Activos"

                                            </td>

                                        </tr>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                        <form action="" method="POST" enctype="multipart/form-data" class="form-producto">

                            

                            <div class="form-group">
                                <label>Semestre:</label>
                                <select name="id_semestre" required>
                                    <option value="">-- Seleccione el Semestre --</option>
                                    <?php foreach($semestre as $cat): ?>
                                        <option value="<?php echo $cat['id_semestre']; ?>">
                                            <?php echo htmlspecialchars($cat['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                          

                            

                            <div class="form-buttons">
                                <button type="submit" name="guardar">
                                    <i class="fas fa-save"></i> Guardar 
                                </button>
                                <a href="alumnos_inscritos.php">
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