<?php
// Iniciar sesión para obtener el usuario activo
session_start();

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'petsinstagram'; 
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}


$uploadDir = 'uploads/';


if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); 
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = trim($_POST['description']);  
    $imagen = $_FILES['uploadedImage'];  
    $usuario = $_SESSION['username'] ?? 'Anonimo';  
    $usuario_id = $_SESSION['user_id'];

    if ($imagen['error'] !== UPLOAD_ERR_OK) {
        die("Error al subir la imagen.");
    }
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($imagen['type'], $allowedTypes)) {
        die("Solo se permiten archivos JPEG, PNG o GIF.");
    }

    if ($imagen['size'] > 5 * 1024 * 1024) {
        die("El archivo es demasiado grande. Tamaño máximo: 5MB.");
    }

    $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
    $nombreArchivo = uniqid('img_') . '.' . $extension;
    $rutaArchivo = $uploadDir . $nombreArchivo;

    if (!move_uploaded_file($imagen['tmp_name'], $rutaArchivo)) {
        die("Error al guardar el archivo.");
    }

    $sql = "INSERT INTO publicaciones (usuario, descripcion, imagen_ruta, usuario_id) VALUES (:usuario, :descripcion, :imagen_ruta, :usuario_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'usuario' => $usuario,
        'descripcion' => $descripcion,
        'imagen_ruta' => $rutaArchivo,
        'usuario_id' => $usuario_id
    ]);

    echo '<meta http-equiv="refresh" content="0;url=timeline.php">';
} else {
    echo "Método no permitido.";
}
?>
