<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(['success' => false, 'message' => 'Método não permitido'], 405);
}

if (!isLoggedIn()) {
    sendJSONResponse(['success' => false, 'message' => 'Não autenticado'], 401);
}

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');

if (empty($username)) {
    sendJSONResponse(['success' => false, 'message' => 'Nome de usuário é obrigatório']);
}

$currentUserId = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();
    
    // Buscar usuário pelo username
    $stmt = $pdo->prepare("SELECT id, username, full_name FROM users WHERE username = ? AND active = TRUE");
    $stmt->execute([$username]);
    $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$targetUser) {
        sendJSONResponse(['success' => false, 'message' => 'Usuário não encontrado']);
    }
    
    if ($targetUser['id'] == $currentUserId) {
        sendJSONResponse(['success' => false, 'message' => 'Você não pode adicionar a si mesmo']);
    }
    
    // Verificar se já existe uma conexão
    $stmt = $pdo->prepare("
        SELECT * FROM connections 
        WHERE (follower_id = ? AND following_id = ?) 
           OR (follower_id = ? AND following_id = ?)
    ");
    $stmt->execute([$currentUserId, $targetUser['id'], $targetUser['id'], $currentUserId]);
    $existingConnection = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingConnection) {
        if ($existingConnection['status'] === 'pending') {
            sendJSONResponse(['success' => false, 'message' => 'Já existe uma solicitação pendente']);
        } elseif ($existingConnection['status'] === 'accepted') {
            sendJSONResponse(['success' => false, 'message' => 'Vocês já são conexões']);
        } elseif ($existingConnection['status'] === 'blocked') {
            sendJSONResponse(['success' => false, 'message' => 'Não foi possível enviar a solicitação']);
        }
    }
    
    // Criar solicitação de conexão
    $stmt = $pdo->prepare("
        INSERT INTO connections (follower_id, following_id, status, created_at) 
        VALUES (?, ?, 'pending', NOW())
    ");
    $stmt->execute([$currentUserId, $targetUser['id']]);
    
    // Buscar nome do usuário atual
    $stmt = $pdo->prepare("SELECT full_name, username FROM users WHERE id = ?");
    $stmt->execute([$currentUserId]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Criar notificação para o usuário alvo
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message, related_id, created_at) 
        VALUES (?, 'follow', 'Nova solicitação de conexão', ?, ?, NOW())
    ");
    $message = ($currentUser['full_name'] ?: $currentUser['username']) . ' quer se conectar com você';
    $stmt->execute([$targetUser['id'], $message, $currentUserId]);
    
    sendJSONResponse([
        'success' => true, 
        'message' => 'Solicitação enviada para ' . ($targetUser['full_name'] ?: $targetUser['username'])
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao enviar solicitação: " . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno'], 500);
}
