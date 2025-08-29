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
if (!isset($input['album_id']) || !verificarCSRF($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
    exit;
}

$album_id = (int)$input['album_id'];
$user_id = $_SESSION['user_id'];

try {
    // Conectar ao banco de dados
    $pdo = conectarBanco();
    
    // Verificar se o álbum existe
    $stmt = $pdo->prepare("SELECT id FROM albums WHERE id = ?");
    $stmt->execute([$album_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Álbum não encontrado']);
        exit;
    }
    
    // Verificar se já está nos favoritos
    $stmt = $pdo->prepare("
        SELECT id FROM favoritos 
        WHERE usuario_id = ? AND album_id = ?
    ");
    $stmt->execute([$user_id, $album_id]);
    $ja_favorito = $stmt->fetch();
    
    if ($ja_favorito) {
        // Remover dos favoritos
        $stmt = $pdo->prepare("
            DELETE FROM favoritos 
            WHERE usuario_id = ? AND album_id = ?
        ");
        $stmt->execute([$user_id, $album_id]);
        
        $favorited = false;
        $message = 'Álbum removido dos favoritos';
    } else {
        // Adicionar aos favoritos
        $stmt = $pdo->prepare("
            INSERT INTO favoritos (usuario_id, album_id, data_criacao) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$user_id, $album_id]);
        
        $favorited = true;
        $message = 'Álbum adicionado aos favoritos';
    }
    
    echo json_encode([
        'success' => true, 
        'favorited' => $favorited,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao favoritar álbum: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
