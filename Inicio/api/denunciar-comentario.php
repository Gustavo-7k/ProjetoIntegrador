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
if (!isset($input['comentario_id'], $input['motivo'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos'], JSON_UNESCAPED_UNICODE);
    exit;
}

$comentario_id = (int)$input['comentario_id'];
$motivo = sanitizeInput($input['motivo']);
$user_id = $_SESSION['user_id'];

// Validar motivo
if (empty(trim($motivo))) {
    echo json_encode(['success' => false, 'message' => 'Motivo da denúncia é obrigatório'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Conectar ao banco de dados
    $pdo = getDBConnection();
    
    // Verificar se o comentário existe
    $stmt = $pdo->prepare("SELECT id FROM comentarios WHERE id = ?");
    $stmt->execute([$comentario_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Comentário não encontrado'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar se o usuário já denunciou este comentário
    $stmt = $pdo->prepare("
        SELECT id FROM denuncias 
        WHERE denunciante_id = ? AND comentario_id = ? AND status = 'pendente'
    ");
    $stmt->execute([$user_id, $comentario_id]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Você já denunciou este comentário'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Inserir denúncia
    $stmt = $pdo->prepare("
        INSERT INTO denuncias (denunciante_id, comentario_id, motivo, status, data_criacao) 
        VALUES (?, ?, ?, 'pendente', NOW())
    ");
    $stmt->execute([$user_id, $comentario_id, $motivo]);
    
    // Criar notificação para administradores
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, related_id, created_at) VALUES (?, 'report', ?, ?, ?, NOW())");
    // Notificar admin (fallback to user_id 1)
    $title = 'Nova denúncia de comentário';
    $message = 'Um comentário foi denunciado. Motivo: ' . $motivo;
    $stmt->execute([1, $title, $message, $comentario_id]);
    $dados = json_encode([
        'denuncia_id' => $pdo->lastInsertId(),
        'comentario_id' => $comentario_id,
        'denunciante_id' => $user_id,
        'motivo' => $motivo
    ], JSON_UNESCAPED_UNICODE);
    $stmt->execute([$dados]);
    
    echo json_encode(['success' => true, 'message' => 'Denúncia enviada com sucesso'], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Erro ao processar denúncia: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor'], JSON_UNESCAPED_UNICODE);
}
?>
