<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

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
        SELECT c.*, u.full_name, u.username 
        FROM connections c
        JOIN users u ON u.id = c.follower_id
        WHERE c.id = ? AND c.following_id = ? AND c.status = 'pending'
    ");
    $stmt->execute([$connectionId, $currentUserId]);
    $connection = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$connection) {
        sendJSONResponse(['success' => false, 'message' => 'Solicitação não encontrada']);
    }
    
    $pdo->beginTransaction();
    
    // Aceitar a conexão
    $stmt = $pdo->prepare("UPDATE connections SET status = 'accepted' WHERE id = ?");
    $stmt->execute([$connectionId]);
    
    // Buscar nome do usuário atual
    $stmt = $pdo->prepare("SELECT full_name, username FROM users WHERE id = ?");
    $stmt->execute([$currentUserId]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Criar notificação para quem enviou a solicitação
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message, related_id, created_at) 
        VALUES (?, 'follow', 'Conexão aceita!', ?, ?, NOW())
    ");
    $message = ($currentUser['full_name'] ?: $currentUser['username']) . ' aceitou sua solicitação de conexão';
    $stmt->execute([$connection['follower_id'], $message, $currentUserId]);
    
    // Marcar notificação original como lida
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = TRUE 
        WHERE user_id = ? AND type = 'follow' AND related_id = ?
    ");
    $stmt->execute([$currentUserId, $connection['follower_id']]);
    
    $pdo->commit();
    
    sendJSONResponse([
        'success' => true, 
        'message' => 'Você e ' . ($connection['full_name'] ?: $connection['username']) . ' agora são conexões!'
    ]);
    
} catch (PDOException $e) {
    if (isset($pdo)) $pdo->rollback();
    error_log("Erro ao aceitar conexão: " . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno'], 500);
}
