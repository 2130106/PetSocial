<?php
    session_start();

    if (!isset($_GET['id'])) {
        die("Usuario no especificado Arriba.");
    }
    $usuario_id = $_GET['id'];

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

    $sql_publicaciones = "SELECT * FROM publicaciones WHERE usuario_id = ? ORDER BY id DESC";
    $stmt_publicaciones = $pdo->prepare($sql_publicaciones);
    $stmt_publicaciones->execute([$usuario_id]);
    $publicaciones = $stmt_publicaciones->fetchAll(PDO::FETCH_ASSOC);

    // Obtener el nombre del usuario seleccionado (perfil)
    $sql_usuario = "SELECT nombre, apellido, foto_perfil FROM usuarios WHERE id = ?";
    $stmt_usuario = $pdo->prepare($sql_usuario);
    $stmt_usuario->execute([$usuario_id]);
    $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);
    // Obtener comentarios de la publicación específica
    $sql_comentarios = "SELECT * FROM comentarios WHERE id_publicacion = ? AND id_respuesta IS NULL ORDER BY id ASC";
    $sql_respuestas = "SELECT * FROM comentarios WHERE id_publicacion = ? AND id_respuesta IS NOT NULL ORDER BY id ASC";

    // Verifica si se obtuvo el usuario
    if ($usuario) {
        $nombre = htmlspecialchars($usuario['nombre']);
        $apellido = htmlspecialchars($usuario['apellido']);
        $foto_perfil = htmlspecialchars($usuario['foto_perfil']);
    } else {
        die("Usuario no encontrado.");
    }


    // Obtener comentarios
    $sql_comentarios = "SELECT * FROM comentarios WHERE id_respuesta IS NULL ORDER BY id ASC";
    $sql_respuestas = "SELECT * FROM comentarios WHERE id_respuesta IS NOT NULL ORDER BY id ASC";
    $stmt_comentarios = $pdo->query($sql_comentarios);
    $stmt_respuestas = $pdo->query($sql_respuestas);
    $comentarios = $stmt_comentarios->fetchAll(PDO::FETCH_ASSOC);
    $respuestas = $stmt_respuestas->fetchAll(PDO::FETCH_ASSOC);

    $sql_seguidores = "SELECT COUNT(*) as count FROM seguidores WHERE usuario_seguido = ?";
    $stmt_seguidores = $pdo->prepare($sql_seguidores);
    $stmt_seguidores->execute([$usuario_id]);
    $seguidores = $stmt_seguidores->fetch();
    $cantidad_seguidores = $seguidores['count'];

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

    $perfil_id = $_GET['id'] ?? $user_id;
    $usuario_logueado = $_SESSION['user_id'];


    $sql = "SELECT * FROM seguidores WHERE usuario_seguidor = ? AND usuario_seguido = ?";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([$usuario_logueado, $perfil_id]);
    $sigue = $stmt->fetch();

    $botonTexto = $sigue ? "Dejar de seguir" : "Seguir";
    $accion = $sigue ? "unfollow" : "follow";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet's Social - Perfil</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
     <style>
    :root {
        --color-1: #f1c1d9;
        --color-2: #f7b6e0;
        --color-3: #f49ac2;
        --color-4: #d57b9b;
        --color-5: #b65d7e;
        --border-radius: 12px;
    }

    body {
        background-color: var(--color-1);
        font-family: 'Segoe UI', sans-serif;
        color: var(--color-5);
    }

    .navbar {
        background-color: var(--color-3);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .navbar-brand, .nav-link {
        color: white !important;
    }

    .btn-primary {
        background-color: var(--color-3);
        border: none;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: var(--color-4);
    }

    .profile-section {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-top: 20px;
        max-width: auto;
        margin-left: auto;
        margin-right: auto;
    }

    .profile-pic {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid white;
        box-shadow: 0 0 0 3px var(--color-3);
        transition: transform 0.3s ease;
    }

    .profile-pic-small {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid white;
        box-shadow: 0 0 0 2px var(--color-3);
        margin-right: 10px;
    }

    .profile-pic:hover {
        transform: scale(1.05);
    }

    .upload-form {
        margin-top: 20px;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }

    .post-header {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .post-header textarea {
        flex: 1;
        border: 1px solid var(--color-2);
        border-radius: var(--border-radius);
        padding: 10px;
        resize: none;
        font-size: 14px;
    }

    .post-header textarea::placeholder {
        color: #999;
    }

    .post-header .file-input-container {
        position: relative;
        margin-top: 10px;
    }

    .post-header .file-input-container input[type="file"] {
        opacity: 0;
        position: absolute;
        width: 100%;
        height: 100%;
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

    /* Estilos para la cuadrícula de publicaciones */
    .posts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); /* Columnas de 200px */
        gap: 16px; /* Espacio entre publicaciones */
        padding: 20px;
    }

    .post-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden; /* Para que las imágenes no se salgan del contenedor */
        position: relative;
    }

    .post-card:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    }

    .post-image-container {
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    .post-image {
        width: 100%;
        aspect-ratio: 1;
        object-fit: cover;
        transition: transform 0.3s ease, opacity 0.3s ease;
    }

    .post-image-container:hover .post-image {
        transform: scale(1.1);
        opacity: 0.9;
    }

    .post-content {
        padding: 10px;
    }

    .reaction-btn {
        background: none;
        border: none;
        color: var(--color-5);
        transition: color 0.3s ease;
    }

    .reaction-btn:hover {
        color: var(--color-3);
    }

    .comment-section {
        border-top: 1px solid var(--color-2);
        padding-top: 10px;
    }

    .comment {
        margin-bottom: 10px;
        padding: 10px;
        background-color: #f9f9f9;
        border-radius: var(--border-radius);
    }

    .comment strong {
        color: var(--color-3);
    }

    .comment-input {
        border-radius: var(--border-radius);
        border: 1px solid var(--color-2);
        padding: 10px;
        width: 100%;
    }

    .footer {
        background-color: var(--color-3);
        color: white;
        text-align: center;
        padding: 10px 0;
        margin-top: 30px;
    }

    @media (max-width: 768px) {
        .profile-pic {
            width: 90px;
            height: 90px;
        }

        .posts-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); /* Columnas más pequeñas en móviles */
        }
    }
   
    .profile-and-upload {
        display: flex;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 20px;
    }

    .profile-info {
        flex: 1;
    }

    .upload-form {
        flex: 2;
    }

    @media (max-width: 768px) {
        .profile-and-upload {
            flex-direction: column;
        }
    }


    .modal-image {
        max-width: 100%;
        max-height: 80vh; 
        margin: 0 auto; 
    }


    .btn-secondary {
        background-color: var(--color-2);
        border: none;
        transition: background-color 0.3s ease;
    }

    .btn-secondary:hover {
        background-color: var(--color-3);
    }

    .btn-primary {
        background-color: var(--color-3);
        border: none;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: var(--color-4);
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
</style>
</head>
<body>
    <!-- Navegación -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="timeline.php">Pet's Social 🐾</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">  <span class="navbar-toggler-icon"></span> </button>
        <div>
            <button id="toggleReadAloud" class="btn btn-primary"><i class="fas fa-volume-up"></i> Activar Lectura</button>
            <a href="timeline.php" class="btn btn-primary me-2"><i class="fas fa-home"></i> Inicio</a>
            <a href="perfil.php" class="btn btn-primary me-2"><i class="fas fa-user"></i> Perfil</a>
            <a href="paginaInicio.html" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>

        </div>
        
    </nav>
    </nav>

    <!-- Sección del Perfil -->
    <main class="container">
        <section class="profile-section">
            <div class="d-flex align-items-center gap-3 p-3">
            <img src="<?= !empty($foto_perfil) ? $foto_perfil : 'ruta/default.jpg' ?>" class="profile-pic" alt="Foto de perfil">
            <div>
            <h2 class="mb-0"><?= $nombre . ' ' . $apellido ?></h2>
            <p><strong><?= $cantidad_seguidores ?> seguidores</strong></p>
                </div>
                <button id="followBtn" class="btn btn-primary" data-accion="<?= $accion ?>" data-perfil="<?= $perfil_id ?>">
                    <?= $botonTexto ?>
                </button>
            </div>
        </section>

        <section class="posts-grid">

            <?php if (empty($publicaciones)): ?>
                <p>No hay publicaciones disponibles.</p>
            <?php else: ?>
                <?php foreach ($publicaciones as $post): ?>
                    <?php
                        // Obtener la cantidad de comentarios para esta publicación
                        $sql_comentarios_count = "SELECT COUNT(*) as count FROM comentarios WHERE id_publicacion = ?";
                        $stmt_comentarios_count = $pdo->prepare($sql_comentarios_count);
                        $stmt_comentarios_count->execute([$post['id']]);
                        $comentarios_count = $stmt_comentarios_count->fetchColumn();

                        // Obtener la cantidad de likes para esta publicación
                        $sql_likes = "SELECT COUNT(*) as count FROM likes WHERE id_publicacion = ?";
                        $stmt_likes = $pdo->prepare($sql_likes);
                        $stmt_likes->execute([$post['id']]);
                        $likes = $stmt_likes->fetchColumn();
                    ?>
                    <article class="post-card">
                    <div class="post-image-container" 
                        data-bs-toggle="modal" 
                        data-bs-target="#imageModal" 
                        data-image="<?= htmlspecialchars($post['imagen_ruta']) ?>" 
                        data-post-id="<?= htmlspecialchars($post['id']) ?>" 
                        data-likes="<?= htmlspecialchars($likes) ?>">
                        <img src="<?= htmlspecialchars($post['imagen_ruta']) ?>" class="post-image" alt="Publicación">
                    </div>
                        <div class="post-content">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="<?= htmlspecialchars($_SESSION['pet_photo']) ?>" class="rounded-circle" width="40" height="40" alt="Perfil">
                                <span class="font-weight-bold"><?= htmlspecialchars($post['usuario']) ?></span>
                            </div>
                            <p class="mb-3"><?= htmlspecialchars($post['descripcion']) ?></p>

                            <!-- Botones de reacción y comentarios -->
                            <div class="d-flex justify-content-between mt-3">

                                <button class="reaction-btn" data-id="<?= $post['id'] ?>">
                                    <i class="fas fa-thumbs-up"></i> <?= number_format($likes) ?>
                                </button>
                                <button class="reaction-btn">
                                    <i class="fas fa-comment"></i> <?= number_format($comentarios_count) ?>
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
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
        <div id="fixed-buttons" style="position: fixed; top: 840px; right: 20px; z-index: 1000;">

            <div class="font-size-buttons d-flex gap-2">
                <button id="increaseFontBtn" class="btn btn-secondary">
                    A+
                </button>
                <button id="decreaseFontBtn" class="btn btn-secondary">
                    A-
                </button>
            </div>
        </div>
    </div>
    <!-- Scripts necesarios -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        
        $('#followBtn').click(function() {
            var accion = $(this).data('accion');
            var perfilId = $(this).data('perfil');
            
            $.post('seguir.php', { accion: accion, perfil_id: perfilId })
                .done(function(response) {
                    if (response === 'success') {
                        if (accion === 'follow') {
                            $('#followBtn').text('Dejar de seguir').data('accion', 'unfollow');
                        } else {
                            $('#followBtn').text('Seguir').data('accion', 'follow');
                        }
                    }
                });
        });

        $(document).ready(function() {
        // Función para leer en voz alta
        $('#readAloudBtn').click(function() {
            let textToRead = '';
            $('.post-content p').each(function() {
                textToRead += $(this).text() + ' ';
            });

            if ('speechSynthesis' in window) {
                let utterance = new SpeechSynthesisUtterance(textToRead);
                utterance.lang = 'es-ES'; // Configura el idioma
                window.speechSynthesis.speak(utterance);
            } else {
                alert('Tu navegador no soporta la lectura en voz alta.');
            }
        });

        // Función para aumentar el tamaño de la letra
        $('#increaseFontBtn').click(function() {
            let currentSize = parseFloat($('body').css('font-size'));
            $('body').css('font-size', currentSize * 1.1);
        });

        // Función para disminuir el tamaño de la letra
        $('#decreaseFontBtn').click(function() {
            let currentSize = parseFloat($('body').css('font-size'));
            $('body').css('font-size', currentSize * 0.9);
        });
    });
    document.addEventListener("DOMContentLoaded", function () {
        const btnModoLectura = document.getElementById("toggleReadAloud");
        const btnAumentarLetra = document.getElementById("increaseFontBtn");
        const btnDisminuirLetra = document.getElementById("decreaseFontBtn");
        let modoLecturaActivo = false;
        const synth = window.speechSynthesis;

        // Activar/desactivar la lectura en voz alta
        btnModoLectura.addEventListener("click", function () {
            modoLecturaActivo = !modoLecturaActivo;

            if (modoLecturaActivo) {
                btnModoLectura.innerHTML = '<i class="fas fa-volume-up"></i> Desactivar Lectura';
                document.body.addEventListener("mouseover", leerTextoBajoMouse);
            } else {
                btnModoLectura.innerHTML = '<i class="fas fa-volume-up"></i> Activar Lectura';
                synth.cancel(); // Detener cualquier lectura en curso
                document.body.removeEventListener("mouseover", leerTextoBajoMouse);
            }
        });

        // Aumentar el tamaño de la letra
        btnAumentarLetra.addEventListener("click", function () {
            let currentSize = parseFloat(getComputedStyle(document.body).fontSize);
            document.body.style.fontSize = (currentSize * 1.1) + "px";
        });

        // Disminuir el tamaño de la letra
        btnDisminuirLetra.addEventListener("click", function () {
            let currentSize = parseFloat(getComputedStyle(document.body).fontSize);
            document.body.style.fontSize = (currentSize * 0.9) + "px";
        });

        // Función para leer el texto bajo el mouse
        function leerTextoBajoMouse(event) {
            if (!modoLecturaActivo) return;

            let texto = "";

            // Si el usuario pasa por encima de una imagen, leer mensaje sobre la foto
            if (event.target.classList.contains('post-image')) {
                const post = event.target.closest('.post-card');
                const postUserName = post.querySelector('.font-weight-bold').innerText;
                texto = `Estás visualizando la foto de: ${postUserName}`;
            }

            // Si el usuario pasa por encima de un comentario
            else if (event.target.classList.contains('reaction-btn')) {
                const icono = event.target.querySelector('i');

                if (icono.classList.contains('fa-thumbs-up')) {
                    const likesCount = icono.nextSibling.textContent.trim();
                    texto = `Esta publicación tiene ${likesCount} likes. Dar like.`;
                } else if (icono.classList.contains('fa-comment')) {
                    const commentsCount = icono.nextSibling.textContent.trim();
                    texto = `Esta publicación tiene ${commentsCount} comentarios. Ver comentarios.`;
                }
            }

            // Si el usuario pasa por encima de un texto
            else if (event.target.innerText) {
                texto = event.target.innerText.trim();
            }

            if (texto.length > 0) {
                synth.cancel();
                const utterance = new SpeechSynthesisUtterance(texto);
                utterance.lang = "es-ES"; // Idioma 
                utterance.rate = 1; 
                utterance.pitch = 1;
                synth.speak(utterance);
            }
        }
    });

    $(document).ready(function() {
        // Mostrar la imagen en grande en el modal con comentarios y likes
        $('.post-image-container').click(function() {
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