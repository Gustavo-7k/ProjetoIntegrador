<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obter dados JSON
$input = json_decode(file_get_contents('php://input'), true);

// Verificar CSRF token
if (!isset($input['comentario_id']) || !verificarCSRF($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
    exit;
}

$comentario_id = (int)$input['comentario_id'];
$user_id = $_SESSION['user_id'];

try {
    // Conectar ao banco de dados
    $pdo = conectarBanco();
    
    // Verificar se o comentário existe
    $stmt = $pdo->prepare("SELECT id FROM comentarios WHERE id = ?");
    $stmt->execute([$comentario_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Comentário não encontrado']);
        exit;
    }
    
    // Verificar se já curtiu
    $stmt = $pdo->prepare("
        SELECT id FROM curtidas 
        WHERE usuario_id = ? AND comentario_id = ?
    ");
    $stmt->execute([$user_id, $comentario_id]);
    $ja_curtiu = $stmt->fetch();
    
    if ($ja_curtiu) {
        // Remover curtida
        $stmt = $pdo->prepare("
            DELETE FROM curtidas 
            WHERE usuario_id = ? AND comentario_id = ?
        ");
        $stmt->execute([$user_id, $comentario_id]);
    } else {
        // Adicionar curtida
        $stmt = $pdo->prepare("
            INSERT INTO curtidas (usuario_id, comentario_id, data_criacao) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$user_id, $comentario_id]);
    }
    
    // Obter total de curtidas
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM curtidas WHERE comentario_id = ?
    ");
    $stmt->execute([$comentario_id]);
    $total_curtidas = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true, 
        'curtidas' => $total_curtidas,
        'curtiu' => !$ja_curtiu
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao curtir comentário: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
