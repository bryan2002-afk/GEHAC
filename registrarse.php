<?php

session_start();

/*=========================================
= CONEXIÓN A LA BASE DE DATOS
=========================================*/
include('conexion.php');

/*=========================================
= VARIABLE PARA MENSAJES
=========================================*/
$mensaje = "";

/*=========================================
= PROCESAR FORMULARIO
=========================================*/
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['guardar'])) {

    // 🔐 Obtener y sanitizar datos
    $nombre   = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
    $correo   = htmlspecialchars(trim($_POST['correo']), ENT_QUOTES, 'UTF-8');
    $password = trim($_POST['password']);

    // ❌ Validar campos obligatorios
    if (empty($nombre) || empty($correo) || empty($password)) {
        $mensaje = "Por favor complete todos los campos (Nombre, Correo, Contraseña).";
    } else {

        // ✅ Verificar si el correo ya existe
        $sql_check = "SELECT id_usuario FROM usuario WHERE correo=?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $correo);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check && $result_check->num_rows > 0) {
            $mensaje = "El correo ya está registrado, use otro.";
            $stmt_check->close();
        } else {
            $stmt_check->close();

            // 🔐 Hash de la contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // ✅ Insertar usuario
            $sql_insert = "INSERT INTO usuario (nombre, correo, password, estado) VALUES (?, ?, ?, 1)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sss", $nombre, $correo, $password_hash);

            

            if ($stmt_insert->execute()) {
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

            $stmt_insert->close();
            header("Location: login.php");
            exit();
        }
    }
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - GEHAC</title>

    <!-- Favicon -->
    <link rel="icon" href="img/gehac.png" type="image/x-icon">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    /* ================= RESET ================= */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, Helvetica, sans-serif;
    }

    body {
        background-color: white;
        color: #333;
    }

    /* ================= HEADER ================= */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 60px;
        background-color: white;

        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .logo img {
        width: 80px;
    }

    .info-contacto {
        font-size: 14px;
    }

    .telefono {
        color: #0689b5;
    }

    .servicio {
        color: red;
    }

    .redes a {
        font-size: 20px;
        margin-left: 15px;
        color: #0689b5;
        transition: 0.3s;
    }

    .redes a:hover {
        color: #ff006d;
    }

    /* ================= HERO ================= 
    .hero {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 140px 20px 60px;
        min-height: 100vh;

        background: linear-gradient(to right, #0689b5, #00b4d8);
        background-image: url('fondo4.jpg');
        background-size: cover;         🔥 Hace que la imagen cubra todo 
        background-position: center;    🔥 Centra la imagen 
        background-repeat: no-repeat;   🔥 Evita que se repita 
    }*/

    /* ============= nuevo .hero ========*/

    .hero {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 140px 20px 60px;
        min-height: 100vh;

        overflow: hidden;
    }

    .hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: url('img/fondo4.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;

        filter: blur(6px);          /* 🔥 Nivel de desenfoque */
        transform: scale(1.1);      /* 🔥 Evita bordes recortados */
        z-index: 0;
    }

    /* Asegura que el contenido esté encima */
    .login-container {
        position: relative;
        z-index: 1;
    }

    /* ================= LOGIN ================= */
    .login-container {
        display: flex;
        width: 100%;
        max-width: 850px;
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);

        animation: fadeIn 0.8s ease-in-out;
    }

    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(20px);}
        to {opacity: 1; transform: translateY(0);}
    }

    /* FORM */
    .login-form {
        flex: 1;
        padding: 40px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .login-form h2 {
        margin-bottom: 15px;
    }

    .profile-img {
        width: 80px;
        border-radius: 50%;
        margin-bottom: 20px;
    }

    .input-group {
        width: 100%;
        position: relative;
        margin-bottom: 15px;
    }

    .input-group i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #00b4ff;
    }

    .input-group input {
        width: 100%;
        padding: 12px 15px 12px 45px;
        border: 1px solid #ddd;
        border-radius: 25px;
        background: #f9f9f9;
    }

    .input-group input:focus {
        border-color: #00b4ff;
        outline: none;
    }

    /* OPTIONS */
    .options {
        width: 100%;
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        margin-bottom: 20px;
    }


    /* OPTIONS 
    .options {
        width: 100%;
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        margin-bottom: 20px;
    }*/

    .options a{
            /*color:#fff;*/
            text-decoration:none;
            font-weight:bold;
            }

    .options2 {
        width: 100%;
        display: flex;
        justify-content: center;
        font-size: 0.8rem;
        margin-bottom: 20px;
    }

    .options2 a{
            /*color:#fff;*/
            color: #00d2ff;
            text-decoration:none;
            font-weight:bold;
            }


            
    


    /* BUTTON */
    .btn-signin {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 25px;
        background: linear-gradient(to right, #00d2ff, #3a7bd5);
        color: white;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn-signin:hover {
        transform: translateY(-2px);
    }

    .btn-signin:active {
        transform: scale(0.98);
    }

    .create-account {
        margin-top: 15px;
        font-size: 0.8rem;
    }

    /* VIDEO */
    .login-visual {
        flex: 1;
        position: relative;
    }

    .login-visual video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .login-visual::after {
        content: "";
        position: absolute;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.3);
    }

    /* ================= RESPONSIVE ================= */
    @media (max-width: 768px) {

        .header {
            flex-direction: column;
            text-align: center;
            gap: 10px;
        }

        .hero {
            padding-top: 180px;
        }

        .login-container {
            flex-direction: column;
        }

        .login-visual {
            height: 200px;
        }
    }

    /* FORMULARIO ESTILIZADO */
.form-producto {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 40px;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    max-width: 400px;
    margin: auto;
    z-index: 2;
    position: relative;
    animation: fadeIn 0.8s ease-in-out;
}

.form-producto h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #3a7bd5;
}

.form-producto .form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 20px;
}

.form-producto label {
    margin-bottom: 5px;
    font-weight: bold;
    color: #0689b5;
}

.form-producto input {
    padding: 12px 15px;
    border-radius: 25px;
    border: 1px solid #ccc;
    outline: none;
    transition: 0.3s;
    font-size: 14px;
}

.form-producto input:focus {
    border-color: #00b4ff;
    box-shadow: 0 0 5px rgba(0,180,255,0.3);
}

.form-producto .form-buttons {
    display: flex;
    justify-content: center;
    margin-top: 10px;
}

.form-producto .form-buttons button {
    padding: 12px 25px;
    border-radius: 25px;
    border: none;
    background: linear-gradient(to right, #00d2ff, #3a7bd5);
    color: white;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.form-producto .form-buttons button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.form-producto .form-buttons button:active {
    transform: scale(0.98);
}

/* MENSAJES DE ERROR/SUCCESS */
.mensaje {
    margin-bottom: 20px;
    padding: 12px;
    border-radius: 15px;
    font-weight: bold;
    text-align: center;
}

.mensaje.error {
    background-color: #ffdddd;
    color: #d8000c;
    border: 1px solid #d8000c;
}

.mensaje.success {
    background-color: #ddffdd;
    color: #4f8a10;
    border: 1px solid #4f8a10;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .form-producto {
        margin: 20px;
        padding: 25px;
    }
}
</style>

    
</head>

<body>

<!-- HEADER -->
<header class="header">
    <div class="logo">
        <img src="img/gehac.png" alt="Logo de  GEHAC">
    </div>

    <div class="info-contacto">
        <h3>GEHAC</h3>
        <P>Sistema Gestor de Horas Academicas y Culturales.</P>
        <!--<div class="telefono"><strong>GEHAC</strong></div>
        <div class="servicio">Servicio a domicilio sin costo</div>-->
    </div>

    <div class="redes">
        <a href="https://es-la.facebook.com/farmaciasomegaoficial/"><i class="fab fa-facebook"></i></a>
        <a href="https://www.instagram.com/farmaciasomegaoficial/"><i class="fab fa-instagram"></i></a>
        <a href="https://api.whatsapp.com/send?phone=+5219511026829&text=%C2%A
        1Hola!%20Quisiera%20chatear%20con%20alguien%20de%20Farmacias%20Omega."><i class="fab fa-whatsapp"></i></a>
    </div>
</header>

<!-- HERO -->
<section class="hero">

<div class="login-container">

    <!-- FORM -->
                        <form action="" method="POST" enctype="multipart/form-data" class="form-producto">

                            <h2>Registrarse</h2>
                            
                            <?php if($mensaje != ""): ?>
                                <div class="mensaje error"><?php echo $mensaje; ?></div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label>Nombre:</label>
                                <input type="text" name="nombre" required>
                            </div>    
                            

                            <div class="form-group">
                                <label>Correo:</label>
                                <input type="text" name="correo" required>
                            </div>

                            <div class="form-group">
                                <label>Contraseña:</label>
                                <input type="password" name="password" required>
                            </div>



                            

                            <div class="form-buttons">
                                <button type="submit" name="guardar">
                                    <i class="fas fa-save"></i> Guardar 
                                </button>
                                
                            </div>

                            <br>

                            <div class="options2">
                                <a href="login.php">
                                    <i class="fa-solid fa-arrow-left"> </i> Volver</a>
                            </div>
                        </form>

    <!-- VIDEO -->
    <div class="login-visual">
        <video autoplay muted loop playsinline>
            <source src="img/seg_4.mp4" type="video/mp4">
        </video>
    </div>

</div>

</section>

</body>
</html>

<?php
//$conn->close();
?>