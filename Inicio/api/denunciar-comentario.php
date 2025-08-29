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

// Verificar CSRF token e dados necessários
if (!isset($input['comentario_id'], $input['motivo']) || !verificarCSRF($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos ou token CSRF inválido']);
    exit;
}

$comentario_id = (int)$input['comentario_id'];
$motivo = sanitizeInput($input['motivo']);
$user_id = $_SESSION['user_id'];

// Validar motivo
if (empty(trim($motivo))) {
    echo json_encode(['success' => false, 'message' => 'Motivo da denúncia é obrigatório']);
    exit;
}

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
    
    // Verificar se o usuário já denunciou este comentário
    $stmt = $pdo->prepare("
        SELECT id FROM denuncias 
        WHERE denunciante_id = ? AND comentario_id = ? AND status = 'pendente'
    ");
    $stmt->execute([$user_id, $comentario_id]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Você já denunciou este comentário']);
        exit;
    }
    
    // Inserir denúncia
    $stmt = $pdo->prepare("
        INSERT INTO denuncias (denunciante_id, comentario_id, motivo, status, data_criacao) 
        VALUES (?, ?, ?, 'pendente', NOW())
    ");
    $stmt->execute([$user_id, $comentario_id, $motivo]);
    
    // Criar notificação para administradores
    $stmt = $pdo->prepare("
        INSERT INTO notificacoes_admin (tipo, dados, data_criacao) 
        VALUES ('nova_denuncia', ?, NOW())
    ");
    $dados = json_encode([
        'denuncia_id' => $pdo->lastInsertId(),
        'comentario_id' => $comentario_id,
        'denunciante_id' => $user_id,
        'motivo' => $motivo
    ]);
    $stmt->execute([$dados]);
    
    echo json_encode(['success' => true, 'message' => 'Denúncia enviada com sucesso']);
    
} catch (Exception $e) {
    error_log("Erro ao processar denúncia: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
