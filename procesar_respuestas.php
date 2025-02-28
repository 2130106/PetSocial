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

// Validar los datos enviados por POST
if (isset($_POST['id_comentario'], $_POST['respuesta']) && !empty($_POST['respuesta'])) {
    $id_comentario = $_POST['id_comentario'];
    $respuesta = trim($_POST['respuesta']);

    // Insertar la respuesta en la base de datos
    $sql = "INSERT INTO comentarios (id_publicacion, id_respuesta, usuario, comentario) 
            SELECT id_publicacion, :id_comentario, :usuario, :respuesta 
            FROM comentarios WHERE id = :id_comentario";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            ':id_comentario' => $id_comentario,
            ':usuario' => $usuario,
            ':respuesta' => $respuesta
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Respuesta agregada con éxito']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al agregar la respuesta: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
}
?>
