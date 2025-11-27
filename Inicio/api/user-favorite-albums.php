<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    sendJSONResponse(['success' => false, 'message' => 'Não autenticado'], 401);
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getDBConnection();
    
    if ($method === 'GET') {
        // Buscar álbuns favoritos do usuário
        $stmt = $pdo->prepare("
            SELECT ufa.position, a.id, a.title, a.artist, a.cover_image
            FROM user_favorite_albums ufa
            JOIN albums a ON ufa.album_id = a.id
            WHERE ufa.user_id = ?
            ORDER BY ufa.position ASC
            LIMIT 6
        ");
        $stmt->execute([$userId]);
        $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Processar caminhos das capas
        foreach ($favorites as &$album) {
            $cover = $album['cover_image'] ?? '';
            if ($cover) {
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
        
        sendJSONResponse(['success' => true, 'favorites' => $favorites]);
        
    } elseif ($method === 'POST') {
        // Salvar álbuns favoritos
        $input = json_decode(file_get_contents('php://input'), true);
        $albumIds = $input['album_ids'] ?? [];
        
        // Validar - máximo 6 álbuns
        if (count($albumIds) > 6) {
            sendJSONResponse(['success' => false, 'message' => 'Máximo de 6 álbuns permitido'], 400);
        }
        
        // Filtrar IDs válidos
        $albumIds = array_filter($albumIds, function($id) {
            return is_numeric($id) && $id > 0;
        });
        $albumIds = array_values(array_unique($albumIds));
        
        $pdo->beginTransaction();
        
        // Remover favoritos antigos
        $stmt = $pdo->prepare("DELETE FROM user_favorite_albums WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Inserir novos favoritos
        if (!empty($albumIds)) {
            $stmt = $pdo->prepare("
                INSERT INTO user_favorite_albums (user_id, album_id, position) 
                VALUES (?, ?, ?)
            ");
            
            foreach ($albumIds as $position => $albumId) {
                $stmt->execute([$userId, $albumId, $position]);
            }
        }
        
        $pdo->commit();
        
        sendJSONResponse(['success' => true, 'message' => 'Álbuns favoritos atualizados']);
        
    } else {
        sendJSONResponse(['success' => false, 'message' => 'Método não permitido'], 405);
    }
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erro em user-favorite-albums: " . $e->getMessage());
    sendJSONResponse(['success' => false, 'message' => 'Erro interno'], 500);
}
