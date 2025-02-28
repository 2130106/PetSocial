<?php
session_start();
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_publicacion = $_POST['id_publicacion'];
    $tipo_reaccion = $_POST['tipo_reaccion'];
    $usuario = $_SESSION['username'] ?? 'Anonimo';

    $sql = "INSERT INTO reacciones (id_publicacion, usuario, tipo_reaccion) VALUES (:id_publicacion, :usuario, :tipo_reaccion)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_publicacion' => $id_publicacion, 'usuario' => $usuario, 'tipo_reaccion' => $tipo_reaccion]);

    echo json_encode(['status' => 'success']);
}
?>
