<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(['success' => false, 'message' => 'Método não permitido'], 405);
}

if (!isLoggedIn()) sendJSONResponse(['success' => false, 'message' => 'Usuário não autenticado'], 401);

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['comment_id']) || !isset($input['reason'])) {
    sendJSONResponse(['success' => false, 'message' => 'Dados inválidos'], 400);
}


$comment_id = (int)$input['comment_id'];
$reason = sanitizeInput($input['reason']);
$description = isset($input['description']) ? sanitizeInput($input['description']) : null;

try {
    $pdo = getDBConnection();

    // Insert into reports
    $stmt = $pdo->prepare('INSERT INTO reports (reporter_id, comment_id, reason, description, status, created_at) VALUES (?, ?, ?, ?, "pending", NOW())');
    $stmt->execute([$_SESSION['user_id'], $comment_id, $reason, $description]);

    sendJSONResponse(['success' => true, 'report_id' => (int)$pdo->lastInsertId()]);

} catch (Exception $e) {
    error_log('Erro report-comment: ' . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno'], 500);
}

?>
