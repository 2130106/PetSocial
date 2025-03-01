<?php
session_start();

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'petsinstagram'; // Nombre de la base de datos
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}

// Obtener publicaciones
$sql = "SELECT * FROM publicaciones ORDER BY id DESC"; // Ordenar por la más reciente
$stmt = $pdo->query($sql);
$publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener comentarios
$sql_comentarios = "SELECT * FROM comentarios where id_respuesta is NULL ORDER BY id ASC";
$sql_respuestas = "SELECT * FROM comentarios where id_respuesta is not NULL ORDER BY id ASC";
$stmt_comentarios = $pdo->query($sql_comentarios);
$stmt_respuestas = $pdo->query($sql_respuestas);
$comentarios = $stmt_comentarios->fetchAll(PDO::FETCH_ASSOC);
$respuestas = $stmt_respuestas->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline - Red Social</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
    :root {
    --color-1: #f1c1d9;
    --color-2: #f7b6e0;
    --color-3: #f49ac2;
    --color-4: #d57b9b;
    --color-5: #b65d7e;
    --border-radius: 12px;
    --font-size-base: 16px; 
}

body {
    background-color: var(--color-1);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: var(--color-5);
    font-size: var(--font-size-base); 
}

h1, h2, h3, h4, h5, h6 {
    font-size: calc(var(--font-size-base) * 1.5);
}

p {
    font-size: calc(var(--font-size-base) * 1); 
}

input, textarea, button {
    font-size: calc(var(--font-size-base) * 0.875);
}

.font-size-buttons {
    position: fixed;
    bottom: 20px;
    right: 20px;
    display: flex;
    gap: 10px;
    z-index: 1000;
}

.font-size-buttons button {
    background-color: var(--color-5);
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    color: white;
    font-size: 18px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.font-size-buttons button:hover {
    background-color: var(--color-4);
    transform: scale(1.1);
}

   
    .navbar {
        background-color: var(--color-3);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .navbar-brand, .nav-link {
        color: white !important;
    }


    .container {
        background-color: white;
        padding: 30px;
        border-radius: var(--border-radius);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-top: 30px;
    }

    .profile-pic-small {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 10px;
        border: 2px solid white;
        box-shadow: 0 0 0 2px var(--color-3);
    }

    .post {
        margin-bottom: 30px;
        border: 1px solid var(--color-2);
        border-radius: var(--border-radius);
        padding: 20px;
        background-color: #fff;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .post:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .post-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .post-header h5 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: bold;
        color: var(--color-5);
    }

    .post-image-container {
        width: 55%;
        overflow: hidden;
        border-radius: var(--border-radius);
        margin-bottom: 15px;
        position: relative;
        cursor: pointer;
    }

    .post-image-container img {
        width: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .post-image-container img.enlarged {
        transform: scale(1.5);
        cursor: zoom-out;
    }

    .post-actions {
        margin-bottom: 15px;
    }

    .post-actions button {
        margin-right: 10px;
        transition: transform 0.2s ease-in-out;
    }

    .post-actions button:hover {
        transform: scale(1.1);
    }

    .comment-section {
        margin-top: 15px;
    }

    .comment-section h6 {
        font-size: 1rem;
        font-weight: bold;
        color: var(--color-5);
        margin-bottom: 10px;
    }

    .comment {
        margin-bottom: 10px;
        padding: 10px;
        background-color: #f9f9f9;
        border-radius: var(--border-radius);
        transition: background-color 0.2s ease-in-out;
    }

    .comment:hover {
        background-color: #f1f1f1;
    }

    .comment strong {
        color: var(--color-3);
    }

    .replies {
        margin-left: 20px;
        margin-top: 10px;
    }

    .comment-input {
        width: 50%;
        padding: 10px;
        border: 1px solid var(--color-2);
        border-radius: var(--border-radius);
        margin-bottom: 10px;
    }

    .modal-image {
        max-width: 100%;
        max-height: 80vh; 
        margin: 0 auto; 
    }

    .btn-logout {
        background-color: var(--color-4);
        color: white;
        transition: background-color 0.2s ease-in-out;
    }

    .modal-image {
        max-width: 100%;
        height: auto;
        border-radius: 10px;
    }

    .btn-logout:hover {
        background-color: var(--color-5);
    }

    .btn-profile {
        background-color: var(--color-3);
        color: white;
        transition: background-color 0.2s ease-in-out;
    }

    .btn-profile:hover {
        background-color: var(--color-4);
    }

    .upload-form {
        background-color: #fff;
        padding: 20px;
        border-radius: var(--border-radius);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .upload-form h2 {
        color: var(--color-5);
        margin-bottom: 20px;
    }

    .upload-form .form-group label {
        font-weight: bold;
        color: var(--color-5);
    }

    .upload-form .form-control-file {
        border: 1px solid var(--color-2);
        border-radius: var(--border-radius);
        padding: 10px;
        background-color: #f9f9f9;
    }

    .upload-form .btn-success {
        background-color: var(--color-3);
        border: none;
        transition: background-color 0.2s ease-in-out;
    }

    .upload-form .btn-success:hover {
        background-color: var(--color-4);
    }

    .post-header .file-input-container input[type="file"] {
        opacity: 0;
        position: absolute;
        width: 1%;
        height: 1%;
        cursor: pointer;
    }

    .post-header .file-input-container label {
        background-color: var(--color-3);
        color: white;
        padding: 8px 12px;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .post-header .file-input-container label:hover {
        background-color: var(--color-4);
    }

    .post-header button {
        background-color: var(--color-3);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .post-header button:hover {
        background-color: var(--color-4);
    }

    
    .btn-primary {
        background-color: var(--color-3);
        border: none;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: var(--color-4);
    }
    
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
        <a class="navbar-brand" href="timeline.php">Pet's Social 🐾</a>
        <div>
        <button id="toggleReadAloud" class="btn btn-primary mb-2"><i class="fas fa-volume-up"></i> Activar Lectura</button>
                <a href="timeline.php" class="btn btn-primary me-2"><i class="fas fa-home"></i> Inicio</a>
                <a href="perfil.php" class="btn btn-primary me-2"><i class="fas fa-user"></i> Perfil</a>
                <a href="paginaInicio.html" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container">
    <div class="upload-form">
                    <div class="post-header">
                    <img src="<?= htmlspecialchars($_SESSION['pet_photo']) ?>" class="profile-pic-small" alt="Foto de perfil">

                        <form action="procesar_subida.php" method="POST" enctype="multipart/form-data" class="w-100">
                            <textarea class="form-control mt-2" name="description" rows="2" placeholder="¿Qué estás pensando?" required></textarea>
                            <div class="file-input-container mt-2">
                                <label for="uploadedImage" style="cursor: pointer;"><i class="fas fa-image"></i> Subir imagen</label>
                                <input type="file" id="uploadedImage" name="uploadedImage" required style="display:flex">
                                <button type="submit" class="btn btn-primary btn-sm mb-1"><i class="fas fa-paper-plane"></i> Publicar </button>
                            </div>
                        </form>
                    </div>
                </div>

        <?php foreach ($publicaciones as $publicacion): ?>

            <?php
                // Obtener la foto de perfil del usuario para esta publicación
                $sql_usuario = "SELECT foto_perfil FROM usuarios WHERE id = :usuario_id";
                $stmt_usuario = $pdo->prepare($sql_usuario);
                $stmt_usuario->execute(['usuario_id' => $publicacion['usuario_id']]);
                $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

                // Verificar si el usuario tiene foto de perfil
                if (!$usuario) {
                    $usuario['foto_perfil'] = 'ruta/default.jpg'; // Foto por defecto si no tiene foto
                }

                $sql_likes = "SELECT * FROM likes where id_publicacion = $publicacion[id]";
                $stmt_likes = $pdo->query($sql_likes);
                $likes = $stmt_likes->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="post">
                <div class="post-header">
                    <img src="<?= htmlspecialchars($usuario['foto_perfil']) ?>" class="profile-pic-small" alt="Foto de perfil">
                    <h5>
                        <a href="perfil2.php?id=<?php echo urlencode($publicacion['usuario_id']); ?>">
                            <?php echo htmlspecialchars($publicacion['usuario']); ?>
                        </a>
                    </h5>
                </div>
                <p><?php echo htmlspecialchars($publicacion['descripcion']);  ?></p>
                <div class="post-image-container">
                    <img src="<?php echo htmlspecialchars($publicacion['imagen_ruta']); ?>" alt="Imagen de publicación" class="post-image"
                    data-bs-toggle="modal" 
                    data-bs-target="#imageModal" 
                    data-image="<?= htmlspecialchars($publicacion['imagen_ruta']) ?>" 
                    data-post-id="<?= htmlspecialchars($publicacion['id']) ?>" 
                    data-likes="<?= htmlspecialchars(Count($likes) ?? 0) ?>">
                </div>
                <div class="post-actions">
                    <button class="btn btn-primary btn-like" data-id="<?php echo $publicacion['id']; ?>"> 💗 Like : <strong class="likes"><?php echo htmlspecialchars(Count($likes) ?? 0); ?></strong></button>
                </div>

                <div class="comment-section">
                    <h6>Comentarios:</h6>
                    <?php foreach ($comentarios as $comentario): ?>
                        <?php if ($comentario['id_publicacion'] == $publicacion['id']): ?>
                            <div class="comment">
                                <strong>
                                    <a href="perfil2.php?usuario=<?php echo urlencode($comentario['usuario']); ?>&id=<?php echo urlencode($comentario['id']); ?>">
                                        <?php echo htmlspecialchars($comentario['usuario']); ?>
                                    </a>
                                </strong>
                                <?php echo htmlspecialchars($comentario['comentario']); ?>
                                <button class="btn btn-link btn-reply" data-id="<?php echo $comentario['id']; ?>">Responder</button>
                                <!-- Respuestas -->
                                <div class="replies">
                                    <?php foreach ($respuestas as $respuesta): ?>
                                        <?php if ($respuesta['id_respuesta'] == $comentario['id']): ?>
                                            <div class="comment">
                                                <strong>
                                                    <a href="perfi2.php?usuario=<?php echo urlencode($comentario['usuario']); ?>&id=<?php echo urlencode($comentario['id']); ?>">
                                                        <?php echo htmlspecialchars($comentario['usuario']); ?>
                                                    </a>
                                                </strong>
                                                <?php echo htmlspecialchars($respuesta['comentario']); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <input type="text" class="comment-input" placeholder="Escribe un comentario">
                    <button class="btn btn-secondary btn-comment" data-id="<?php echo $publicacion['id']; ?>">Comentar</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <!-- Columna para la imagen -->
                        <div class="col-md-8">
                            <img src="" class="modal-image img-fluid" alt="Imagen en grande" id="modalImage">
                        </div>
                        <!-- Columna para comentarios y likes -->
                        <div class="col-md-4">
                            <div class="comments-section">
                                <h5>Comentarios</h5>
                                <div id="modalComments"></div>
                                <form id="commentForm" class="mt-3">
                                    <textarea class="form-control" placeholder="Añade un comentario..." rows="2"></textarea>
                                    <button type="submit" class="btn btn-primary mt-2">Comentar</button>
                                </form>
                            </div>
                            <div class="likes-section mt-3">
                                <h5>Likes</h5>
                                <div id="modalLikes"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="font-size-buttons">
    <button id="increaseFontSize">A+</button>
    <button id="decreaseFontSize">A-</button>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {


            // Likes
            $('.btn-like').click(function() {
                let id_publicacion = $(this).data('id');
                let likes = $(this).find('strong.likes');
                $.post('procesar_likes.php', { id_publicacion: id_publicacion }, function(response) {
                    let data = JSON.parse(response);
                    if (data.status === 'liked') {
                        likes.text(parseInt(likes.text()) + 1);
                    } else if (data.status === 'unliked') {
                        likes.text(Math.max(0, parseInt(likes.text()) - 1));
                    }
                });
            });

            // Comentarios
            $('.btn-comment').click(function() {
                let id_publicacion = $(this).data('id');
                let comentario = $(this).siblings('.comment-input').val();
                if (comentario.trim() !== '') {
                    $.post('procesar_comentarios.php', { id_publicacion: id_publicacion, comentario: comentario }, function(response) {
                        let data = JSON.parse(response);
                        if (data.status === 'success') {
                            location.reload();
                        }
                    });
                }
            });

            // Responder comentario
            $('.btn-reply').click(function() {
                let id_comentario = $(this).data('id');
                let respuesta = prompt('Escribe tu respuesta:');
                if (respuesta && respuesta.trim() !== '') {
                    $.post('procesar_respuestas.php', { id_comentario: id_comentario, respuesta: respuesta }, function(response) {
                        let data = JSON.parse(response);
                        if (data.status === 'success') {
                            location.reload();
                        }
                    });
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
        const btnModoLectura = document.getElementById("toggleReadAloud");
        let modoLecturaActivo = false;
        const synth = window.speechSynthesis;

        btnModoLectura.addEventListener("click", function () {
            modoLecturaActivo = !modoLecturaActivo;

            if (modoLecturaActivo) {
                btnModoLectura.textContent = "Desactivar Lectura";
                document.body.addEventListener("mouseover", leerTextoBajoMouse);
            } else {
                btnModoLectura.textContent = "Activar Lectura";
                synth.cancel(); // Detener cualquier lectura en curso
                document.body.removeEventListener("mouseover", leerTextoBajoMouse);
            }
        });

        function leerTextoBajoMouse(event) {
            if (!modoLecturaActivo) return;

            let texto = event.target.innerText.trim();

            // Si el usuario pasa por encima de una imagen, leer mensaje sobre la foto
            if (event.target.classList.contains('post-image')) {
                let postUserName = event.target.closest('.post').querySelector('.post-header h5 a').innerText;
                texto = `Estás visualizando la foto de: ${postUserName}`;
            }

            // Si el usuario pasa por encima de un comentario
            if (event.target.classList.contains('comment')) {
                let comentarioUsuario = event.target.querySelector('strong a').innerText;
                let comentarioTexto = event.target.innerText.replace(comentarioUsuario, '').trim();
                texto = `${comentarioUsuario} comentó: ${comentarioTexto}`;
            }

            // Si el usuario pasa por encima del botón de like, leer la cantidad de likes
            if (event.target.classList.contains('btn-like')) {
                let likesCount = event.target.querySelector('.likes').innerText;
                texto = `Esta publicación tiene ${likesCount} likes. Dar like.`;
            }

            if (texto.length > 0) {
                synth.cancel(); // Detener cualquier lectura anterior
                const utterance = new SpeechSynthesisUtterance(texto);
                utterance.lang = "es-ES"; // Idioma español
                utterance.rate = 1; // Velocidad normal
                utterance.pitch = 1; // Tono normal
                synth.speak(utterance);
            }
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
    const increaseFontSizeBtn = document.getElementById('increaseFontSize');
    const decreaseFontSizeBtn = document.getElementById('decreaseFontSize');
    const root = document.documentElement;

    increaseFontSizeBtn.addEventListener('click', () => {
        let currentSize = parseFloat(getComputedStyle(root).getPropertyValue('--font-size-base'));
        if (currentSize < 24) { // Límite máximo de 24px
            root.style.setProperty('--font-size-base', `${currentSize + 2}px`);
        }
    });

    decreaseFontSizeBtn.addEventListener('click', () => {
        let currentSize = parseFloat(getComputedStyle(root).getPropertyValue('--font-size-base'));
        if (currentSize > 12) { // Límite mínimo de 12px
            root.style.setProperty('--font-size-base', `${currentSize - 2}px`);
        }
    });
});

        $(document).ready(function() {
            // Mostrar la imagen en grande en el modal con comentarios y likes
            $('.post-image').click(function() {
                const imageUrl = $(this).data('image');
                const postId = $(this).data('post-id');
                const likes = $(this).data('likes');

                $('#modalImage').attr('src', imageUrl);
                $('#modalLikes').html(`<p>${likes} likes</p>`);

                // Hacer una solicitud AJAX para obtener los comentarios de la publicación específica
                $.ajax({
                    url: 'obtener_comentarios.php',
                    method: 'GET',
                    data: { post_id: postId },
                    success: function(response) {
                        const comments = JSON.parse(response);
                        $('#modalComments').html(comments.map(comment => `<div class="comment"><strong>${comment.usuario}</strong>: ${comment.comentario}</div>`).join(''));
                    },
                    error: function() {
                        $('#modalComments').html('<p>Error al cargar los comentarios.</p>');
                    }
                });
            });
            // Manejar el envío de comentarios
            $('#commentForm').submit(function(e) {
                e.preventDefault();
                const commentText = $(this).find('textarea').val();
                if (commentText.trim() !== '') {
                    $('#modalComments').append(`<div class="comment"><strong>Usuario</strong>: ${commentText}</div>`);
                    $(this).find('textarea').val('');
                }
            });
        });

    </script>
</body>
</html>