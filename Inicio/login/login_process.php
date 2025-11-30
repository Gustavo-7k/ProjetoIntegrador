<?php
require_once __DIR__ . '/../config.php';

//verificação post
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido');
}
 //sanitização do email -> ?? retorna vazio
$email = sanitizeInput(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$remember = !empty($_POST['remember_me']);

//verifica tipo de requisição
$isAjax = false;

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $isAjax = true;
} elseif (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    $isAjax = true;
} elseif (!empty($_POST['_ajax']) && $_POST['_ajax'] == '1') {
    $isAjax = true;
}

// Validação básica
if (!isValidEmail($email) || $password === '') {
    if ($isAjax) {
        sendJSONResponse(['success' => false, 'message' => 'Email ou senha inválidos.'], 400);
    }
    redirectTo(APP_URL . 'login/login.php?error=credenciais');
}


//verificações
$pdo = getDBConnection();
$stmt = $pdo->prepare('SELECT id, password_hash, is_admin, active FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !$user['active'] || !verifyPassword($password, $user['password_hash'])) {
    if ($isAjax) {
        sendJSONResponse(['success' => false, 'message' => 'Credenciais inválidas.'], 401);
    }
    redirectTo(APP_URL . 'login/login.php?error=credenciais');
}

// Autenticar
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['is_admin'] = (bool)$user['is_admin'];
$pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);


//lembrar-me
if ($remember) {
    setcookie(session_name(), session_id(), time() + SESSION_LIFETIME, '/');
}

if ($isAjax) {
    sendJSONResponse(['success' => true, 'message' => 'Login realizado com sucesso.', 'redirect' => APP_URL . 'inicio.php']);
}

redirectTo(APP_URL . 'inicio.php');