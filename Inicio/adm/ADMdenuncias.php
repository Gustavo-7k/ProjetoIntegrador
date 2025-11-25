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

// Buscar denúncias pendentes do banco
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('SELECT r.id, r.reason, r.description, r.status, r.created_at, u.username as reporter, c.comment as commented_text, cu.username as comment_author FROM reports r JOIN users u ON r.reporter_id = u.id JOIN comments c ON r.comment_id = c.id JOIN users cu ON c.user_id = cu.id WHERE r.status = "pending" ORDER BY r.created_at DESC');
    $stmt->execute();
    $denuncias = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Erro ADMdenuncias: ' . $e->getMessage());
    $denuncias = [];
}

$pageTitle = 'Denúncias - Administração - Anthems';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" type="image/png" href="../img/NTHMSnavcon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/estilos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Medula+One&display=swap" rel="stylesheet">
    <!-- CSRF meta removed -->
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #170045;">
        <div class="container">
            <a class="navbar-brand" href="../inicio.php" style="position: relative; display: inline-block; font-family: 'Medula One', sans-serif; font-size: 40px;">
                <img src="../img/discosemfundo.png" alt="Logo" style="height: 57px; position: absolute; top: 7px; left: 0; z-index: 0;">
                <span style="position: relative; z-index: 1; color: white; right: 15px;">anthems</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="#">Denúncias</a></li>
                    <li class="nav-item"><a class="nav-link" href="ADMtelainicial.php">Início</a></li>
                    <li class="nav-item"><a class="nav-link" href="../comentarios/VerComentariosConexoes.php">Conexões</a></li>
                    <li class="nav-item"><a class="nav-link" href="../comentarios/novocomentario.php">Novo Comentário</a></li>
                    <li class="nav-item"><a class="nav-link" href="../perfil/perfil.php">Usuário</a></li>
                    <!-- Chat removed -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="../img/notificacaoicon.png" alt="Notificações" style="height: 30px; margin-bottom: 3px;">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" style="width: 300px; padding: 0;">
                            <div style="padding: 10px 15px; border-bottom: 1px solid #dee2e6;">
                                <h6 class="mb-0">Notificações</h6>
                            </div>
                            <div style="padding: 20px; text-align: center;">
                                <p class="text-muted mb-0">Você não tem notificações</p>
                            </div>
                            <div style="padding: 10px 15px; border-top: 1px solid #dee2e6; text-align: center;">
                                <a href="../notificacoes/todasnotificacoes.php" class="text-primary">Ver todas</a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <?php include __DIR__ . '/../includes/chat-sidebar.php'; ?>

    <!-- Denúncias Content -->
    <div class="notification-container">
        <div class="notification-header">
            Denúncias Pendentes
            <span class="badge bg-danger"><?= count($denuncias) ?></span>
        </div>
        
        <!-- Lista de Denúncias -->
        <div class="notification-list">
            <?php foreach ($denuncias as $denuncia): ?>
                <div class="notification-item admin-report-item" data-report-id="<?= $denuncia['id'] ?>">
                    <img src="<?= htmlspecialchars($denuncia['avatar']) ?>" alt="Avatar" class="notification-avatar">
                    <div class="notification-content">
                        <div class="notification-title">
                            <?= htmlspecialchars($denuncia['denunciante']) ?> enviou uma denúncia
                            <span class="report-type-badge"><?= htmlspecialchars($denuncia['motivo']) ?></span>
                        </div>
                        <div class="notification-time"><?= htmlspecialchars($denuncia['tempo']) ?></div>
                    </div>
                    <div class="admin-actions">
                        <button class="btn btn-sm btn-primary" onclick="verDenuncia(<?= $denuncia['id'] ?>)">
                            Ver Detalhes
                        </button>
                        <button class="btn btn-sm btn-success" onclick="aprovarDenuncia(<?= $denuncia['id'] ?>)">
                            Aprovar
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="rejeitarDenuncia(<?= $denuncia['id'] ?>)">
                            Rejeitar
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal de Detalhes da Denúncia -->
    <div class="modal fade" id="reportDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes da Denúncia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="reportDetailsContent">
                    <!-- Conteúdo será carregado via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-success" onclick="aprovarDenuncia(currentReportId)">Aprovar</button>
                    <button type="button" class="btn btn-danger" onclick="rejeitarDenuncia(currentReportId)">Rejeitar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/anthems.js"></script>

    <script>
        let currentReportId = null;

        function verDenuncia(reportId) {
            currentReportId = reportId;
            
            // Carregar detalhes da denúncia via AJAX
            fetch(`../api/denuncia-detalhes.php?id=${reportId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('reportDetailsContent').innerHTML = `
                        <div class="report-details">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Denunciante:</strong> ${data.denuncia.denunciante}<br>
                                    <strong>Data:</strong> ${data.denuncia.data}<br>
                                    <strong>Tipo:</strong> ${data.denuncia.tipo}
                                </div>
                                <div class="col-md-6">
                                    <strong>Status:</strong> <span class="badge bg-warning">${data.denuncia.status}</span>
                                </div>
                            </div>
                            <hr>
                            <div class="mt-3">
                                <strong>Motivo da Denúncia:</strong>
                                <p class="mt-2">${data.denuncia.descricao}</p>
                            </div>
                            <hr>
                            <div class="mt-3">
                                <strong>Conteúdo Denunciado:</strong>
                                <div class="p-3 border rounded bg-light mt-2">
                                    ${data.denuncia.conteudo_denunciado}
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Mostrar modal
                    new bootstrap.Modal(document.getElementById('reportDetailsModal')).show();
                } else {
                    mostrarToast('Erro ao carregar detalhes da denúncia', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarToast('Erro ao carregar detalhes da denúncia', 'error');
            });
        }

        function aprovarDenuncia(reportId) {
            if (!confirm('Tem certeza que deseja aprovar esta denúncia? Isso resultará em ação contra o conteúdo/usuário denunciado.')) {
                return;
            }

            fetch('../api/processar-denuncia.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    denuncia_id: reportId,
                    acao: 'aprovar'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remover item da lista
                    const item = document.querySelector(`[data-report-id="${reportId}"]`);
                    item.remove();
                    
                    // Fechar modal se estiver aberto
                    const modal = bootstrap.Modal.getInstance(document.getElementById('reportDetailsModal'));
                    if (modal) modal.hide();
                    
                    mostrarToast('Denúncia aprovada com sucesso!');
                } else {
                    mostrarToast('Erro ao aprovar denúncia', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarToast('Erro ao aprovar denúncia', 'error');
            });
        }

        function rejeitarDenuncia(reportId) {
            if (!confirm('Tem certeza que deseja rejeitar esta denúncia?')) {
                return;
            }

            fetch('../api/processar-denuncia.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    denuncia_id: reportId,
                    acao: 'rejeitar'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remover item da lista
                    const item = document.querySelector(`[data-report-id="${reportId}"]`);
                    item.remove();
                    
                    // Fechar modal se estiver aberto
                    const modal = bootstrap.Modal.getInstance(document.getElementById('reportDetailsModal'));
                    if (modal) modal.hide();
                    
                    mostrarToast('Denúncia rejeitada');
                } else {
                    mostrarToast('Erro ao rejeitar denúncia', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarToast('Erro ao rejeitar denúncia', 'error');
            });
        }

        function mostrarToast(mensagem, tipo = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${tipo}`;
            toast.textContent = mensagem;
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                background: ${tipo === 'error' ? '#dc3545' : '#28a745'};
                color: white;
                border-radius: 5px;
                z-index: 1050;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => toast.style.opacity = '1', 100);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
