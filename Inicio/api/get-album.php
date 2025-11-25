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

    sendJSONResponse(['success' => true, 'album' => $album]);

} catch (Exception $e) {
    error_log('Erro get-album: ' . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno'], 500);
}

?>
