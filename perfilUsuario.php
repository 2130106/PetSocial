<?php
session_start();
if (!isset($_GET['id'])) {
    die("Usuario no especificado Arriba.");
}

$usuario_id = $_GET['id'];

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

// Obtener datos del usuario seleccionado
$sql_usuario = "SELECT * FROM usuarios WHERE id = ?";
$stmt_usuario = $pdo->prepare($sql_usuario);
$stmt_usuario->execute([$usuario_id]);
$usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuario no encontrado Abajo.");
}

//var_dump($usuario);


$sql_publicaciones = "SELECT * FROM publicaciones WHERE usuario_id = ? ORDER BY id DESC";
$stmt_publicaciones = $pdo->prepare($sql_publicaciones);
$stmt_publicaciones->execute([$usuario_id]);
$publicaciones = $stmt_publicaciones->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($usuario['nombre']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Perfil de <?php echo htmlspecialchars($usuario['nombre']); ?></h2>
        <img src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" class="img-fluid rounded-circle" width="100" alt="Foto de perfil">
        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre']); ?></p>
        
        <h3>Publicaciones</h3>
        <?php foreach ($publicaciones as $publicacion): ?>
            <div class="post mb-3 p-3 border rounded">
                <p><?php echo htmlspecialchars($publicacion['descripcion']); ?></p>
                <img src="<?php echo htmlspecialchars($publicacion['imagen_ruta']); ?>" class="img-fluid rounded" alt="Publicación">
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
