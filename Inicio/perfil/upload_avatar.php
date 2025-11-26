<?php
require_once __DIR__ . '/../config.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendJSONResponse(['error' => 'Método não permitido'], 405);
}

if (!isset($_FILES['avatar'])) {
    sendJSONResponse(['error' => 'Arquivo não enviado'], 400);
}

$file = $_FILES['avatar'];

// Verificar erro de upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    sendJSONResponse(['error' => 'Erro no upload: ' . $file['error']], 400);
}

// Verificar tamanho
if ($file['size'] > UPLOAD_MAX_SIZE) {
    sendJSONResponse(['error' => 'Arquivo muito grande'], 422);
}

// Detectar tipo MIME real do arquivo
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

// Mapear MIME type para extensão
$mimeToExt = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp'
];

if (!isset($mimeToExt[$mimeType])) {
    sendJSONResponse(['error' => 'Tipo de arquivo não permitido: ' . $mimeType], 422);
}

$ext = $mimeToExt[$mimeType];
$filename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
$destPath = UPLOAD_PATH . $filename;

if (!is_dir(UPLOAD_PATH)) {
    @mkdir(UPLOAD_PATH, 0777, true);
}

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    sendJSONResponse(['error' => 'Falha ao salvar arquivo'], 500);
}

$pdo = getDBConnection();
$stmt = $pdo->prepare('UPDATE users SET profile_image = ?, updated_at = NOW() WHERE id = ?');
$stmt->execute([$filename, $_SESSION['user_id']]);

sendJSONResponse(['ok' => true, 'path' => 'uploads/' . $filename]);
