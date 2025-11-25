<?php
require_once '../config.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Método não permitido');
}

// CSRF validation removed

try {
    // Sanitizar e validar dados de entrada
    $username = sanitizeInput($_POST['username'] ?? '');
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $terms_accepted = isset($_POST['terms_accepted']);
    $newsletter = isset($_POST['newsletter']);
    
    $errors = [];
    
    // Validação do nome de usuário
    if (empty($username)) {
        $errors[] = 'Nome de usuário é obrigatório.';
    } elseif (strlen($username) < 3 || strlen($username) > 30) {
        $errors[] = 'Nome de usuário deve ter entre 3 e 30 caracteres.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Nome de usuário deve conter apenas letras, números e underscore.';
    }
    
    // Validação do nome completo
    if (empty($full_name)) {
        $errors[] = 'Nome completo é obrigatório.';
    } elseif (strlen($full_name) < 2 || strlen($full_name) > 100) {
        $errors[] = 'Nome completo deve ter entre 2 e 100 caracteres.';
    }
    
    // Validação do email
    if (empty($email)) {
        $errors[] = 'Email é obrigatório.';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'Email inválido.';
    }
    
    // Validação da senha
    if (empty($password)) {
        $errors[] = 'Senha é obrigatória.';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Senha deve ter pelo menos ' . PASSWORD_MIN_LENGTH . ' caracteres.';
    }
    
    // Validação da confirmação de senha
    if ($password !== $password_confirm) {
        $errors[] = 'Confirmação de senha não confere.';
    }
    
    // Validação dos termos
    if (!$terms_accepted) {
        $errors[] = 'Você deve aceitar os termos de uso.';
    }
    
    if (!empty($errors)) {
        sendJSONResponse(['success' => false, 'message' => implode(' ', $errors)], 400);
    }
    
    $pdo = getDBConnection();
    
    // Verificar se nome de usuário já existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        sendJSONResponse(['success' => false, 'message' => 'Nome de usuário já está em uso.'], 409);
    }
    
    // Verificar se email já existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        sendJSONResponse(['success' => false, 'message' => 'Email já está cadastrado.'], 409);
    }
    
    // Hash da senha
    $password_hash = hashPassword($password);
    
    // Token de verificação de email
    $email_token = bin2hex(random_bytes(32));
    
    // Inserir usuário
    $stmt = $pdo->prepare("
        INSERT INTO users (
            username, full_name, email, password_hash, 
            email_token, newsletter, terms_accepted_at, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $success = $stmt->execute([
        $username, $full_name, $email, $password_hash, 
        $email_token, $newsletter ? 1 : 0
    ]);
    
    if ($success) {
        $user_id = $pdo->lastInsertId();
        
        // Log da atividade
        logActivity("Novo usuário registrado: $username (ID: $user_id)");
        
        // TODO: Enviar email de verificação
        // sendVerificationEmail($email, $email_token);
        
        // Resposta de sucesso
        sendJSONResponse([
            'success' => true,
            'message' => 'Conta criada com sucesso! Verifique seu email para ativar sua conta.',
            'redirect' => 'login.php'
        ]);
        
    } else {
        throw new Exception('Erro ao criar conta.');
    }
    
} catch (Exception $e) {
    error_log("Erro ao registrar usuário: " . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno. Tente novamente.'], 500);
}

// Função para enviar email de verificação (implementar com biblioteca de email)
function sendVerificationEmail($email, $token) {
    // TODO: Implementar envio de email
    // Exemplo com PHPMailer ou similar
    $verification_link = APP_URL . "verify-email.php?token=" . $token;
    
    // Por enquanto, apenas log
    logActivity("Email de verificação deveria ser enviado para: $email");
}
?>
