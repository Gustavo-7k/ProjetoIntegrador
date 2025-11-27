<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || !isAdmin()) {
    sendJSONResponse(['success' => false, 'message' => 'Acesso negado'], 403);
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) sendJSONResponse(['success' => false, 'message' => 'ID inválido'], 400);

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('SELECT r.*, u.username as reporter_username, c.comment as commented_text, c.id as commented_id, cu.username as comment_author FROM reports r JOIN users u ON r.reporter_id = u.id JOIN comments c ON r.comment_id = c.id JOIN users cu ON c.user_id = cu.id WHERE r.id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) sendJSONResponse(['success' => false, 'message' => 'Denúncia não encontrada'], 404);

    // Build response
    $denuncia = [
        'id' => (int)$row['id'],
        'reporter' => $row['reporter_username'],
        'reason' => $row['reason'],
        'description' => $row['description'],
        'status' => $row['status'],
        'created_at' => $row['created_at'],
        'comment' => [
            'id' => (int)$row['commented_id'],
            'author' => $row['comment_author'],
            'text' => $row['commented_text']
        ]
    ];

    sendJSONResponse(['success' => true, 'denuncia' => $denuncia]);

} catch (Exception $e) {
    error_log('Erro denuncia-detalhes: ' . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno'], 500);
}

?>
