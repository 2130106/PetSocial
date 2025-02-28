<?php
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

if (isset($_POST['id_publicacion'])) {
    $id_publicacion = $_POST['id_publicacion'];

    $usuario = $_SESSION['username'] ?? 'anonimo';
    $query = "SELECT * FROM likes WHERE id_publicacion = :id_publicacion AND usuario = :usuario";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id_publicacion' => $id_publicacion, 'usuario' => $usuario]);
    $like = $stmt->fetch();

    if ($like) {
        $query = "DELETE FROM likes WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id' => $like['id']]);

        echo json_encode(['status' => 'unliked']);
    } else {
        $query = "INSERT INTO likes (id_publicacion, usuario) VALUES (:id_publicacion, :usuario)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id_publicacion' => $id_publicacion, 'usuario' => $usuario]);

        echo json_encode(['status' => 'liked']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
}
?>
