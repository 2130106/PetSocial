<?php
session_start();

// Verifica si el usuario está logueado
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

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

// Obtener el ID de la publicación desde la solicitud GET
$post_id = $_GET['post_id'];

// Obtener comentarios de la publicación específica
$sql_comentarios = "SELECT * FROM comentarios WHERE id_publicacion = ? AND id_respuesta IS NULL ORDER BY id ASC";
$stmt_comentarios = $pdo->prepare($sql_comentarios);
$stmt_comentarios->execute([$post_id]);
$comentarios = $stmt_comentarios->fetchAll(PDO::FETCH_ASSOC);

// Devolver los comentarios en formato JSON
echo json_encode($comentarios);
?>