<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

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
$comment = sanitizeInput($input['comment']);

// Rating deve ser um inteiro entre 1-5, padrão 3 se não fornecido (campo NOT NULL no banco)
$rating = 3; // valor padrão
if (isset($input['rating']) && $input['rating'] !== '' && $input['rating'] !== null) {
    $ratingValue = (int)$input['rating'];
    if ($ratingValue >= 1 && $ratingValue <= 5) {
        $rating = $ratingValue;
    }
}

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare('INSERT INTO comments (user_id, album_id, rating, comment, status, created_at) VALUES (?, ?, ?, ?, "approved", NOW())');
    $stmt->execute([$_SESSION['user_id'], $album_id, $rating, $comment]);

    sendJSONResponse(['success' => true, 'comment_id' => (int)$pdo->lastInsertId()]);

} catch (Exception $e) {
    error_log('Erro post-comment: ' . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno'], 500);
}

?>
