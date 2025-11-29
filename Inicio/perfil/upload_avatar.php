<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(['ok' => false, 'error' => 'Método não permitido'], 405);
}

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
    sendJSONResponse(['ok' => false, 'error' => 'Arquivo não enviado'], 400);
}

$file = $_FILES['avatar'];

// Verificar erro de upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE => 'Arquivo excede o tamanho máximo do servidor',
        UPLOAD_ERR_FORM_SIZE => 'Arquivo excede o tamanho máximo do formulário',
        UPLOAD_ERR_PARTIAL => 'Upload incompleto',
        UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada',
        UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever arquivo',
        UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
    ];
    $errorMsg = $uploadErrors[$file['error']] ?? 'Erro desconhecido no upload';
    sendJSONResponse(['ok' => false, 'error' => $errorMsg], 400);
}

// Verificar tamanho (5MB max para avatar)
$maxSize = 5 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    sendJSONResponse(['ok' => false, 'error' => 'Arquivo muito grande. Máximo: 5MB'], 422);
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
    sendJSONResponse(['ok' => false, 'error' => 'Tipo de arquivo não permitido. Use: JPG, PNG, GIF ou WebP'], 422);
}

$ext = $mimeToExt[$mimeType];
$filename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
$uploadDir = __DIR__ . '/../uploads/';
$destPath = $uploadDir . $filename;

// Criar diretório se não existir
if (!is_dir($uploadDir)) {
    if (!@mkdir($uploadDir, 0755, true)) {
        sendJSONResponse(['ok' => false, 'error' => 'Não foi possível criar diretório de uploads'], 500);
    }
}

// Verificar se o diretório é gravável
if (!is_writable($uploadDir)) {
    sendJSONResponse(['ok' => false, 'error' => 'Diretório de uploads sem permissão de escrita'], 500);
}

// Mover arquivo
if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    sendJSONResponse(['ok' => false, 'error' => 'Falha ao salvar arquivo'], 500);
}

// Remover avatar antigo se existir
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('SELECT profile_image FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $oldAvatar = $stmt->fetchColumn();
    
    if ($oldAvatar && file_exists($uploadDir . $oldAvatar)) {
        @unlink($uploadDir . $oldAvatar);
    }
    
    // Atualizar banco com novo arquivo
    $stmt = $pdo->prepare('UPDATE users SET profile_image = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$filename, $_SESSION['user_id']]);
    
} catch (Exception $e) {
    error_log('Erro ao atualizar profile_image: ' . $e->getMessage());
    @unlink($destPath);
    sendJSONResponse(['ok' => false, 'error' => 'Erro ao salvar no banco de dados'], 500);
}

sendJSONResponse(['ok' => true, 'path' => 'uploads/' . $filename]);
