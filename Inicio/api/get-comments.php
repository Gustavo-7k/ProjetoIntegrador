<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJSONResponse(['success' => false, 'message' => 'Método não permitido'], 405);
}

$album_id = isset($_GET['album_id']) ? (int)$_GET['album_id'] : 0;
if ($album_id <= 0) sendJSONResponse(['success' => false, 'message' => 'Álbum inválido'], 400);

try {
    $pdo = getDBConnection();

    // Fetch comments with user info
    $stmt = $pdo->prepare(
        'SELECT c.*, u.username, u.full_name, u.profile_image FROM comments c JOIN users u ON c.user_id = u.id WHERE c.album_id = ? AND c.status = "approved" ORDER BY c.created_at ASC'
    );
    $stmt->execute([$album_id]);
    $rows = $stmt->fetchAll();

    // Processar imagens de perfil
    foreach ($rows as &$row) {
        if (!empty($row['profile_image'])) {
            $imagePath = __DIR__ . '/../uploads/' . $row['profile_image'];
            if (file_exists($imagePath)) {
                $row['profile_image'] = '/uploads/' . $row['profile_image'];
            } else {
                $row['profile_image'] = null;
            }
        }
    }
    unset($row);

    // Build nested tree
    $byId = [];
    foreach ($rows as $r) {
        $r['replies'] = [];
        $byId[$r['id']] = $r;
    }
    $tree = [];
    foreach ($byId as $id => $c) {
        if ($c['parent_id']) {
            if (isset($byId[$c['parent_id']])) {
                $byId[$c['parent_id']]['replies'][] = &$byId[$id];
            }
        } else {
            $tree[] = &$byId[$id];
        }
    }

    sendJSONResponse(['success' => true, 'comments' => $tree]);

} catch (Exception $e) {
    error_log('Erro get-comments: ' . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno'], 500);
}

?>
