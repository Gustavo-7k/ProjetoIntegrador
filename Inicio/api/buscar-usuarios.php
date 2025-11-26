<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    sendJSONResponse(['success' => false, 'message' => 'Não autenticado'], 401);
}

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    sendJSONResponse(['success' => true, 'users' => []]);
}

$currentUserId = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();
    
    // Buscar usuários pelo username ou nome
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.full_name, u.profile_image,
               (SELECT status FROM connections 
                WHERE (follower_id = ? AND following_id = u.id) 
                   OR (follower_id = u.id AND following_id = ?)
                LIMIT 1
               ) as connection_status
        FROM users u
        WHERE u.active = TRUE 
          AND u.id != ?
          AND (u.username LIKE ? OR u.full_name LIKE ?)
        ORDER BY u.full_name
        LIMIT 10
    ");
    $searchTerm = '%' . $query . '%';
    $stmt->execute([$currentUserId, $currentUserId, $currentUserId, $searchTerm, $searchTerm]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendJSONResponse([
        'success' => true,
        'users' => $users
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar usuários: " . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno'], 500);
}
