<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    sendJSONResponse(['success' => false, 'message' => 'Não autenticado'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(['success' => false, 'message' => 'Método não permitido'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$connectionId = (int)($data['connection_id'] ?? 0);

if ($connectionId <= 0) {
    sendJSONResponse(['success' => false, 'message' => 'ID de conexão inválido']);
}

$currentUserId = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();
    
    // Verificar se a conexão existe e é para o usuário atual
    $stmt = $pdo->prepare("
        SELECT * FROM connections 
        WHERE id = ? AND following_id = ? AND status = 'pending'
    ");
    $stmt->execute([$connectionId, $currentUserId]);
    $connection = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$connection) {
        sendJSONResponse(['success' => false, 'message' => 'Solicitação não encontrada']);
    }
    
    // Deletar a solicitação
    $stmt = $pdo->prepare("DELETE FROM connections WHERE id = ?");
    $stmt->execute([$connectionId]);
    
    // Marcar notificação como lida
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = TRUE 
        WHERE user_id = ? AND type = 'follow' AND related_id = ?
    ");
    $stmt->execute([$currentUserId, $connection['follower_id']]);
    
    sendJSONResponse(['success' => true, 'message' => 'Solicitação recusada']);
    
} catch (PDOException $e) {
    error_log("Erro ao recusar conexão: " . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno'], 500);
}
