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
    die(json_encode(['status' => 'error', 'message' => 'Error en la conexión: ' . $e->getMessage()]));
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
    exit;
}

$usuario = $_SESSION['username'];

if (isset($_POST['id_publicacion'], $_POST['comentario']) && !empty($_POST['comentario'])) {
    $id_publicacion = $_POST['id_publicacion'];
    $comentario = trim($_POST['comentario']);
    $id_respuesta = !empty($_POST['id_respuesta']) ? $_POST['id_respuesta'] : null; // Soporta respuestas

   
    $sql = "INSERT INTO comentarios (id_publicacion, id_respuesta, usuario, comentario) 
            VALUES (:id_publicacion, :id_respuesta, :usuario, :comentario)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            ':id_publicacion' => $id_publicacion,
            ':id_respuesta' => $id_respuesta,
            ':usuario' => $usuario,
            ':comentario' => $comentario
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Comentario o respuesta agregado con éxito']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al agregar el comentario: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
}
?>
