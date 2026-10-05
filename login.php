<?php

/*=========================================
= INICIAR SESIÓN DEL SISTEMA
=========================================*/
session_start();


// ================= MENSAJE =================
$mensaje = [
    "tipo" => "",
    "texto" => ""
];

if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

/*=========================================
= VERIFICAR SI EL USUARIO YA INICIÓ SESIÓN
= Si existe una sesión activa lo envía al dashboard
=========================================*/
if(isset($_SESSION['id_usuario'])){
    header("Location: dashboard.php");
    exit();
}


/*=========================================
= CONEXIÓN A LA BASE DE DATOS
=========================================*/
include('conexion.php');




/*=========================================
= VARIABLE PARA MENSAJES DE ERROR
=========================================*/
$error_message="";


/*=========================================
= VALIDAR SI EL FORMULARIO FUE ENVIADO
=========================================*/
if($_SERVER['REQUEST_METHOD']=="POST"){

    /*=========================================
    = OBTENER Y LIMPIAR DATOS DEL FORMULARIO
    =========================================*/
    $correo=trim($_POST['correo']);
    $password=trim($_POST['password']);


    /*=========================================
    = VALIDAR CAMPOS VACÍOS
    =========================================*/
    if(empty($correo) || empty($password)){

        $error_message="Por favor complete todos los campos.";

    }else{

        /*=========================================
        = CONSULTA PARA BUSCAR EL USUARIO
        = Se usa consulta preparada para evitar
        = ataques SQL Injection
        =========================================*/
        $query="SELECT * FROM usuario WHERE correo=?";


        if($stmt=$conn->prepare($query)){

            /*=========================================
            = ASIGNAR PARÁMETROS Y EJECUTAR CONSULTA
            =========================================*/
            $stmt->bind_param("s",$correo);

            $stmt->execute();

            $result=$stmt->get_result();


            /*=========================================
            = VERIFICAR SI EXISTE EL USUARIO
            =========================================*/
            if($result->num_rows>0){

                /*=========================================
                = OBTENER DATOS DEL USUARIO
                =========================================*/
                $user=$result->fetch_assoc();


                /*=========================================
                = VARIABLE DE CONTROL
                =========================================*/
                $loginCorrecto=false;


                /*=========================================
                = VALIDACIÓN DE CONTRASEÑA EN HASH
                = password_verify compara:
                = contraseña ingresada VS hash guardado
                =========================================*/
                if(password_verify(
                    $password,
                    $user['password']
                )){

                    $loginCorrecto=true;

                }


                /*=========================================
                = COMPATIBILIDAD CON CONTRASEÑAS ANTIGUAS
                = Si existen contraseñas en texto plano
                =========================================*/
                elseif($password === $user['password']){

                    $loginCorrecto=true;


                    /*=========================================
                    = CONVERTIR AUTOMÁTICAMENTE
                    = TEXTO PLANO → HASH
                    =========================================*/
                    $nuevoHash=password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                    /*=========================================
                    = ACTUALIZAR LA CONTRASEÑA
                    = EN LA BASE DE DATOS
                    =========================================*/
                    $update="
                    UPDATE usuario
                    SET password=?
                    WHERE id_usuario=?
                    ";

                    $stmtUpdate=
                    $conn->prepare($update);

                    $stmtUpdate->bind_param(
                        "si",
                        $nuevoHash,
                        $user['id_usuario']
                    );

                    $stmtUpdate->execute();

                    $stmtUpdate->close();
                }


                /*=========================================
                = SI EL LOGIN ES CORRECTO
                =========================================*/
                if($loginCorrecto){

                    /*=========================================
                    = CREAR VARIABLES DE SESIÓN
                    =========================================*/
                    $_SESSION['id_usuario']
                    =$user['id_usuario'];

                    $_SESSION['nombre']
                    =$user['nombre'];

                    $_SESSION['correo']
                    =$user['correo'];

                    $_SESSION['id_rol']
                    =$user['id_rol'];

                    $_SESSION['id_carrera']
                    =$user['id_carrera'];

                    $_SESSION['id_modalidad']
                    =$user['id_modalidad'];


                    /*=========================================
                    = REDIRECCIONAR AL DASHBOARD
                    =========================================*/
                    header(
                        "Location: dashboard.php"
                    );

                    exit();

                }else{

                    /*=========================================
                    = ERROR DE AUTENTICACIÓN
                    =========================================*/
                    $error_message=
                    "Correo o contraseña incorrectos.";
                }

            }else{

                /*=========================================
                = USUARIO NO ENCONTRADO
                =========================================*/
                $error_message=
                "Correo o contraseña incorrectos.";
            }


            /*=========================================
            = CERRAR CONSULTA
            =========================================*/
            $stmt->close();

        }

    }

}


/*=========================================
= CERRAR CONEXIÓN A BASE DE DATOS
=========================================*/
$conn->close();

?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - GEHAC</title>

    <!-- Favicon -->
    <link rel="icon" href="img/gehac.png" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
   

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

    .titulo-gehac{
        font-size: 32px;
        font-weight: 900;
        letter-spacing: 3px;
        background: linear-gradient(to right, #00b4ff, #3a7bd5);
        
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;

        text-transform: uppercase;
    }

    .subtitulo-gehac{
        font-size: 14px;
        color: #555;
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    /*.telefono {
        color: #0689b5;
    }

    .servicio {
        color: red;
    }*/

    /* background: linear-gradient(45deg, #F77737, #FCAF45, #F7A800, #DC2E92, #8134AF, #515BD4);
*/

  /* Variables CSS para los colores */
  :root {
    --twitter-color: #1da1f2;
    --facebook-color: #1877F2;
    --whatsapp-color: #25D366;
    --instagram-color: #E4405F;

  }

  /* Estilos comunes para los íconos */
  .social-icon, .social-icon-1, .social-icon-2, .social-icon-3 {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background-color: white;
    border-radius: 50%;
    margin: 20px;
    font-size: 24px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    cursor: pointer;
    transition: transform 0.3s ease;
  }

  .tooltip, .tooltip-1, .tooltip-2, .tooltip-3 {
    position: absolute;
    top: -36px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 14px;
    opacity: 0;
    pointer-events: none;
    transform: translateY(10px);
    transition: all 0.3s ease;
    white-space: nowrap;
    z-index: 2;
    color: white;
  }

  .tooltip::after, .tooltip-1::after, .tooltip-2::after, .tooltip-3::after {
    content: "";
    position: absolute;
    bottom: -6px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
  }

  /* Estilos específicos para cada red social */
  .social-icon {
    color: var(--twitter-color);
  }

  .social-icon .tooltip {
    background-color: var(--twitter-color);
  }

  .social-icon .tooltip::after {
    border-top: 6px solid var(--twitter-color);
  }

  .social-icon-1 {
    color: var(--facebook-color);
  }

  .social-icon-1 .tooltip-1 {
    background-color: var(--facebook-color);
  }

  .social-icon-1 .tooltip-1::after {
    border-top: 6px solid var(--facebook-color);
  }

  .social-icon-2 {
    color: var(--whatsapp-color);
  }

  .social-icon-2 .tooltip-2 {
    background-color: var(--whatsapp-color);
  }

  .social-icon-2 .tooltip-2::after {
    border-top: 6px solid var(--whatsapp-color);
  }

  .social-icon-3 {
    color: var(--instagram-color);
  }

  .social-icon-3 .tooltip-3 {
    background-color: var(--instagram-color);
  }

  .social-icon-3 .tooltip-3::after {
    border-top: 6px solid var(--instagram-color);
  }

  /* Mostrar tooltip al pasar el mouse */
  .social-icon:hover .tooltip,
  .social-icon-1:hover .tooltip-1,
  .social-icon-2:hover .tooltip-2,
  .social-icon-3:hover .tooltip-3 {
    opacity: 1;
    transform: translateY(0);
  }

  a{
    text-decoration: none;
    color: inherit;
  }

    /*.redes a {
        font-size: 20px;
        margin-left: 15px;
        color: #0689b5;
        transition: 0.3s;
    }

    .redes a:hover {
        color: #ff006d;
    }*/

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

    /*.alert {
    margin-bottom: 15px;
    padding: 12px;
    border-radius: 8px;
    background-color: #f2f2f2;
    color: #333;
    text-align: center;
    font-weight: bold;
    }*/
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

    
</head>

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

<!-- HEADER -->
<header class="header">
    <div class="logo">
        <img src="img/gehac.png" alt="Logo de  GEHAC">
    </div>

    <div class="info-contacto">
        <h3 class="titulo-gehac">GEHAC</h3>
        <p class="subtitulo-gehac">
            Sistema Gestor de Horas Académicas y Culturales.
        </p>
    </div>

    <div class="redes">

        <a href="https://www.facebook.com/UniversidadMundoMayaOaxaca/" target="_blank">
            <div class="social-icon-1">
            <span class="tooltip-1">Facebook</span>
            <i class="fab fa-facebook"></i>
            </div>
        </a>

        <a href="https://wa.me/5219516529718?text=Hola%20quiero%20información." target="_blank">
            <div class="social-icon-2">
            <span class="tooltip-2">WhatsApp</span>
            <i class="fab fa-whatsapp"></i>
            </div>
        </a>

        <a href="https://www.instagram.com/ummaoax/" target="_blank">
            <div class="social-icon-3">
            <span class="tooltip-3">Instagram</span>
            <i class="fab fa-instagram"></i>
            </div>
        </a>

    </div>
</header>

<!-- HERO -->
<section class="hero">

<div class="login-container">


                    

    <!-- FORM -->
    <form class="login-form" method="post">
        <h2>Iniciar Sesión</h2>

        <img src="img/user2.png" alt="Usuario" class="profile-img">

        

        <!--<div class="input-group">
            <i class="fa-regular fa-user"></i>
            <input type="text" name="usuario" placeholder="Usuario" required>
        </div>-->

        <div class="input-group">
            <i class="fa-regular fa-paper-plane"></i>
            <input type="email" name="correo" placeholder="Correo" required>
        </div>

        <div class="input-group">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="password" placeholder="Contraseña" required>
        </div>

       

        <div class="options">
            <label><input type="checkbox"> Recuérdame</label>
            <!--<a href="#">¿Olvidaste tu contraseña?</a>-->
        </div>

        
        <button type="submit" class="btn-signin">Iniciar Sesión</button>

        <br>
        
        <div class="options2">
            <a href="2_recuperarpss.php">¿Olvidaste tu contraseña?</a>
        </div>

        <div class="options2">
            <a href="registrarse.php">
                <i class="fa-solid fa-user-plus"> </i> Registrarse</a>
        </div>

        <!--<div class="options2">
            <a href="Farmacia_inicio.html"><i class="fas fa-arrow-left"></i> Regresar</a>
        </div>
        <p class="create-account">
            ¿No tienes cuenta? <a href="#">Crear</a>
        </p>-->
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