<?php
require_once __DIR__ . '/../config.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendJSONResponse(['error' => 'Método não permitido'], 405);
}

$token = $_POST['csrf_token'] ?? '';
if (!validateCSRFToken($token)) {
    sendJSONResponse(['error' => 'Token CSRF inválido'], 403);
}

if (!isset($_FILES['cover'])) {
    sendJSONResponse(['error' => 'Arquivo não enviado'], 400);
}

$errors = validateUpload($_FILES['cover']);
if ($errors) {
    sendJSONResponse(['error' => implode('\n', $errors)], 422);
}

$ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
$filename = 'cover_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
$destPath = UPLOAD_PATH . $filename;

if (!is_dir(UPLOAD_PATH)) {
    @mkdir(UPLOAD_PATH, 0777, true);
}

if (!move_uploaded_file($_FILES['cover']['tmp_name'], $destPath)) {
    sendJSONResponse(['error' => 'Falha ao salvar arquivo'], 500);
}

$pdo = getDBConnection();
$stmt = $pdo->prepare('UPDATE users SET cover_image = ?, updated_at = NOW() WHERE id = ?');
$stmt->execute([$filename, $_SESSION['user_id']]);

sendJSONResponse(['ok' => true, 'path' => 'uploads/' . $filename]);
<?php
