<?php
    // Conexión a la base de datos
    $host = "localhost";
    $user = "root";
    $password = "";
    $dbname = "petsInstagram";

    $conn = new mysqli($host, $user, $password, $dbname);

    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }


    session_start();


    $message = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST['login'])) {
            // Inicio de sesión
            $email = $_POST['loginEmail'];
            $password = $_POST['loginPassword'];

            $stmt = $conn->prepare("SELECT id, nombre, apellido, contrasena, nombre_mascota, foto_perfil FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['contrasena'])) {
                    
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['nombre'] . ' ' . $row['apellido'];
                    $_SESSION['pet_name'] = $row['nombre_mascota'];
                    $_SESSION['pet_photo'] = $row['foto_perfil'];
                    header("Location: timeline.php");
                    exit();
                } else {
                    $message = "Contraseña incorrecta.";
                }
            } else {
                $message = "No se encontró el usuario.";
            }

        } elseif (isset($_POST['register'])) {

            $nombre = $_POST['registerName'];
            $apellido = $_POST['registerLastName'];
            $fechaNacimiento = $_POST['registerDOB'];
            $email = $_POST['registerEmail'];
            $password = $_POST['registerPassword'];
            $nombreMascota = $_POST['registerPetName'];


            $passwordHashed = password_hash($password, PASSWORD_DEFAULT);


            $fotoPerfil = null;
            if (isset($_FILES['registerPetPhoto']) && $_FILES['registerPetPhoto']['error'] === UPLOAD_ERR_OK) {
                $carpetaDestino = "uploads/";
                if (!is_dir($carpetaDestino)) {
                    mkdir($carpetaDestino, 0777, true);
                }

                // Validar que el archivo sea una imagen
                $tipoArchivo = strtolower(pathinfo($_FILES['registerPetPhoto']['name'], PATHINFO_EXTENSION));
                if (in_array($tipoArchivo, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $nombreArchivo = time() . "_" . basename($_FILES['registerPetPhoto']['name']);
                    $rutaDestino = $carpetaDestino . $nombreArchivo;

                    if (move_uploaded_file($_FILES['registerPetPhoto']['tmp_name'], $rutaDestino)) {
                        $fotoPerfil = $rutaDestino;
                    } else {
                        $message = "Error al subir la imagen.";
                    }
                } else {
                    $message = "Solo se permiten archivos de imagen (JPG, JPEG, PNG, GIF).";
                }
            }

            if (empty($message)) {

                if (strlen($password) < 8 || !preg_match('/[a-zA-Z].*[a-zA-Z]/', $password)) {
                    $message = "La contraseña debe tener al menos 8 caracteres y al menos 2 letras.";
                } else {
                    // Cifrar contraseña
                    $passwordHashed = password_hash($password, PASSWORD_DEFAULT);
                
                    // Insertar datos en la base de datos
                    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, fecha_nacimiento, email, contrasena, nombre_mascota, foto_perfil) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssss", $nombre, $apellido, $fechaNacimiento, $email, $passwordHashed, $nombreMascota, $fotoPerfil);
                
                    if ($stmt->execute()) {
                        $message = "Registro exitoso. ¡Bienvenido, $nombre $apellido!";
                    } else {
                        $message = "Error al registrar. Inténtalo nuevamente.";
                    }
                }
                
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login y Registro</title>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
        <style>
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            :root {
                --color-1: #f1c1d9;
                --color-2: #f7b6e0;
                --color-3: #f49ac2;
                --color-4: #d57b9b;
                --color-5: #b65d7e;
            }

            body {
                font-family: 'Montserrat', sans-serif;
                background: linear-gradient(to right, var(--color-4), var(--color-2));
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                overflow: hidden;
            }

            .container {
                background-color: #fff;
                border-radius: 20px;
                box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);
                width: 1000px;
                max-width: 100%;
                min-height: 600px;
                position: relative;
                overflow: hidden;
                display: flex;
            }

            .form-container {
                position: absolute;
                top: 0;
                height: 100%;
                transition: all 0.6s ease-in-out;
                width: 50%;
                padding: 40px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
            }

            .sign-in-container {
                position: relative;
                z-index: 2;
            }

            .sign-up-container {
                left: 0;
                opacity: 0;
                z-index: 1;
            }

            .container.right-panel-active .sign-in-container {
                transform: translateX(0);
            }

            .container.right-panel-active .sign-up-container {
                transform: translateX(100%);
                opacity: 1;
                z-index: 5;
                animation: show 0.6s;
            }

            @keyframes show {
                0%, 49.99% {
                    opacity: 0;
                    z-index: 1;
                }
                50%, 100% {
                    opacity: 1;
                    z-index: 5;
                }
            }

            .overlay-container {
                position: absolute;
                top: 0;
                left: 50%;
                width: 50%;
                height: 100%;
                overflow: hidden;
                transition: transform 0.6s ease-in-out;
                z-index: 100;
            }

            .container.right-panel-active .overlay-container {
                transform: translateX(-100%);
            }

            .overlay {
                background: linear-gradient(to right, var(--color-4), var(--color-2));
                color: #fff;
                position: relative;
                left: -100%;
                height: 100%;
                width: 200%;
                transform: translateX(0);
                transition: transform 0.6s ease-in-out;
            }

            .container.right-panel-active .overlay {
                transform: translateX(50%);
            }

            .overlay-panel {
                position: absolute;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                padding: 0 40px;
                text-align: center;
                top: 0;
                height: 100%;
                width: 50%;
                transform: translateX(0);
                transition: transform 0.6s ease-in-out;
            }

            .overlay-left {
                transform: translateX(-20%);
            }

            .container.right-panel-active .overlay-left {
                transform: translateX(0);
            }

            .overlay-right {
                right: 0;
                transform: translateX(0);
            }

            .container.right-panel-active .overlay-right {
                transform: translateX(20%);
            }

            h1 {
                font-weight: bold;
                margin-bottom: 20px;
            }

            p {
                font-size: 14px;
                font-weight: 100;
                line-height: 20px;
                letter-spacing: 0.5px;
                margin: 20px 0 30px;
            }

            input {
                width: 100%;
                padding: 12px 15px;
                margin: 8px 0;
                border: 1px solid #ccc;
                border-radius: 5px;
                outline: none;
            }

            button {
                border-radius: 20px;
                border: 1px solid #fff;
                background-color: #fff;
                color: var(--color-4);
                font-size: 12px;
                font-weight: bold;
                padding: 12px 45px;
                letter-spacing: 1px;
                text-transform: uppercase;
                transition: transform 80ms ease-in;
                cursor: pointer;
            }

            button:active {
                transform: scale(0.95);
            }

            button:focus {
                outline: none;
            }

            button.ghost {
                background-color: transparent;
                border-color: #fff;
                color: #fff;
            }

            .alert-overlay {
                position: fixed;
                top: 40%;
                left: 50%;
                transform: translateX(-50%);
                z-index: 1000;
                padding: 35px 20px;
                border-radius: 5px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                background-color: var(--color-4) !important;
                color: var(--color-1) !important;
                width: 90%;
                max-width: 500px;
                animation: fadeIn 0.5s ease-in-out;
            }

            .alert-overlay .close-btn {
                background: none;
                border: none;
                color: inherit;
                font-size: 20px;
                cursor: pointer;
                margin-left: 15px;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translate(-50%, -20px);
                }
                to {
                    opacity: 1;
                    transform: translate(-50%, 0);
                }
            }

            .alert {
                margin-bottom: 20px;
                padding: 10px;
                border-radius: 5px;
                text-align: center;
                width: 100%;
                max-width: 300px;
            }

            .alert-info {
                background-color: #d1ecf1;
                color: #0c5460;
            }

            .alert-error {
                background-color: #f8d7da;
                color: #721c24;
            }

            @media (max-width: 768px) {
                .container {
                    flex-direction: column;
                    height: auto;
                }

                .form-container {
                    position: relative;
                    width: 100%;
                    height: auto;
                    padding: 20px;
                }

                .overlay-container {
                    display: none;
                }
            }

            /* Estilos para la alerta de contraseña */
            .password-alert {
                display: none;
                padding: 10px;
                margin: 10px 0;
                border-radius: 5px;
                background-color: #f8d7da; /* Fondo rojo claro */
                color: #721c24; /* Texto rojo oscuro */
                border: 1px solid #f5c6cb; /* Borde rojo */
                font-size: 14px;
                text-align: left;
                width: 100%;
                max-width: 300px;
                animation: fadeIn 0.3s ease-in-out;
            }

            /* Animación para mostrar la alerta */
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    </head>
    <body>

        <?php if (!empty($message)): ?>
            <div class="alert-overlay <?= strpos($message, 'Error') !== false ? 'alert-error' : 'alert-info' ?>">
                <span><?= htmlspecialchars($message) ?></span>
                <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
            </div>
        <?php endif; ?>

        <div class="container" id="container">
            <!-- Formulario de Registro -->
            <div class="form-container sign-up-container">
                <form method="POST" action="" enctype="multipart/form-data">
                    <h1>Crear Cuenta</h1>
                    <input type="text" name="registerName" placeholder="Nombre" required>
                    <input type="text" name="registerLastName" placeholder="Apellido" required>
                    <input type="date" name="registerDOB" placeholder="Fecha de Nacimiento" required>
                    <input type="email" name="registerEmail" placeholder="Correo Electrónico" required>
                    <input type="password" name="registerPassword" placeholder="Contraseña" required>           
                    <input type="text" name="registerPetName" placeholder="Nombre de la Mascota" required>
                    <input type="file" name="registerPetPhoto" id="registerPetPhoto" accept="image/*" required>
                    <label for="registerPetPhoto" id="fileLabel">Ingresa la foto de tu mascota</label>
                    <button type="submit" name="register">Registrarse</button>
                </form>
            </div>

            <!-- Formulario de Login -->
            <div class="form-container sign-in-container">
                <form method="POST" action="">
                    <h1>Iniciar Sesión</h1>
                    <?php if (!empty($message) && isset($_POST['login'])): ?>
                        <div class="alert alert-error">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>
                    <input type="email" name="loginEmail" placeholder="Correo Electrónico" required>
                    <input type="password" name="loginPassword" placeholder="Contraseña" required>
                    <button type="submit" name="login">Iniciar Sesión</button>
                </form>
            </div>

            <!-- Overlay -->
            <div class="overlay-container">
                <div class="overlay">
                    <div class="overlay-panel overlay-left">
                        <h1>¡Hola!</h1>
                        <p>Ingresa tus datos personales y el nombre de tu mascota.</p>
                        <button class="ghost" id="signUp">Iniciar Sesión</button>
                    </div>
                    <div class="overlay-panel overlay-right">
                        <h1>¡Bienvenido de nuevo!</h1>
                        <p>Para mantenerte conectado, inicia sesión con tu información personal.</p>
                        <button class="ghost" id="signIn">Registrarse</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            const signUpButton = document.getElementById('signIn');
            const signInButton = document.getElementById('signUp');
            const container = document.getElementById('container');

            signUpButton.addEventListener('click', () => {
                container.classList.add('right-panel-active');
            });

            signInButton.addEventListener('click', () => {
                container.classList.remove('right-panel-active');
            });

            const fileInput = document.getElementById('registerPetPhoto');
            const fileLabel = document.getElementById('fileLabel');

            fileInput.addEventListener('change', function() {
                if (fileInput.files.length > 0) {
                    fileLabel.textContent = fileInput.files[0].name;
                } else {
                    fileLabel.textContent = 'Ingresa la foto de tu mascota';
                }
            });

            document.addEventListener("DOMContentLoaded", function () {
                const btnModoLectura = document.createElement("button");
                btnModoLectura.textContent = "Activar Modo Lectura";
                btnModoLectura.style.position = "fixed";
                btnModoLectura.style.top = "10px";
                btnModoLectura.style.right = "10px";
                btnModoLectura.style.zIndex = "9999";
                btnModoLectura.style.padding = "10px";
                btnModoLectura.style.backgroundColor = "#b65d7e";
                btnModoLectura.style.color = "#fff";
                btnModoLectura.style.border = "none";
                btnModoLectura.style.borderRadius = "5px";
                btnModoLectura.style.cursor = "pointer";
                document.body.appendChild(btnModoLectura);

                let modoLecturaActivo = false;
                const synth = window.speechSynthesis;

                btnModoLectura.addEventListener("click", function () {
                    modoLecturaActivo = !modoLecturaActivo;

                    if (modoLecturaActivo) {
                        btnModoLectura.textContent = "Desactivar Modo Lectura";
                        document.body.addEventListener("mouseover", leerTextoBajoMouse);
                    } else {
                        btnModoLectura.textContent = "Activar Modo Lectura";
                        synth.cancel();
                        document.body.removeEventListener("mouseover", leerTextoBajoMouse);
                    }
                });

                function leerTextoBajoMouse(event) {
                    if (!modoLecturaActivo) return;

                    let texto = event.target.innerText.trim();

                    // Definir textos personalizados para los inputs
                    const textosInputs = {
                        "loginEmail": "Ingrese un correo electrónico",
                        "loginPassword": "Ingrese una contraseña",
                        "registerName": "Ingrese su nombre",
                        "registerLastName": "Ingrese su apellido",
                        "registerDOB": "Ingrese su fecha de nacimiento",
                        "registerEmail": "Ingrese un correo electrónico",
                        "registerPassword": "Ingrese una contraseña",
                        "registerPetName": "Ingrese el nombre de su mascota",
                        "registerPetPhoto": "Seleccione una foto de su mascota"
                    };

                    // Si el mouse está sobre un input, leer el texto personalizado
                    if (event.target.tagName === "INPUT") {
                        let id = event.target.id || event.target.name;
                        if (textosInputs[id]) {
                            texto = textosInputs[id];
                        }
                    }

                    if (texto.length > 0) {
                        synth.cancel();
                        const utterance = new SpeechSynthesisUtterance(texto);
                        utterance.lang = "es-ES";
                        utterance.rate = 1;
                        utterance.pitch = 1;
                        synth.speak(utterance);
                    }
                }
            });
        </script>

    </body>
</html>