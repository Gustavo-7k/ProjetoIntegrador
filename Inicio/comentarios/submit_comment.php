<?php
require_once '../config.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Método não permitido');
}

// Verificar se usuário está logado
if (!isLoggedIn()) {
    sendJSONResponse(['success' => false, 'message' => 'Você precisa estar logado para comentar.'], 401);
}

// CSRF validation removed

try {
    // Validar dados de entrada
    $album_id = filter_var($_POST['album_id'], FILTER_VALIDATE_INT);
    $rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT);
    $comment = trim($_POST['comment']);
    
    $errors = [];
    
    if (!$album_id || $album_id < 1) {
        $errors[] = 'ID do álbum inválido.';
    }
    
    if (!$rating || $rating < 1 || $rating > 5) {
        $errors[] = 'Avaliação deve ser entre 1 e 5 estrelas.';
    }
    
    if (empty($comment)) {
        $errors[] = 'Comentário é obrigatório.';
    } elseif (strlen($comment) < 10) {
        $errors[] = 'Comentário deve ter pelo menos 10 caracteres.';
    } elseif (strlen($comment) > 1000) {
        $errors[] = 'Comentário deve ter no máximo 1000 caracteres.';
    }
    
    if (!empty($errors)) {
        sendJSONResponse(['success' => false, 'message' => implode(' ', $errors)], 400);
    }
    
    $pdo = getDBConnection();
    
    // Verificar se o álbum existe
    $stmt = $pdo->prepare("SELECT id FROM albums WHERE id = ?");
    $stmt->execute([$album_id]);
    if (!$stmt->fetch()) {
        sendJSONResponse(['success' => false, 'message' => 'Álbum não encontrado.'], 404);
    }
    
    // Verificar se usuário já comentou neste álbum
    $stmt = $pdo->prepare("SELECT id FROM comments WHERE user_id = ? AND album_id = ?");
    $stmt->execute([$_SESSION['user_id'], $album_id]);
    if ($stmt->fetch()) {
        sendJSONResponse(['success' => false, 'message' => 'Você já comentou neste álbum.'], 409);
    }
    
    // Inserir comentário
    $stmt = $pdo->prepare("
        INSERT INTO comments (user_id, album_id, rating, comment, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $success = $stmt->execute([$_SESSION['user_id'], $album_id, $rating, $comment]);
    
    if ($success) {
        $comment_id = $pdo->lastInsertId();
        
        // Log da atividade
        logActivity("Usuário {$_SESSION['user_id']} comentou no álbum $album_id");
        
        // Resposta de sucesso
        sendJSONResponse([
            'success' => true,
            'message' => 'Comentário publicado com sucesso!',
            'comment_id' => $comment_id,
            'redirect' => "Vercomentario.php?id=$comment_id"
        ]);
    } else {
        throw new Exception('Erro ao salvar comentário.');
    }
    
} catch (Exception $e) {
    error_log("Erro ao processar comentário: " . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno. Tente novamente.'], 500);
}
?>
