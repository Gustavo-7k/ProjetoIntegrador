<?php
require_once __DIR__ . '/../config.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Método não permitido';
    exit;
}

// CSRF validation removed (intentionally)

$name = trim($_POST['name'] ?? '');
$bio = trim($_POST['bio'] ?? '');

if ($name === '') {
    http_response_code(422);
    echo 'Nome é obrigatório';
    exit;
}

$pdo = getDBConnection();
$stmt = $pdo->prepare('UPDATE users SET full_name = ?, bio = ?, updated_at = NOW() WHERE id = ?');
$stmt->execute([$name, $bio, $_SESSION['user_id']]);

redirectTo('../perfil/perfil.php');
