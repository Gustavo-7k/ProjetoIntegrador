<?php
require_once __DIR__ . '/../config.php';
requireAuth();

$usuario = getCurrentUser();
$pdo = getDBConnection();

// Buscar todas as notificações do usuário do banco de dados
$stmt = $pdo->prepare("
    SELECT 
        n.*,
        CASE 
            WHEN n.type = 'follow' THEN (
                SELECT c.id FROM connections c 
                WHERE c.follower_id = n.related_id 
                AND c.following_id = ? 
                AND c.status = 'pending'
                LIMIT 1
            )
            ELSE NULL
        END as connection_id,
        CASE 
            WHEN n.type = 'follow' THEN (
                SELECT u.profile_image FROM users u WHERE u.id = n.related_id
            )
            WHEN n.type = 'like' OR n.type = 'comment' THEN (
                SELECT u.profile_image FROM users u 
                JOIN comments c ON c.user_id = u.id 
                WHERE c.id = n.related_id 
                LIMIT 1
            )
            ELSE NULL
        END as related_avatar,
        CASE 
            WHEN n.type = 'follow' THEN n.related_id
            WHEN n.type = 'like' OR n.type = 'comment' THEN (
                SELECT c.user_id FROM comments c WHERE c.id = n.related_id LIMIT 1
            )
            ELSE NULL
        END as related_user_id,
        CASE 
            WHEN n.type = 'follow' THEN (
                SELECT u.username FROM users u WHERE u.id = n.related_id
            )
            WHEN n.type = 'like' OR n.type = 'comment' THEN (
                SELECT u.username FROM users u 
                JOIN comments c ON c.user_id = u.id 
                WHERE c.id = n.related_id 
                LIMIT 1
            )
            ELSE NULL
        END as related_username
    FROM notifications n
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
");
$stmt->execute([$usuario['id'], $usuario['id']]);
$notificacoesDB = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Marcar todas como lidas
$stmtUpdate = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
$stmtUpdate->execute([$usuario['id']]);

// Formatar notificações
$notificacoes = [];
foreach ($notificacoesDB as $n) {
    // Formatar data relativa
    $created = new DateTime($n['created_at']);
    $now = new DateTime();
    $diff = $now->diff($created);
    
    if ($diff->days == 0) {
        if ($diff->h == 0) {
            $tempo = $diff->i == 0 ? 'Agora' : 'Há ' . $diff->i . ' min';
        } else {
            $tempo = 'Há ' . $diff->h . ' hora' . ($diff->h > 1 ? 's' : '');
        }
    } elseif ($diff->days == 1) {
        $tempo = 'Ontem, ' . $created->format('H:i');
    } elseif ($diff->days < 7) {
        $tempo = $diff->days . ' dias atrás';
    } else {
        $tempo = $created->format('d/m/Y H:i');
    }
    
    // Verificar se é solicitação pendente de follow
    $isPendingFollow = ($n['type'] === 'follow' && !empty($n['connection_id']));
    
    // Avatar
    $avatar = !empty($n['related_avatar']) ? '../uploads/' . $n['related_avatar'] : '../img/NTHMS.png';
    
    $notificacoes[] = [
        'id' => $n['id'],
        'tipo' => $n['type'],
        'titulo' => $n['title'],
        'mensagem' => $n['message'],
        'avatar' => $avatar,
        'tempo' => $tempo,
        'lida' => $n['is_read'],
        'acao' => $isPendingFollow,
        'connection_id' => $n['connection_id'] ?? null,
        'related_id' => $n['related_id'],
        'related_user_id' => $n['related_user_id'] ?? null,
        'related_username' => $n['related_username'] ?? null
    ];
}

$pageTitle = 'Notificações - Anthems';

// Configurações para o header.php
$page_title = $pageTitle;
$active_page = 'notificacoes';
$base_path = '../';

// Incluir header (já inclui DOCTYPE, html, head, body)
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

    <div class="notification-container">
        <div class="notification-header">
            Notificações
        </div>
        
        <!-- Lista de Notificações -->
        <div class="notification-list">
            <?php if (empty($notificacoes)): ?>
                <div class="empty-notifications">
                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <p>Você não tem notificações ainda</p>
                </div>
            <?php else: ?>
                <?php foreach ($notificacoes as $notificacao): ?>
                    <div class="notification-item <?= $notificacao['lida'] ? '' : 'unread' ?>" data-id="<?= $notificacao['id'] ?>">
                        <?php if (!empty($notificacao['related_username'])): ?>
                            <a href="../perfil/perfiloutrosusuarios.php?username=<?= htmlspecialchars($notificacao['related_username']) ?>" class="notification-avatar-link">
                                <img src="<?= htmlspecialchars($notificacao['avatar']) ?>" alt="Avatar" class="notification-avatar" onerror="this.src='../img/NTHMS.png'">
                            </a>
                        <?php else: ?>
                            <img src="<?= htmlspecialchars($notificacao['avatar']) ?>" alt="Avatar" class="notification-avatar" onerror="this.src='../img/NTHMS.png'">
                        <?php endif; ?>
                        <div class="notification-content">
                            <div class="notification-title">
                                <?php if (!empty($notificacao['related_username'])): ?>
                                    <a href="../perfil/perfiloutrosusuarios.php?username=<?= htmlspecialchars($notificacao['related_username']) ?>" class="notification-user-link">
                                        <?= htmlspecialchars($notificacao['titulo']) ?>
                                    </a>
                                <?php else: ?>
                                    <?= htmlspecialchars($notificacao['titulo']) ?>
                                <?php endif; ?>
                            </div>
                            <div class="notification-message">
                                <?= htmlspecialchars($notificacao['mensagem']) ?>
                            </div>
                            <div class="notification-time"><?= htmlspecialchars($notificacao['tempo']) ?></div>
                        </div>
                        <?php if ($notificacao['acao'] && $notificacao['connection_id']): ?>
                            <div class="notification-actions">
                                <button class="btn-accept" onclick="aceitarConexao(<?= $notificacao['connection_id'] ?>, <?= $notificacao['id'] ?>)">Aceitar</button>
                                <button class="btn-decline" onclick="recusarConexao(<?= $notificacao['connection_id'] ?>, <?= $notificacao['id'] ?>)">Recusar</button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

<?php include __DIR__ . '/../includes/chat-sidebar.php'; ?>

<?php
$inline_js = '
function aceitarConexao(connectionId, notificacaoId) {
    fetch("../api/aceitar-conexao.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ connection_id: connectionId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`[data-id="${notificacaoId}"]`);
            if (item) {
                const actions = item.querySelector(".notification-actions");
                if (actions) actions.remove();
                item.classList.add("accepted");
            }
            mostrarToast("Conexão aceita com sucesso!");
        } else {
            mostrarToast(data.message || "Erro ao aceitar conexão", "error");
        }
    })
    .catch(error => {
        console.error("Erro:", error);
        mostrarToast("Erro ao aceitar conexão", "error");
    });
}

function recusarConexao(connectionId, notificacaoId) {
    fetch("../api/recusar-conexao.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ connection_id: connectionId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`[data-id="${notificacaoId}"]`);
            if (item) item.remove();
            mostrarToast("Conexão recusada");
        } else {
            mostrarToast(data.message || "Erro ao recusar conexão", "error");
        }
    })
    .catch(error => {
        console.error("Erro:", error);
        mostrarToast("Erro ao recusar conexão", "error");
    });
}

function mostrarToast(mensagem, tipo) {
    tipo = tipo || "success";
    const toast = document.createElement("div");
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
