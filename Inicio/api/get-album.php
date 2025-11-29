<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJSONResponse(['success' => false, 'message' => 'Método não permitido'], 405);
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    sendJSONResponse(['success' => false, 'message' => 'ID de álbum inválido'], 400);
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('SELECT * FROM albums WHERE id = ?');
    $stmt->execute([$id]);
    $album = $stmt->fetch();

    if (!$album) {
        sendJSONResponse(['success' => false, 'message' => 'Álbum não encontrado'], 404);
    }

    // Processar caminho da capa
    $cover = $album['cover_image'] ?? '';
    if ($cover) {
        // Verificar em qual pasta a imagem existe
        if (file_exists(__DIR__ . '/../img/albums/' . $cover)) {
            $album['cover_url'] = '/img/albums/' . $cover;
        } elseif (file_exists(__DIR__ . '/../uploads/' . $cover)) {
            $album['cover_url'] = '/uploads/' . $cover;
        } elseif (file_exists(__DIR__ . '/../img/' . $cover)) {
            $album['cover_url'] = '/img/' . $cover;
        } else {
            // Fallback: tentar encontrar por prefixo cover_ na pasta uploads
            $album['cover_url'] = '/img/' . $cover;
        }
    } else {
        $album['cover_url'] = null;
    }

    sendJSONResponse(['success' => true, 'album' => $album]);

} catch (Exception $e) {
    error_log('Erro get-album: ' . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno'], 500);
}

?>
