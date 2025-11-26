<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    sendJSONResponse(['success' => false, 'message' => 'Não autenticado'], 401);
}

$currentUserId = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();
    
    // Buscar notificações não lidas
    $stmt = $pdo->prepare("
        SELECT n.*, 
               CASE 
                   WHEN n.type = 'follow' THEN (
                       SELECT c.id FROM connections c 
                       WHERE c.follower_id = n.related_id 
                       AND c.following_id = ? 
                       AND c.status = 'pending'
                       LIMIT 1
                   )
                   ELSE NULL
               END as connection_id
        FROM notifications n
        WHERE n.user_id = ? AND n.is_read = FALSE
        ORDER BY n.created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$currentUserId, $currentUserId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contar solicitações pendentes
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM connections 
        WHERE following_id = ? AND status = 'pending'
    ");
    $stmt->execute([$currentUserId]);
    $pendingCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    sendJSONResponse([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => count($notifications),
        'pending_requests' => (int)$pendingCount
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar notificações: " . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno'], 500);
}
