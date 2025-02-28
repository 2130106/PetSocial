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

// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    // Si no está autenticado, redirigir al login
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario desde la sesión
$user_id = $_SESSION['user_id'];
$message = "";

// Verificar si se ha subido un archivo de imagen
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    // Verificar tipo de archivo (solo imágenes)
    $tipoArchivo = $_FILES['photo']['type'];
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];

    if (!in_array($tipoArchivo, $tiposPermitidos)) {
        $message = "Solo se permiten imágenes JPEG, PNG o GIF.";
    } else {
        // Verificar tamaño del archivo (por ejemplo, máximo 5MB)
        if ($_FILES['photo']['size'] > 5000000) {
            $message = "El archivo es demasiado grande. El tamaño máximo permitido es 5MB.";
        } else {
            // Crear carpeta de destino si no existe
            $carpetaDestino = "uploads/";
            if (!is_dir($carpetaDestino)) {
                mkdir($carpetaDestino, 0777, true);
            }

            // Renombrar el archivo para evitar conflictos
            $nombreArchivo = time() . "_" . basename($_FILES['photo']['name']);
            $rutaDestino = $carpetaDestino . $nombreArchivo;

            // Mover el archivo a la carpeta de destino
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $rutaDestino)) {
                // Actualizar la base de datos con la nueva foto de perfil
                $stmt = $conn->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
                $stmt->bind_param("si", $rutaDestino, $user_id);

                if ($stmt->execute()) {
                    $message = "Foto de perfil actualizada con éxito.";
                    $_SESSION['pet_photo'] = $rutaDestino;  // Actualizar la sesión con la nueva foto
                } else {
                    $message = "Error al actualizar la foto de perfil.";
                }
            } else {
                $message = "Error al mover el archivo al servidor.";
            }
        }
    }
} else {
    $message = "No se ha seleccionado ninguna foto.";
}

// Guardar el mensaje en la sesión para mostrarlo en perfil.php
$_SESSION['upload_message'] = $message;

// Redirigir al perfil.php
header("Location: perfil.php");
exit();
