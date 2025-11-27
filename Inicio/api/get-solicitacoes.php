<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    sendJSONResponse(['success' => false, 'message' => 'Não autenticado'], 401);
}

$currentUserId = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();
    
    // Buscar solicitações pendentes recebidas
    $stmt = $pdo->prepare("
        SELECT c.id as connection_id, c.follower_id, c.created_at,
               u.username, u.full_name, u.profile_image
        FROM connections c
        JOIN users u ON u.id = c.follower_id
        WHERE c.following_id = ? AND c.status = 'pending'
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$currentUserId]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Processar imagens de perfil
    foreach ($requests as &$request) {
        if (!empty($request['profile_image'])) {
            $imagePath = __DIR__ . '/../uploads/' . $request['profile_image'];
            if (file_exists($imagePath)) {
                $request['profile_image'] = '/uploads/' . $request['profile_image'];
            } else {
                $request['profile_image'] = null;
            }
        }
    }
    unset($request);
    
    sendJSONResponse([
        'success' => true,
        'requests' => $requests,
        'count' => count($requests)
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar solicitações: " . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno'], 500);
}
