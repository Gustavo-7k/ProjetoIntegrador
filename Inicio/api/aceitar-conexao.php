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
if (!isset($input['notificacao_id']) || !verificarCSRF($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
    exit;
}

$notificacao_id = (int)$input['notificacao_id'];
$user_id = $_SESSION['user_id'];

try {
    // Conectar ao banco de dados
    $pdo = conectarBanco();
    
    // Verificar se a notificação existe e é uma solicitação de conexão
    $stmt = $pdo->prepare("
        SELECT * FROM notificacoes 
        WHERE id = ? AND receptor_id = ? AND tipo = 'solicitacao_conexao' AND status = 'pendente'
    ");
    $stmt->execute([$notificacao_id, $user_id]);
    $notificacao = $stmt->fetch();
    
    if (!$notificacao) {
        echo json_encode(['success' => false, 'message' => 'Notificação não encontrada']);
        exit;
    }
    
    $solicitante_id = $notificacao['remetente_id'];
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    // Criar conexão entre os usuários
    $stmt = $pdo->prepare("
        INSERT INTO conexoes (usuario1_id, usuario2_id, status, data_criacao) 
        VALUES (?, ?, 'aceita', NOW())
    ");
    $stmt->execute([$solicitante_id, $user_id]);
    
    // Atualizar status da notificação
    $stmt = $pdo->prepare("
        UPDATE notificacoes 
        SET status = 'aceita', data_processamento = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$notificacao_id]);
    
    // Criar notificação de aceite para o solicitante
    $stmt = $pdo->prepare("
        INSERT INTO notificacoes (remetente_id, receptor_id, tipo, mensagem, data_criacao) 
        VALUES (?, ?, 'conexao_aceita', 'Sua solicitação de conexão foi aceita', NOW())
    ");
    $stmt->execute([$user_id, $solicitante_id]);
    
    // Confirmar transação
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Conexão aceita com sucesso']);
    
} catch (Exception $e) {
    // Reverter transação em caso de erro
    if (isset($pdo)) {
        $pdo->rollback();
    }
    
    error_log("Erro ao aceitar conexão: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
