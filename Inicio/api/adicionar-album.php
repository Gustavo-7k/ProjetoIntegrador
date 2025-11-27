<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validar campos obrigatórios
$title = trim($_POST['title'] ?? '');
$artist = trim($_POST['artist'] ?? '');
$genre = trim($_POST['genre'] ?? '');
$release_year = trim($_POST['release_year'] ?? '');

if (empty($title)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Título do álbum é obrigatório'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($artist)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Nome do artista é obrigatório'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validar ano
if (!empty($release_year)) {
    $release_year = intval($release_year);
    if ($release_year < 1900 || $release_year > date('Y') + 1) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Ano de lançamento inválido'], JSON_UNESCAPED_UNICODE);
        exit;
    }
} else {
    $release_year = null;
}

// Processar upload da capa
$cover_image = null;
if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['cover'];
    
    // Validar tipo de arquivo
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    
    if (!in_array($mime_type, $allowed_types)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WebP'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Validar tamanho (máximo 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 5MB'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Gerar nome único para o arquivo
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (empty($extension)) {
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        $extension = $extensions[$mime_type] ?? 'jpg';
    }
    
    $filename = 'album_' . uniqid() . '_' . time() . '.' . $extension;
    $upload_dir = __DIR__ . '/../img/albums/';
    
    // Criar diretório se não existir
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $upload_path = $upload_dir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar imagem'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $cover_image = $filename;
}

try {
    $pdo = getDBConnection();
    
    // Verificar se álbum já existe (mesmo título e artista)
    $stmt = $pdo->prepare("SELECT id FROM albums WHERE title = ? AND artist = ?");
    $stmt->execute([$title, $artist]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Este álbum já existe no sistema'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Inserir álbum
    $stmt = $pdo->prepare("
        INSERT INTO albums (title, artist, cover_image, genre, release_year, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$title, $artist, $cover_image, $genre ?: null, $release_year]);
    
    $album_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Álbum adicionado com sucesso!',
        'album' => [
            'id' => $album_id,
            'title' => $title,
            'artist' => $artist,
            'cover_image' => $cover_image,
            'genre' => $genre,
            'release_year' => $release_year
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log("Erro ao adicionar álbum: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno ao salvar álbum'], JSON_UNESCAPED_UNICODE);
}
