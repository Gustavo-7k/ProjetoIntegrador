<?php
require_once __DIR__ . '/../config.php';
requireAuth();

$usuario = getCurrentUser();
$pdo = getDBConnection();

// Configurações da página
$page_title = "NTHMS - Anthems | Comentários das Conexões";
$active_page = "conexoes";
$base_path = "../";

// Buscar IDs dos usuários conectados (conexões aceitas)
$stmtConnections = $pdo->prepare("
    SELECT 
        CASE 
            WHEN follower_id = ? THEN following_id 
            ELSE follower_id 
        END as connected_user_id
    FROM connections 
    WHERE (follower_id = ? OR following_id = ?) 
    AND status = 'accepted'
");
$stmtConnections->execute([$usuario['id'], $usuario['id'], $usuario['id']]);
$connectionIds = $stmtConnections->fetchAll(PDO::FETCH_COLUMN);

// Buscar comentários das conexões
$comentarios = [];
if (!empty($connectionIds)) {
    $placeholders = str_repeat('?,', count($connectionIds) - 1) . '?';
    
    $sql = "
        SELECT 
            c.id,
            c.comment as texto,
            c.created_at,
            c.likes_count as likes,
            c.rating,
            u.id as autor_id,
            u.username,
            u.full_name as autor,
            u.profile_image,
            a.title as album_titulo,
            a.cover_image as album_capa,
            a.id as album_id
        FROM comments c
        JOIN users u ON c.user_id = u.id
        LEFT JOIN albums a ON c.album_id = a.id
        WHERE c.user_id IN ($placeholders)
        AND c.status = 'approved'
        ORDER BY c.created_at DESC
        LIMIT 50
    ";
    
    $stmtComments = $pdo->prepare($sql);
    $stmtComments->execute($connectionIds);
    $comentarios = $stmtComments->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar data relativa
    foreach ($comentarios as &$c) {
        $created = new DateTime($c['created_at']);
        $now = new DateTime();
        $diff = $now->diff($created);
        
        if ($diff->days == 0) {
            if ($diff->h == 0) {
                $c['data'] = $diff->i == 0 ? 'Agora' : $diff->i . ' min atrás';
            } else {
                $c['data'] = $diff->h . ' hora' . ($diff->h > 1 ? 's' : '') . ' atrás';
            }
        } elseif ($diff->days == 1) {
            $c['data'] = 'Ontem';
        } elseif ($diff->days < 7) {
            $c['data'] = $diff->days . ' dias atrás';
        } elseif ($diff->days < 30) {
            $weeks = floor($diff->days / 7);
            $c['data'] = $weeks . ' semana' . ($weeks > 1 ? 's' : '') . ' atrás';
        } else {
            $c['data'] = $created->format('d/m/Y');
        }
        
        // Gerar iniciais do avatar
        $names = explode(' ', $c['autor']);
        $c['autor_avatar'] = strtoupper(substr($names[0], 0, 1) . (isset($names[1]) ? substr($names[1], 0, 1) : ''));
    }
    unset($c);
}

// CSS específico para esta página
$inline_css = '
.conexoes-page {
    padding-top: 100px;
    min-height: 100vh;
    background-color: #f5f5f5;
}

.conexoes-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.conexoes-container h1 {
    color: var(--primary-color);
    margin-bottom: 30px;
    font-size: 1.8rem;
}

/* Estado vazio */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.empty-state svg {
    color: var(--primary-light);
    margin-bottom: 20px;
}

.empty-state h3 {
    color: var(--primary-color);
    margin-bottom: 10px;
}

.empty-state p {
    color: #666;
    margin-bottom: 25px;
}

.btn-add-connections {
    display: inline-block;
    background: var(--primary-color);
    color: white;
    padding: 12px 30px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.3s ease;
}

.btn-add-connections:hover {
    background: var(--primary-light);
    color: var(--primary-color);
}

.review-link {
    text-decoration: none;
    color: inherit;
}

.review-card {
    display: flex;
    background: white;
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    min-height: 180px;
}

.review-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.album-cover-container {
    width: 180px;
    height: 180px;
    background-size: cover;
    background-position: center;
    flex-shrink: 0;
}

.review-content-container {
    flex: 1;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.album-variations {
    font-size: 1.2rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 10px;
}

.reviewer-info {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.reviewer-avatar {
    width: 40px;
    height: 40px;
    background: var(--primary-light);
    color: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

.reviewer-avatar-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.reviewer-name {
    font-weight: 600;
    color: #333;
}

.review-date {
    color: #999;
    font-size: 13px;
    margin-left: 10px;
}

.review-text {
    color: #555;
    line-height: 1.6;
    margin-bottom: 15px;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.review-actions {
    display: flex;
    gap: 15px;
}

.action-btn {
    background: none;
    border: 1px solid var(--primary-color);
    color: var(--primary-color);
    padding: 8px 16px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.3s ease;
}

.action-btn:hover {
    background: var(--primary-color);
    color: white;
}

@media (max-width: 600px) {
    .review-card {
        flex-direction: column;
    }
    
    .album-cover-container {
        width: 100%;
        height: auto;
        aspect-ratio: 1 / 1;
        max-height: 250px;
    }
}
';

// Incluir header
include '../includes/header.php';
?>

<?php include '../includes/navbar.php'; ?>

<main class="conexoes-page">
    <div class="conexoes-container">
        <h1>Últimos Comentários de Conexões</h1>
        
        <?php if (empty($comentarios)): ?>
            <div class="empty-state">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <h3>Sem comentários ainda</h3>
                <p>Adicione conexões para ver os comentários delas aqui.</p>
                <a href="../perfil/perfil.php" class="btn-add-connections">Encontrar Conexões</a>
            </div>
        <?php else: ?>
            <?php foreach ($comentarios as $comentario): ?>
                <a href="Vercomentario.php?id=<?= $comentario['id'] ?>" class="review-link">
                    <div class="review-card">
                        <?php if (!empty($comentario['album_capa'])): ?>
                            <div class="album-cover-container" style="background-image: url('/img/<?= htmlspecialchars($comentario['album_capa']) ?>');"></div>
                        <?php else: ?>
                            <div class="album-cover-container" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-light)); display: flex; align-items: center; justify-content: center;">
                                <svg width="50" height="50" fill="white" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 14.5c-2.49 0-4.5-2.01-4.5-4.5S9.51 7.5 12 7.5s4.5 2.01 4.5 4.5-2.01 4.5-4.5 4.5zm0-5.5c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1z"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                        <div class="review-content-container">
                            <div class="album-variations">
                                <?= htmlspecialchars($comentario['album_titulo'] ?? 'Álbum') ?>
                            </div>
                            
                            <div class="reviewer-info">
                                <?php 
                                $hasValidImage = !empty($comentario['profile_image']) && file_exists(__DIR__ . '/../uploads/' . $comentario['profile_image']);
                                if ($hasValidImage): 
                                ?>
                                    <img src="../uploads/<?= htmlspecialchars($comentario['profile_image']) ?>" alt="" class="reviewer-avatar-img">
                                <?php else: ?>
                                    <div class="reviewer-avatar"><?= htmlspecialchars($comentario['autor_avatar']) ?></div>
                                <?php endif; ?>
                                <div>
                                    <span class="reviewer-name"><?= htmlspecialchars($comentario['autor']) ?></span>
                                    <span class="review-date"><?= htmlspecialchars($comentario['data']) ?></span>
                                </div>
                            </div>
                            
                            <div class="review-text">
                                <?= htmlspecialchars($comentario['texto']) ?>
                            </div>
                            
                            <div class="review-actions">
                                <button class="action-btn" onclick="event.preventDefault(); curtirComentario(<?= $comentario['id'] ?>)">
                                    Curtir (<?= $comentario['likes'] ?? 0 ?>)
                                </button>
                                <button class="action-btn" onclick="event.preventDefault(); compartilharComentario(<?= $comentario['id'] ?>)">
                                    Compartilhar
                                </button>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/chat-sidebar.php'; ?>

<?php
$inline_js = '
function curtirComentario(comentarioId) {
    fetch("/api/like-comment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ comment_id: comentarioId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarToast("Comentário curtido!");
        } else {
            mostrarToast("Erro ao curtir comentário", "error");
        }
    })
    .catch(error => {
        console.error("Erro:", error);
        mostrarToast("Erro ao curtir comentário", "error");
    });
}

function compartilharComentario(comentarioId) {
    const url = window.location.origin + "/comentarios/Vercomentario.php?id=" + comentarioId;
    
    if (navigator.share) {
        navigator.share({ title: "Comentário no Anthems", url: url });
    } else {
        navigator.clipboard.writeText(url).then(() => {
            mostrarToast("Link copiado para área de transferência!");
        });
    }
}

function mostrarToast(mensagem, tipo) {
    tipo = tipo || "success";
    const toast = document.createElement("div");
    toast.textContent = mensagem;
    toast.style.cssText = "position:fixed;top:20px;right:20px;padding:15px 20px;background:" + (tipo === "error" ? "#dc3545" : "#28a745") + ";color:white;border-radius:5px;z-index:1050;opacity:0;transition:opacity 0.3s ease;";
    document.body.appendChild(toast);
    setTimeout(function() { toast.style.opacity = "1"; }, 100);
    setTimeout(function() {
        toast.style.opacity = "0";
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}
';

include '../includes/footer.php';
?>
