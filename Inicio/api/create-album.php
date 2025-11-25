<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(['success' => false, 'message' => 'Método não permitido'], 405);
}

if (!isLoggedIn()) {
    sendJSONResponse(['success' => false, 'message' => 'Usuário não autenticado'], 401);
}

$data = $_POST;
if (!isset($data['title']) || !isset($data['artist'])) {
    sendJSONResponse(['success' => false, 'message' => 'Dados obrigatórios faltando'], 400);
}

$title = sanitizeInput($data['title']);
$artist = sanitizeInput($data['artist']);
$release_year = isset($data['release_year']) ? (int)$data['release_year'] : null;
$genre = isset($data['genre']) ? sanitizeInput($data['genre']) : null;
$spotify = isset($data['spotify_url']) ? sanitizeInput($data['spotify_url']) : null;
$apple = isset($data['apple_music_url']) ? sanitizeInput($data['apple_music_url']) : null;

try {
    $pdo = getDBConnection();

    // Handle optional cover upload
    $coverPath = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $errors = validateUpload($_FILES['cover_image']);
        if (count($errors) === 0) {
            $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
            $filename = 'album_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest = UPLOAD_PATH . $filename;
            if (!is_dir(UPLOAD_PATH)) @mkdir(UPLOAD_PATH, 0755, true);
            move_uploaded_file($_FILES['cover_image']['tmp_name'], $dest);
            $coverPath = 'uploads/' . $filename;
        }
    }

    $stmt = $pdo->prepare('INSERT INTO albums (title, artist, cover_image, release_year, genre, spotify_url, apple_music_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
    $stmt->execute([$title, $artist, $coverPath, $release_year, $genre, $spotify, $apple]);

    $newId = $pdo->lastInsertId();
    sendJSONResponse(['success' => true, 'album_id' => (int)$newId]);

} catch (Exception $e) {
    error_log('Erro create-album: ' . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno'], 500);
}

?>
