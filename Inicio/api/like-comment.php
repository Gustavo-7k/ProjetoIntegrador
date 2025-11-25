<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(['success' => false, 'message' => 'Método não permitido'], 405);
}

if (!isLoggedIn()) sendJSONResponse(['success' => false, 'message' => 'Usuário não autenticado'], 401);

$input = json_decode(file_get_contents('php://input'), true);
$comment_id = isset($input['comment_id']) ? (int)$input['comment_id'] : 0;
if ($comment_id <= 0) sendJSONResponse(['success' => false, 'message' => 'Comentário inválido'], 400);


try {
    $pdo = getDBConnection();
    $user = $_SESSION['user_id'];

    $stmt = $pdo->prepare('SELECT id FROM comment_likes WHERE user_id = ? AND comment_id = ?');
    $stmt->execute([$user, $comment_id]);
    $exists = $stmt->fetch();

    if ($exists) {
        $stmt = $pdo->prepare('DELETE FROM comment_likes WHERE id = ?');
        $stmt->execute([$exists['id']]);
        $liked = false;
    } else {
        $stmt = $pdo->prepare('INSERT INTO comment_likes (user_id, comment_id, created_at) VALUES (?, ?, NOW())');
        $stmt->execute([$user, $comment_id]);
        $liked = true;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM comment_likes WHERE comment_id = ?');
    $stmt->execute([$comment_id]);
    $total = (int)$stmt->fetchColumn();

    sendJSONResponse(['success' => true, 'liked' => $liked, 'total' => $total]);

} catch (Exception $e) {
    error_log('Erro like-comment: ' . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno'], 500);
}

?>
