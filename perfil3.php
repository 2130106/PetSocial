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

// Obtener publicaciones del usuario logueado
$sql = "SELECT * FROM publicaciones WHERE usuario = ? ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['username']]);
$publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener comentarios
$sql_comentarios = "SELECT * FROM comentarios WHERE id_respuesta IS NULL ORDER BY id ASC";
$sql_respuestas = "SELECT * FROM comentarios WHERE id_respuesta IS NOT NULL ORDER BY id ASC";
$stmt_comentarios = $pdo->query($sql_comentarios);
$stmt_respuestas = $pdo->query($sql_respuestas);
$comentarios = $stmt_comentarios->fetchAll(PDO::FETCH_ASSOC);
$respuestas = $stmt_respuestas->fetchAll(PDO::FETCH_ASSOC);

// Conexión para foto de perfil
$host_db = "localhost";
$user_db = "root";
$password_db = "";
$dbname_db = "petsInstagram";

$conn = new mysqli($host_db, $user_db, $password_db, $dbname_db);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$message = "";

// Procesar subida de foto de perfil
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $tipoArchivo = $_FILES['photo']['type'];
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];

    if (!in_array($tipoArchivo, $tiposPermitidos)) {
        $message = "Solo se permiten imágenes JPEG, PNG o GIF.";
    } else {
        if ($_FILES['photo']['size'] > 5000000) {
            $message = "El archivo es demasiado grande. El tamaño máximo permitido es 5MB.";
        } else {
            $carpetaDestino = "uploads/";
            if (!is_dir($carpetaDestino)) {
                mkdir($carpetaDestino, 0777, true);
            }

            $nombreArchivo = time() . "_" . basename($_FILES['photo']['name']);
            $rutaDestino = $carpetaDestino . $nombreArchivo;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $rutaDestino)) {
                $stmt = $conn->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
                $stmt->bind_param("si", $rutaDestino, $user_id);

                if ($stmt->execute()) {
                    $message = "Foto de perfil actualizada con éxito.";
                    $_SESSION['pet_photo'] = $rutaDestino;
                } else {
                    $message = "Error al actualizar la foto de perfil.";
                }
            } else {
                $message = "Error al mover el archivo al servidor.";
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
    <title>Pet's Social - Perfil</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ffcad4;
            --secondary-color: #ffd1dc;
            --background-color: #fff5fa;
            --text-color: #6d6875;
            --border-radius: 12px;
        }

        body {
            background-color: var(--background-color);
            font-family: system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: var(--primary-color);
            padding: 1rem;
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .nav-link.active {
            color: white !important;
        }

        .btn-logout {
            background-color: #ff6b6b;
            color: white;
            transition: background-color 0.2s ease-in-out;
        }
        .btn-logout:hover {
            background-color: #ff4c4c;
        }
        .btn-profile {
            background-color: #a2d2ff;
            color: white;
            transition: background-color 0.2s ease-in-out;
        }
        .btn-profile:hover {
            background-color: #7fbfff;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 1rem;
        }

        .profile-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-top: -40px;
            position: relative;
            z-index: 1;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 0 0 3px var(--secondary-color);
            transition: transform 0.3s ease;
        }

        .profile-pic:hover {
            transform: scale(1.05);
        }

        .post-card {
            background: white;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease;
        }

        .post-card:hover {
            transform: translateY(-2px);
        }

        .post-image {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
            border-radius: var(--border-radius);
            margin-top: 1rem;
        }

        .reaction-btn {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            background-color: white;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .reaction-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .comment-input {
            width: calc(100% - 60px);
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0.5rem;
            }
            
            .profile-pic {
                width: 90px;
                height: 90px;
            }
        }
    </style>
</head>
<body>
    <!-- Navegación -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Pet's Social</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        <div>
            <a href="timeline.php" class="btn btn-profile">Inicio</a>
            <a href="perfil.php" class="btn btn-profile">Perfil</a>
            <a href="paginaInicio.html" class="btn btn-logout">Cerrar Sesión</a>
        </div>
    </nav>
    </nav>

    <!-- Sección del Perfil -->
    <main class="container">
        <section class="profile-section">
            <div class="d-flex align-items-center gap-3 p-3">
                <img src="<?= htmlspecialchars($_SESSION['pet_photo']) ?>" class="profile-pic" alt="Foto de perfil">
                <div>
                    <h2 class="mb-0"><?= htmlspecialchars($_SESSION['pet_name']) ?></h2>
                    <button id="changeProfilePicBtn" class="btn btn-secondary mt-2">Cambiar Foto</button>
                </div>
            </div>

            <!-- Formulario para cambiar foto -->
            <form action="procesar_subida_foto_perfil.php" method="POST" enctype="multipart/form-data" 
                  class="update-photo-container d-none p-3 border-top">
                <input type="file" class="form-control mb-2" name="photo" required>
                <button type="submit" class="btn btn-primary w-100">Actualizar</button>
            </form>
        </section>

        <!-- Sección de Publicaciones -->
        <section class="mt-4">
            <?php foreach ($publicaciones as $post): ?>
                <article class="post-card">
                    <div class="card-header d-flex align-items-center gap-3 px-3 py-2">
                        <img src="<?= htmlspecialchars($_SESSION['pet_photo']) ?>" class="rounded-circle" width="40" height="40" alt="Perfil">
                        <span class="font-weight-bold"><?= htmlspecialchars($post['usuario']) ?></span>
                    </div>
                    
                    <div class="card-body p-3">
                        <p class="mb-3"><?= htmlspecialchars($post['descripcion']) ?></p>
                        <img src="<?= htmlspecialchars($post['imagen_ruta']) ?>" class="post-image" alt="Publicación">
                        
                        <!-- Botones de reacción -->
                        <div class="d-flex justify-content-between mt-3">
                            <?php
                                $sql = "SELECT COUNT(*) as count FROM likes WHERE id_publicacion = {$post['id']}";
                                $stmt = $pdo->query($sql);
                                $likes = $stmt->fetchColumn();
                            ?>
                            <button class="reaction-btn" data-id="<?= $post['id'] ?>">
                                👍 <?= number_format($likes) ?>
                            </button>
                      
                        </div>

                        <!-- Comentarios -->
                        <div class="comments-section mt-3">
                            <h6 class="mb-3">Comentarios</h6>
                            
                            <?php foreach ($comentarios as $comment): ?>
                                <?php if ($comment['id_publicacion'] == $post['id']): ?>
                                    <div class="comment mb-2">
                                        <strong><?= htmlspecialchars($comment['usuario']) ?>:</strong>
                                        <?= htmlspecialchars($comment['comentario']) ?>
                                        
                                        <!-- Respuestas -->
                                        <?php foreach ($respuestas as $reply): ?>
                                            <?php if ($reply['id_respuesta'] == $comment['id']): ?>
                                                <div class="ml-4 mt-1">
                                                    <strong><?= htmlspecialchars($reply['usuario']) ?>:</strong>
                                                    <?= htmlspecialchars($reply['comentario']) ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <!-- Input para comentarios -->
                            <div class="d-flex gap-2 mt-2">
                                <input type="text" 
                                       class="form-control comment-input" 
                                       placeholder="Escribe un comentario..."
                                       data-post-id="<?= $post['id'] ?>">
                                <button class="btn btn-primary">Comentar</button>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </main>

    <!-- Scripts necesarios -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Cambiar foto de perfil
            $('#changeProfilePicBtn').click(function() {
                $('.update-photo-container').toggleClass('d-none');
            });

            // Comentarios y respuestas
            $('.btn-comment').click(function() {
                let postId = $(this).data('postId');
                let comment = $(this).siblings('.comment-input').val();
                
                $.post('procesar_comentario.php', { postId: postId, comment: comment })
                    .done(() => location.reload());
            });
        });
    </script>
</body>
</html>