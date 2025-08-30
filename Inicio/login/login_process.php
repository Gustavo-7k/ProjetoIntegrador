<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = !empty($_POST['remember_me']);

if (!isValidEmail($email) || $password === '') {
    redirectTo(APP_URL . 'login/login.php?error=credenciais');
}

$pdo = getDBConnection();
$stmt = $pdo->prepare('SELECT id, password_hash, is_admin, active FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !$user['active'] || !verifyPassword($password, $user['password_hash'])) {
    redirectTo(APP_URL . 'login/login.php?error=credenciais');
}

// Autenticar
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['is_admin'] = (bool)$user['is_admin'];

// Atualizar último login
$pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);

// Opção de lembrar (sessão mais longa)
if ($remember) {
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    setcookie(session_name(), session_id(), time() + SESSION_LIFETIME, '/');
}

redirectTo(APP_URL . 'inicio.php');
?>
