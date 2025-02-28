<?php
session_start();

if (!isset($_POST['accion']) || !isset($_POST['perfil_id'])) {
    die('Acción o perfil no especificado.');
}

$accion = $_POST['accion'];
$perfil_id = $_POST['perfil_id'];
$usuario_logueado = $_SESSION['user_id'];

$host = 'localhost';
$dbname = 'petsinstagram'; 
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($accion == 'follow') {
        // Insertar en la tabla de seguidores
        $sql = "INSERT INTO seguidores (usuario_seguidor, usuario_seguido) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_logueado, $perfil_id]);
    } elseif ($accion == 'unfollow') {
        // Eliminar de la tabla de seguidores
        $sql = "DELETE FROM seguidores WHERE usuario_seguidor = ? AND usuario_seguido = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_logueado, $perfil_id]);
    }

    echo 'success';
} catch (PDOException $e) {
    echo 'error';
}
