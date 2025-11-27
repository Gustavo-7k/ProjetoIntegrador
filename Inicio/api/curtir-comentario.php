<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Obter dados JSON
$input = json_decode(file_get_contents('php://input'), true);

// Verificar dados necessários
if (!isset($input['comentario_id'])) {
    sendJSONResponse(['success' => false, 'message' => 'ID de comentário inválido'], 400);
}

$comentario_id = (int)$input['comentario_id'];
$user_id = $_SESSION['user_id'];

try {
    // Conectar ao banco de dados
    $pdo = getDBConnection();
    
    // Verificar se o comentário existe
    $stmt = $pdo->prepare("SELECT id FROM comments WHERE id = ?");
    $stmt->execute([$comentario_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Comentário não encontrado'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar se já curtiu
    $stmt = $pdo->prepare('SELECT id FROM comment_likes WHERE user_id = ? AND comment_id = ?');
    $stmt->execute([$user_id, $comentario_id]);
    $ja_curtiu = $stmt->fetch();
    
    if ($ja_curtiu) {
        // Remover curtida
        $stmt = $pdo->prepare('DELETE FROM comment_likes WHERE id = ?');
        $stmt->execute([$ja_curtiu['id']]);
    } else {
        // Adicionar curtida
        $stmt = $pdo->prepare('INSERT INTO comment_likes (user_id, comment_id, created_at) VALUES (?, ?, NOW())');
        $stmt->execute([$user_id, $comentario_id]);
    }
    
    // Obter total de curtidas
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM comment_likes WHERE comment_id = ?');
    $stmt->execute([$comentario_id]);
    $total_curtidas = $stmt->fetchColumn();
    
    sendJSONResponse(['success' => true, 'curtidas' => $total_curtidas, 'curtiu' => !(bool)$ja_curtiu]);
    
} catch (Exception $e) {
    error_log("Erro ao curtir comentário: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor'], JSON_UNESCAPED_UNICODE);
}
?>
