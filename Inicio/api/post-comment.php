<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(['success' => false, 'message' => 'Método não permitido'], 405);
}

if (!isLoggedIn()) {
    sendJSONResponse(['success' => false, 'message' => 'Usuário não autenticado'], 401);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['album_id']) || !isset($input['comment'])) {
    sendJSONResponse(['success' => false, 'message' => 'Dados inválidos'], 400);
}

$album_id = (int)$input['album_id'];
$comment = trim($input['comment']);
$parent_id = null;
if (isset($input['parent_id']) && $input['parent_id'] !== null && $input['parent_id'] !== '' && (int)$input['parent_id'] > 0) {
    $parent_id = (int)$input['parent_id'];
}

// Rating deve ser um inteiro entre 1-5, ou 0 para respostas
$rating = 3; // valor padrão
if ($parent_id) {
    // Resposta - não tem rating
    $rating = 0;
} else {
    // Comentário principal
    if (isset($input['rating']) && $input['rating'] !== '' && $input['rating'] !== null) {
        $ratingValue = (int)$input['rating'];
        if ($ratingValue >= 1 && $ratingValue <= 5) {
            $rating = $ratingValue;
        }
    }
}

try {
    $pdo = getDBConnection();
    
    // Verificar se a coluna parent_id existe
    $hasParentId = false;
    try {
        $checkStmt = $pdo->query("SHOW COLUMNS FROM comments LIKE 'parent_id'");
        $hasParentId = $checkStmt->rowCount() > 0;
    } catch (Exception $e) {
        $hasParentId = false;
    }
    
    // Se não tem parent_id no banco, ignorar respostas
    if (!$hasParentId) {
        $parent_id = null;
    }
    
    $parentComment = null;
    
    // Se é uma resposta e o banco suporta
    if ($parent_id && $hasParentId) {
        $stmt = $pdo->prepare('SELECT c.user_id, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id = ?');
        $stmt->execute([$parent_id]);
        $parentComment = $stmt->fetch();
        
        if (!$parentComment) {
            sendJSONResponse(['success' => false, 'message' => 'Comentário pai não encontrado'], 404);
        }
        
        // Adicionar menção ao usuário no início do comentário se não começar com @
        if (strpos($comment, '@') !== 0) {
            $comment = '@' . $parentComment['username'] . ' ' . $comment;
        }
        
        // Resposta - inserir com rating 1 (mínimo válido)
        $stmt = $pdo->prepare('INSERT INTO comments (user_id, album_id, parent_id, rating, comment, status, created_at) VALUES (?, ?, ?, 1, ?, "approved", NOW())');
        $stmt->execute([$_SESSION['user_id'], $album_id, $parent_id, $comment]);
    } else {
        // Comentário principal - inserir sem parent_id
        $stmt = $pdo->prepare('INSERT INTO comments (user_id, album_id, rating, comment, status, created_at) VALUES (?, ?, ?, ?, "approved", NOW())');
        $stmt->execute([$_SESSION['user_id'], $album_id, $rating, $comment]);
    }
    
    $commentId = (int)$pdo->lastInsertId();
    
    // Se é uma resposta, criar notificação para o autor do comentário pai
    if ($parent_id && $hasParentId && $parentComment && $parentComment['user_id'] != $_SESSION['user_id']) {
        $currentUser = getCurrentUser();
        $notifMessage = $currentUser['username'] . ' respondeu seu comentário';
        
        $stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, title, message, related_id, created_at) VALUES (?, "comment", "Nova resposta", ?, ?, NOW())');
        $stmt->execute([$parentComment['user_id'], $notifMessage, $commentId]);
    }

    sendJSONResponse(['success' => true, 'comment_id' => $commentId]);

} catch (Exception $e) {
    error_log('Erro post-comment: ' . $e->getMessage() . ' - Line: ' . $e->getLine());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()], 500);
}

?>
