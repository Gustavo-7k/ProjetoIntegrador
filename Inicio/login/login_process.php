<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido');
}

$email = sanitizeInput(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$remember = !empty($_POST['remember_me']);

// Detectar requisição AJAX (fetch/XmlHttpRequest) ou aceitação de JSON
$isAjax = false;
// Detectar requisição AJAX de forma robusta:
// 1) cabeçalho X-Requested-With
// 2) header Accept contendo application/json
// 3) campo _ajax no corpo (adicionado pelo JS)
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

// Atualizar último login
$pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);

// Opção de lembrar (sessão mais longa)
if ($remember) {
    // Evitar chamado a ini_set() quando a sessão já estiver ativa (gera warning)
    // O suficiente para estender o cookie de sessão é redefinir o cookie com novo prazo
    setcookie(session_name(), session_id(), time() + SESSION_LIFETIME, '/');
}

if ($isAjax) {
    sendJSONResponse(['success' => true, 'message' => 'Login realizado com sucesso.', 'redirect' => APP_URL . 'inicio.php']);
}

redirectTo(APP_URL . 'inicio.php');