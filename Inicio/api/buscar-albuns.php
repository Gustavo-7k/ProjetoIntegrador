<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado'], JSON_UNESCAPED_UNICODE);
    exit;
}

$query = trim($_GET['q'] ?? '');
$limit = intval($_GET['limit'] ?? 50);
$offset = intval($_GET['offset'] ?? 0);

// Limitar para evitar sobrecarga
$limit = min($limit, 100);

try {
    $pdo = getDBConnection();
    
    if (!empty($query)) {
        // Busca com filtro
        $stmt = $pdo->prepare("
            SELECT id, title, artist, cover_image, genre, release_year, average_rating
            FROM albums 
            WHERE title LIKE ? OR artist LIKE ?
            ORDER BY title ASC
            LIMIT ? OFFSET ?
        ");
        $searchTerm = '%' . $query . '%';
        $stmt->execute([$searchTerm, $searchTerm, $limit, $offset]);
        
        // Contar total
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) FROM albums 
            WHERE title LIKE ? OR artist LIKE ?
        ");
        $countStmt->execute([$searchTerm, $searchTerm]);
    } else {
        // Buscar todos
        $stmt = $pdo->prepare("
            SELECT id, title, artist, cover_image, genre, release_year, average_rating
            FROM albums 
            ORDER BY title ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        
        // Contar total
        $countStmt = $pdo->query("SELECT COUNT(*) FROM albums");
    }
    
    $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total = $countStmt->fetchColumn();
    
    // Processar caminhos das capas
    foreach ($albums as &$album) {
        $cover = $album['cover_image'] ?? '';
        if ($cover) {
            // Verificar se é álbum novo ou antigo
            if (file_exists(__DIR__ . '/../img/albums/' . $cover)) {
                $album['cover_url'] = '/img/albums/' . $cover;
            } else {
                $album['cover_url'] = '/img/' . $cover;
            }
        } else {
            $album['cover_url'] = null;
        }
    }
    unset($album);
    
    echo json_encode([
        'success' => true,
        'albums' => $albums,
        'total' => intval($total),
        'limit' => $limit,
        'offset' => $offset
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar álbuns: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno'], JSON_UNESCAPED_UNICODE);
}
