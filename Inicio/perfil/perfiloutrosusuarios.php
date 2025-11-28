<?php
require_once __DIR__ . '/../config.php';
requireAuth();

$usuarioLogado = getCurrentUser();
$pdo = getDBConnection();

// Obter username do usuário a ser visualizado
$username = $_GET['username'] ?? null;
$userId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Buscar usuário pelo username ou id
if ($username) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND active = TRUE");
    $stmt->execute([$username]);
} elseif ($userId) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND active = TRUE");
    $stmt->execute([$userId]);
} else {
    header('Location: perfil.php');
    exit;
}

$perfilUsuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não encontrou o usuário ou é o próprio usuário, redireciona
if (!$perfilUsuario) {
    header('Location: ../inicio.php');
    exit;
}

// Se é o próprio usuário, redireciona para o perfil próprio
if ($perfilUsuario['id'] == $usuarioLogado['id']) {
    header('Location: perfil.php');
    exit;
}

// Contar conexões do usuário
$stmtConn = $pdo->prepare("
    SELECT COUNT(*) as count FROM connections 
    WHERE (follower_id = ? OR following_id = ?) AND status = 'accepted'
");
$stmtConn->execute([$perfilUsuario['id'], $perfilUsuario['id']]);
$connectionsCount = $stmtConn->fetch(PDO::FETCH_ASSOC)['count'];

// Verificar se o usuário logado já segue/tem conexão com este usuário
$stmtStatus = $pdo->prepare("
    SELECT status FROM connections 
    WHERE (follower_id = ? AND following_id = ?) OR (follower_id = ? AND following_id = ?)
    LIMIT 1
");
$stmtStatus->execute([$usuarioLogado['id'], $perfilUsuario['id'], $perfilUsuario['id'], $usuarioLogado['id']]);
$connectionStatus = $stmtStatus->fetch(PDO::FETCH_ASSOC);
$isConnected = $connectionStatus && $connectionStatus['status'] === 'accepted';
$isPending = $connectionStatus && $connectionStatus['status'] === 'pending';

// Buscar álbuns favoritos do usuário
$liked_albums = [];
$stmtAlbums = $pdo->prepare("
    SELECT a.id, a.title, a.artist, a.cover_image
    FROM user_favorite_albums ufa
    JOIN albums a ON ufa.album_id = a.id
    WHERE ufa.user_id = ?
    ORDER BY ufa.position ASC
    LIMIT 6
");
$stmtAlbums->execute([$perfilUsuario['id']]);
$dbAlbums = $stmtAlbums->fetchAll(PDO::FETCH_ASSOC);

foreach ($dbAlbums as $album) {
    $cover = $album['cover_image'] ?? 'NTHMS.png';
    if (file_exists(__DIR__ . '/../img/albums/' . $cover)) {
        $imagePath = 'albums/' . $cover;
    } else {
        $imagePath = $cover;
    }
    $liked_albums[] = [
        'id' => $album['id'],
        'title' => $album['title'],
        'artist' => $album['artist'],
        'image' => $imagePath
    ];
}

// Verificar imagens do perfil
$profile_image_path = '';
$cover_image_path = '';

if (!empty($perfilUsuario['profile_image'])) {
    $fullPath = __DIR__ . '/../uploads/' . $perfilUsuario['profile_image'];
    if (file_exists($fullPath)) {
        $profile_image_path = '../uploads/' . $perfilUsuario['profile_image'];
    }
}

if (!empty($perfilUsuario['cover_image'])) {
    $fullPath = __DIR__ . '/../uploads/' . $perfilUsuario['cover_image'];
    if (file_exists($fullPath)) {
        $cover_image_path = '../uploads/' . $perfilUsuario['cover_image'];
    }
}

// Processar ação de seguir/deixar de seguir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_follow'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        if ($isConnected) {
            // Remover conexão
            $stmtDelete = $pdo->prepare("
                DELETE FROM connections 
                WHERE (follower_id = ? AND following_id = ?) OR (follower_id = ? AND following_id = ?)
            ");
            $stmtDelete->execute([$usuarioLogado['id'], $perfilUsuario['id'], $perfilUsuario['id'], $usuarioLogado['id']]);
            echo json_encode(['success' => true, 'seguindo' => false, 'message' => 'Conexão removida']);
        } else {
            // Criar solicitação de conexão
            $stmtInsert = $pdo->prepare("
                INSERT INTO connections (follower_id, following_id, status) 
                VALUES (?, ?, 'pending')
                ON DUPLICATE KEY UPDATE status = 'pending'
            ");
            $stmtInsert->execute([$usuarioLogado['id'], $perfilUsuario['id']]);
            
            // Criar notificação
            $stmtNotif = $pdo->prepare("
                INSERT INTO notifications (user_id, type, title, message, related_id) 
                VALUES (?, 'follow', 'Nova solicitação de conexão', ?, ?)
            ");
            $message = $usuarioLogado['full_name'] . ' quer se conectar com você';
            $stmtNotif->execute([$perfilUsuario['id'], $message, $usuarioLogado['id']]);
            
            echo json_encode(['success' => true, 'seguindo' => false, 'pendente' => true, 'message' => 'Solicitação enviada']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao processar']);
    }
    exit;
}

$pageTitle = ($perfilUsuario['full_name'] ?: $perfilUsuario['username']) . ' - Anthems';

// Configurações para o header.php
$page_title = $pageTitle;
$active_page = 'perfil';
$base_path = '../';

// Incluir header (já inclui DOCTYPE, html, head, body)
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

    <div class="profile-container">
        <!-- Capa e foto de perfil -->
        <div class="cover-photo" style="<?= $cover_image_path ? 'background-image: url(' . htmlspecialchars($cover_image_path) . ')' : '' ?>">
            <div class="profile-picture" style="<?= $profile_image_path ? 'background-image: url(' . htmlspecialchars($profile_image_path) . ')' : '' ?>"></div>
        </div>
        
        <!-- Conteúdo do perfil -->
        <div class="profile-content">
            <div class="profile-header-row">
                <div>
                    <h1><?= htmlspecialchars($perfilUsuario['full_name'] ?: $perfilUsuario['username']) ?></h1>
                    <span class="profile-username">@<?= htmlspecialchars($perfilUsuario['username']) ?></span>
                </div>
                <?php if ($isConnected): ?>
                    <button id="follow-btn" class="follow-btn following" onclick="toggleFollow()" data-status="connected">
                        Conectado
                    </button>
                <?php elseif ($isPending): ?>
                    <button id="follow-btn" class="follow-btn pending" disabled>
                        Solicitação pendente
                    </button>
                <?php else: ?>
                    <button id="follow-btn" class="follow-btn" onclick="toggleFollow()" data-status="none">
                        Conectar
                    </button>
                <?php endif; ?>
            </div>
            
            <!-- Estatísticas -->
            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-number"><?= number_format($connectionsCount) ?></span>
                    <span class="stat-label">conexões</span>
                </div>
            </div>
            
            <!-- Biografia -->
            <div class="profile-bio">
                <h4>Bio:</h4>
                <p><?= htmlspecialchars($perfilUsuario['bio'] ?: 'Sem bio ainda.') ?></p>
            </div>
            
            <!-- Álbuns favoritos -->
            <?php if (!empty($liked_albums)): ?>
            <div class="favorite-albums-display">
                <h3>Álbuns Favoritos</h3>
                <div class="favorite-albums-grid">
                    <?php foreach ($liked_albums as $album): ?>
                    <a href="../albuns/album.php?id=<?= $album['id'] ?>" class="favorite-album-card">
                        <div class="favorite-album-cover" style="background-image: url('../img/<?= htmlspecialchars($album['image']) ?>');">
                            <?php if (empty($album['image']) || $album['image'] === 'NTHMS.png'): ?>
                            <svg width="50" height="50" fill="white" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 14.5c-2.49 0-4.5-2.01-4.5-4.5S9.51 7.5 12 7.5s4.5 2.01 4.5 4.5-2.01 4.5-4.5 4.5zm0-5.5c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1z"/></svg>
                            <?php endif; ?>
                        </div>
                        <div class="favorite-album-info">
                            <div class="favorite-album-title" title="<?= htmlspecialchars($album['title']) ?>"><?= htmlspecialchars($album['title']) ?></div>
                            <div class="favorite-album-artist" title="<?= htmlspecialchars($album['artist']) ?>"><?= htmlspecialchars($album['artist']) ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

<?php include __DIR__ . '/../includes/chat-sidebar.php'; ?>

<?php
$inline_js = '
function toggleFollow() {
    var btn = document.getElementById("follow-btn");
    var status = btn.dataset.status;
    
    fetch("perfiloutrosusuarios.php?username=' . urlencode($perfilUsuario['username']) . '", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            toggle_follow: "1"
        })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            if (data.seguindo === false && !data.pendente) {
                // Conexão removida
                btn.textContent = "Conectar";
                btn.dataset.status = "none";
                btn.className = "follow-btn";
                btn.disabled = false;
                mostrarToast("Conexão removida");
            } else if (data.pendente) {
                // Solicitação enviada
                btn.textContent = "Solicitação pendente";
                btn.dataset.status = "pending";
                btn.className = "follow-btn pending";
                btn.disabled = true;
                mostrarToast("Solicitação de conexão enviada!");
            }
        } else {
            mostrarToast(data.message || "Erro ao processar", "error");
        }
    })
    .catch(function(error) {
        console.error("Erro:", error);
        mostrarToast("Erro ao processar", "error");
    });
}

function mostrarToast(mensagem, tipo) {
    tipo = tipo || "success";
    var toast = document.createElement("div");
    toast.className = "toast " + tipo;
    toast.textContent = mensagem;
    toast.style.cssText = "position:fixed;top:20px;right:20px;padding:15px 20px;border-radius:5px;color:white;z-index:1050;opacity:0;transition:opacity 0.3s ease;background:" + (tipo === "error" ? "#dc3545" : "#28a745") + ";";
    
    document.body.appendChild(toast);
    
    setTimeout(function() { toast.style.opacity = "1"; }, 100);
    setTimeout(function() {
        toast.style.opacity = "0";
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}
';

include __DIR__ . '/../includes/footer.php';
?>
