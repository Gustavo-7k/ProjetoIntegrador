<?php
require_once __DIR__ . '/../config.php';

// Verificar se o usuário está logado e é administrador
if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit;
}

$usuario = getCurrentUser();
if (!$usuario || !$usuario['is_admin']) {
    header('Location: ../inicio.php');
    exit;
}

// Filtro de status
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'pending';
$validStatuses = ['pending', 'reviewed', 'resolved', 'all'];
if (!in_array($statusFilter, $validStatuses)) {
    $statusFilter = 'pending';
}

// Buscar denúncias do banco
try {
    $pdo = getDBConnection();
    
    $sql = 'SELECT r.id, r.reason, r.description, r.status, r.created_at, r.admin_notes,
                   u.username as reporter_username, u.profile_image as reporter_avatar,
                   c.comment as comment_text, c.id as comment_id,
                   cu.username as comment_author, cu.profile_image as author_avatar,
                   a.title as album_title, a.id as album_id
            FROM reports r 
            JOIN users u ON r.reporter_id = u.id 
            JOIN comments c ON r.comment_id = c.id 
            JOIN users cu ON c.user_id = cu.id
            JOIN albums a ON c.album_id = a.id';
    
    if ($statusFilter !== 'all') {
        $sql .= ' WHERE r.status = ?';
    }
    
    $sql .= ' ORDER BY r.created_at DESC';
    
    $stmt = $pdo->prepare($sql);
    if ($statusFilter !== 'all') {
        $stmt->execute([$statusFilter]);
    } else {
        $stmt->execute();
    }
    $denuncias = $stmt->fetchAll();
    
    // Contar denúncias pendentes
    $stmtCount = $pdo->query('SELECT COUNT(*) FROM reports WHERE status = "pending"');
    $pendingCount = $stmtCount->fetchColumn();
    
} catch (Exception $e) {
    error_log('Erro ADMdenuncias: ' . $e->getMessage());
    $denuncias = [];
    $pendingCount = 0;
}

// Configurações da página para o header.php
$page_title = 'Denúncias - Administração | NTHMS';
$active_page = 'admin-denuncias';
$base_path = '../';
$is_admin = true;

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';

// Função para formatar data relativa
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) return $diff->y . ' ano' . ($diff->y > 1 ? 's' : '') . ' atrás';
    if ($diff->m > 0) return $diff->m . ' mês' . ($diff->m > 1 ? 'es' : '') . ' atrás';
    if ($diff->d > 0) return $diff->d . ' dia' . ($diff->d > 1 ? 's' : '') . ' atrás';
    if ($diff->h > 0) return $diff->h . ' hora' . ($diff->h > 1 ? 's' : '') . ' atrás';
    if ($diff->i > 0) return $diff->i . ' minuto' . ($diff->i > 1 ? 's' : '') . ' atrás';
    return 'agora';
}

// Função para traduzir motivo
function translateReason($reason) {
    $reasons = [
        'spam' => 'Spam',
        'hate_speech' => 'Discurso de ódio',
        'harassment' => 'Assédio',
        'inappropriate' => 'Conteúdo inapropriado',
        'other' => 'Outro'
    ];
    return $reasons[$reason] ?? $reason;
}

// Função para traduzir status
function translateStatus($status) {
    $statuses = [
        'pending' => 'Pendente',
        'reviewed' => 'Revisado',
        'resolved' => 'Resolvido'
    ];
    return $statuses[$status] ?? $status;
}

// Função para cor do badge de status
function statusBadgeClass($status) {
    $classes = [
        'pending' => 'bg-warning text-dark',
        'reviewed' => 'bg-info',
        'resolved' => 'bg-success'
    ];
    return $classes[$status] ?? 'bg-secondary';
}
?>

<main class="admin-reports-page">
    <div class="container py-4">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">
                <span style="color: #c29fef;">⚠️</span> Gerenciar Denúncias
                <?php if ($pendingCount > 0): ?>
                    <span class="badge bg-danger ms-2"><?= $pendingCount ?> pendente<?= $pendingCount > 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </h1>
            <a href="ADMtelainicial.php" class="btn btn-outline-light">← Voltar ao Admin</a>
        </div>
        
        <!-- Filtros -->
        <div class="filter-tabs mb-4">
            <a href="?status=pending" class="filter-tab <?= $statusFilter === 'pending' ? 'active' : '' ?>">
                Pendentes
            </a>
            <a href="?status=reviewed" class="filter-tab <?= $statusFilter === 'reviewed' ? 'active' : '' ?>">
                Revisados
            </a>
            <a href="?status=resolved" class="filter-tab <?= $statusFilter === 'resolved' ? 'active' : '' ?>">
                Resolvidos
            </a>
            <a href="?status=all" class="filter-tab <?= $statusFilter === 'all' ? 'active' : '' ?>">
                Todos
            </a>
        </div>
        
        <!-- Lista de Denúncias -->
        <?php if (empty($denuncias)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>Nenhuma denúncia encontrada</h3>
                <p>Não há denúncias <?= $statusFilter !== 'all' ? 'com status "' . translateStatus($statusFilter) . '"' : '' ?> no momento.</p>
            </div>
        <?php else: ?>
            <div class="reports-list">
                <?php foreach ($denuncias as $denuncia): ?>
                    <?php
                    $defaultAvatar = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50" fill="#c29fef"/><circle cx="50" cy="40" r="18" fill="#170045"/><ellipse cx="50" cy="85" rx="30" ry="25" fill="#170045"/></svg>');
                    $reporterAvatar = $denuncia['reporter_avatar'] ? htmlspecialchars($denuncia['reporter_avatar']) : $defaultAvatar;
                    $authorAvatar = $denuncia['author_avatar'] ? htmlspecialchars($denuncia['author_avatar']) : $defaultAvatar;
                    ?>
                    <div class="report-card" data-report-id="<?= $denuncia['id'] ?>">
                        <div class="report-header">
                            <div class="report-info">
                                <img src="<?= $reporterAvatar ?>" alt="Avatar" class="report-avatar" onerror="this.src='<?= $defaultAvatar ?>'">
                                <div>
                                    <strong>@<?= htmlspecialchars($denuncia['reporter_username']) ?></strong>
                                    <span class="text-muted">denunciou um comentário</span>
                                </div>
                            </div>
                            <div class="report-meta">
                                <span class="badge <?= statusBadgeClass($denuncia['status']) ?>"><?= translateStatus($denuncia['status']) ?></span>
                                <span class="report-time"><?= timeAgo($denuncia['created_at']) ?></span>
                            </div>
                        </div>
                        
                        <div class="report-body">
                            <div class="report-reason">
                                <span class="reason-badge"><?= translateReason($denuncia['reason']) ?></span>
                                <?php if (!empty($denuncia['description'])): ?>
                                    <p class="reason-description"><?= htmlspecialchars($denuncia['description']) ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="reported-content">
                                <div class="content-header">
                                    <img src="<?= $authorAvatar ?>" alt="Avatar" class="content-avatar" onerror="this.src='<?= $defaultAvatar ?>'">
                                    <div>
                                        <strong>@<?= htmlspecialchars($denuncia['comment_author']) ?></strong>
                                        <span class="text-muted">comentou em</span>
                                        <a href="../albuns/album.php?id=<?= $denuncia['album_id'] ?>#comment-<?= $denuncia['comment_id'] ?>" class="album-link">
                                            <?= htmlspecialchars($denuncia['album_title']) ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="content-text">
                                    <?= htmlspecialchars($denuncia['comment_text']) ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($denuncia['status'] === 'pending'): ?>
                            <div class="report-actions">
                                <button class="btn btn-success btn-sm" onclick="aprovarDenuncia(<?= $denuncia['id'] ?>)">
                                    ✓ Aprovar (Remover comentário)
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="rejeitarDenuncia(<?= $denuncia['id'] ?>)">
                                    ✗ Rejeitar denúncia
                                </button>
                                <button class="btn btn-outline-info btn-sm" onclick="verComentario(<?= $denuncia['album_id'] ?>, <?= $denuncia['comment_id'] ?>)">
                                    👁 Ver no contexto
                                </button>
                            </div>
                        <?php elseif ($denuncia['status'] === 'reviewed'): ?>
                            <div class="report-actions">
                                <span class="text-muted">Denúncia foi rejeitada - comentário mantido</span>
                                <button class="btn btn-outline-success btn-sm" onclick="aprovarDenuncia(<?= $denuncia['id'] ?>)">
                                    Reconsiderar
                                </button>
                            </div>
                        <?php elseif ($denuncia['status'] === 'resolved'): ?>
                            <div class="report-actions">
                                <span class="text-muted">Denúncia aprovada - comentário removido</span>
                                <button class="btn btn-outline-warning btn-sm" onclick="liberarComentario(<?= $denuncia['id'] ?>)">
                                    Restaurar comentário
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.admin-reports-page {
    min-height: calc(100vh - 200px);
    padding-bottom: 40px;
}

.page-title {
    color: #fff;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-tabs {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 8px 20px;
    border-radius: 20px;
    background: rgba(194, 159, 239, 0.1);
    color: #c29fef;
    text-decoration: none;
    border: 1px solid rgba(194, 159, 239, 0.3);
    transition: all 0.3s ease;
}

.filter-tab:hover {
    background: rgba(194, 159, 239, 0.2);
    color: #fff;
}

.filter-tab.active {
    background: #c29fef;
    color: #170045;
    font-weight: bold;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 15px;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #fff;
    margin-bottom: 10px;
}

.empty-state p {
    color: #aaa;
}

.reports-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.report-card {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    padding: 20px;
    border: 1px solid rgba(194, 159, 239, 0.2);
}

.report-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 10px;
}

.report-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.report-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.report-meta {
    display: flex;
    align-items: center;
    gap: 10px;
}

.report-time {
    color: #888;
    font-size: 0.85rem;
}

.report-body {
    margin-bottom: 15px;
}

.report-reason {
    margin-bottom: 15px;
}

.reason-badge {
    display: inline-block;
    padding: 4px 12px;
    background: rgba(220, 53, 69, 0.2);
    color: #ff6b6b;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
}

.reason-description {
    margin-top: 10px;
    color: #ccc;
    font-style: italic;
}

.reported-content {
    background: rgba(0, 0, 0, 0.3);
    border-radius: 10px;
    padding: 15px;
    border-left: 3px solid #c29fef;
}

.content-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.content-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
}

.album-link {
    color: #c29fef;
    text-decoration: none;
}

.album-link:hover {
    text-decoration: underline;
}

.content-text {
    color: #ddd;
    line-height: 1.5;
    word-break: break-word;
}

.report-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.report-actions .btn {
    border-radius: 20px;
}

@media (max-width: 768px) {
    .report-header {
        flex-direction: column;
    }
    
    .report-actions {
        flex-direction: column;
    }
    
    .report-actions .btn {
        width: 100%;
    }
}
</style>

<script>
function aprovarDenuncia(reportId) {
    if (!confirm('Tem certeza que deseja aprovar esta denúncia?\nO comentário será removido da exibição.')) {
        return;
    }

    fetch('../api/processar-denuncia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ denuncia_id: reportId, acao: 'aprovar' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarToast('Denúncia aprovada! Comentário removido.');
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrarToast(data.message || 'Erro ao processar', 'error');
        }
    })
    .catch(() => mostrarToast('Erro ao processar denúncia', 'error'));
}

function rejeitarDenuncia(reportId) {
    if (!confirm('Tem certeza que deseja rejeitar esta denúncia?\nO comentário será mantido.')) {
        return;
    }

    fetch('../api/processar-denuncia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ denuncia_id: reportId, acao: 'rejeitar' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarToast('Denúncia rejeitada.');
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrarToast(data.message || 'Erro ao processar', 'error');
        }
    })
    .catch(() => mostrarToast('Erro ao processar denúncia', 'error'));
}

function liberarComentario(reportId) {
    if (!confirm('Tem certeza que deseja restaurar este comentário?')) {
        return;
    }

    fetch('../api/processar-denuncia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ denuncia_id: reportId, acao: 'liberar' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarToast('Comentário restaurado!');
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrarToast(data.message || 'Erro ao processar', 'error');
        }
    })
    .catch(() => mostrarToast('Erro ao processar', 'error'));
}

function verComentario(albumId, commentId) {
    window.open('../albuns/album.php?id=' + albumId + '#comment-' + commentId, '_blank');
}

function mostrarToast(mensagem, tipo = 'success') {
    const toast = document.createElement('div');
    toast.textContent = mensagem;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${tipo === 'error' ? '#dc3545' : '#28a745'};
        color: white;
        border-radius: 8px;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    `;
    
    document.body.appendChild(toast);
    setTimeout(() => toast.style.opacity = '1', 50);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
