<?php
require_once __DIR__ . '/../config.php';

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

$usuario = obterDadosUsuario();

// Verificar se é administrador
if (!$usuario['is_admin']) {
    header('Location: ../inicio.php');
    exit;
}

// Obter ID do comentário
$comentario_id = $_GET['id'] ?? 1;

// Dados do comentário (placeholder - implementar com banco de dados)
$comentario = [
    'id' => $comentario_id,
    'album_titulo' => 'In Rainbows',
    'album_capa' => '../img/InRainbows.jpeg',
    'artista' => 'RADIOHEAD',
    'texto' => 'A experiência de ouvir In Rainbows pela primeira vez foi única. A faixa "Weird Fishes/Arpeggi" é uma jornada sonora incrível. O álbum mostra o Radiohead em seu melhor momento, equilibrando experimentalismo com acessibilidade. A seção rítmica de "15 Step" é hipnótica, enquanto "Reckoner" apresenta uma das melhores performances vocais de Yorke. O jeito como as guitarras se entrelaçam em "Jigsaw Falling Into Place" é pura magia. Um álbum que recompensa ouvidos atentos com cada nova audição.'
];

// Comentários do fórum com dados de usuário para administração
$forum_comentarios = [
    [
        'id' => 1,
        'usuario' => 'AntonyFantano',
        'usuario_id' => 123,
        'texto' => 'dsadasd',
        'data' => '10/05/2023 14:30',
        'denuncias' => 2,
        'status' => 'ativo'
    ],
    [
        'id' => 2,
        'usuario' => 'Fãdemusica',
        'usuario_id' => 456,
        'texto' => 'Alguém mais vai ver o show deles no próximo mês?',
        'data' => '11/05/2023 09:15',
        'denuncias' => 0,
        'status' => 'ativo'
    ],
    [
        'id' => 3,
        'usuario' => 'Novo Comentário',
        'usuario_id' => 789,
        'texto' => 'Estou ouvindo esse álbum agora e é sensacional!',
        'data' => '12/05/2023 16:45',
        'novo' => true,
        'denuncias' => 1,
        'status' => 'ativo'
    ]
];

// Processar ações administrativas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action'])) {
        $acao = $_POST['admin_action'];
        $usuario_id = $_POST['usuario_id'] ?? null;
        $comentario_id = $_POST['comentario_id'] ?? null;
        
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            
            switch ($acao) {
                case 'timeout':
                    // Implementar lógica de timeout
                    echo json_encode(['success' => true, 'message' => 'Usuário recebeu timeout de 24h']);
                    break;
                    
                case 'ban':
                    // Implementar lógica de banimento
                    echo json_encode(['success' => true, 'message' => 'Usuário banido permanentemente']);
                    break;
                    
                case 'delete_comment':
                    // Implementar lógica de deletar comentário
                    echo json_encode(['success' => true, 'message' => 'Comentário removido']);
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'message' => 'Ação inválida']);
            }
            exit;
        }
    }
}

// Processar novo comentário
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novo_comentario'])) {
        $novo_comentario = sanitizeInput($_POST['comentario']);
        
        if (!empty($novo_comentario)) {
            // Inserir no banco de dados
            // Por enquanto, apenas redirecionar
            header('Location: ADMVerComentario.php?id=' . $comentario_id . '&success=1');
            exit;
        }
    }
}

$pageTitle = 'Administração - Comentário - Anthems';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
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
                    <li class="nav-item"><a class="nav-link" href="ADMdenuncias.php">Denúncias</a></li>
                    <li class="nav-item"><a class="nav-link" href="ADMtelainicial.php">Início</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="../comentarios/VerComentariosConexoes.php">Conexões</a></li>
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

    <div class="centered-container">
        <!-- Admin Alert -->
        <div class="alert alert-info alert-dismissible fade show">
            <i class="bi bi-shield-check"></i>
            <strong>Modo Administrador:</strong> Você tem privilégios administrativos nesta página.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <!-- Album info -->
        <div class="album-review-container">
            <div class="album-side">
                <img src="<?= htmlspecialchars($comentario['album_capa']) ?>" alt="<?= htmlspecialchars($comentario['album_titulo']) ?>" class="album-cover">
                <div class="album-title"><?= htmlspecialchars($comentario['album_titulo']) ?></div>
            </div>
            <!-- Comentário info -->
            <div class="review-section">
                <div class="review-author">Comentário</div>
                <div class="review-content">
                    <p><?= htmlspecialchars($comentario['artista']) ?></p>
                    <p><?= htmlspecialchars($comentario['texto']) ?></p>
                </div>
            </div>
        </div>
        
        <!-- Discussion section -->
        <div class="discussion-section">
            <div class="discussion-title">Fórum - Administração</div>
            
            <!-- Comment form -->
            <form class="comment-form" method="POST">
                <!-- CSRF hidden input removed -->
                <textarea name="comentario" placeholder="Adicione seu comentário como administrador..." required></textarea>
                <button type="submit" name="novo_comentario" class="btn btn-post">Postar Comentário</button>
            </form>
            
            <!-- Comments list -->
            <div class="comments-list">
                <?php foreach ($forum_comentarios as $forum_comentario): ?>
                    <div class="comment" data-comment-id="<?= $forum_comentario['id'] ?>" data-user-id="<?= $forum_comentario['usuario_id'] ?>">
                        <!-- Admin Actions -->
                        <div class="admin-actions">
                            <button class="admin-actions-toggle">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <div class="admin-actions-menu">
                                <div class="admin-actions-item timeout" onclick="adminAction('timeout', <?= $forum_comentario['usuario_id'] ?>, <?= $forum_comentario['id'] ?>)">
                                    <i class="bi bi-clock-history"></i> Dar Timeout
                                </div>
                                <div class="admin-actions-item ban" onclick="adminAction('ban', <?= $forum_comentario['usuario_id'] ?>, <?= $forum_comentario['id'] ?>)">
                                    <i class="bi bi-person-x"></i> Banir Usuário
                                </div>
                                <div class="admin-actions-item delete" onclick="adminAction('delete_comment', <?= $forum_comentario['usuario_id'] ?>, <?= $forum_comentario['id'] ?>)">
                                    <i class="bi bi-trash"></i> Deletar Comentário
                                </div>
                                <div class="admin-actions-item info" onclick="viewUserInfo(<?= $forum_comentario['usuario_id'] ?>)">
                                    <i class="bi bi-person-lines-fill"></i> Ver Perfil
                                </div>
                            </div>
                        </div>

                        <div class="comment-user">
                            <?= htmlspecialchars($forum_comentario['usuario']) ?>
                            <?php if (isset($forum_comentario['novo']) && $forum_comentario['novo']): ?>
                                <span class="new-comment-tag">Novo</span>
                            <?php endif; ?>
                            <?php if ($forum_comentario['denuncias'] > 0): ?>
                                <span class="badge bg-warning"><?= $forum_comentario['denuncias'] ?> denúncia(s)</span>
                            <?php endif; ?>
                        </div>
                        <div class="comment-text"><?= htmlspecialchars($forum_comentario['texto']) ?></div>
                        <div class="comment-date"><?= htmlspecialchars($forum_comentario['data']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Ação Admin -->
    <div class="modal fade" id="adminActionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Ação Administrativa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="adminActionContent">
                    <!-- Conteúdo será preenchido via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmAdminAction">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 1050;">
            Comentário adicionado com sucesso!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/anthems.js"></script>

    <script>
        let currentAdminAction = null;
        let currentUserId = null;
        let currentCommentId = null;

        // Controlar menu de ações admin
        document.querySelectorAll('.admin-actions-toggle').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const menu = this.nextElementSibling;
                
                // Fechar outros menus
                document.querySelectorAll('.admin-actions-menu').forEach(m => {
                    if (m !== menu) m.classList.remove('show');
                });
                
                menu.classList.toggle('show');
            });
        });

        // Fechar menus ao clicar fora
        document.addEventListener('click', function() {
            document.querySelectorAll('.admin-actions-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        });

        function adminAction(action, userId, commentId) {
            currentAdminAction = action;
            currentUserId = userId;
            currentCommentId = commentId;

            let title = '';
            let message = '';
            
            switch (action) {
                case 'timeout':
                    title = 'Dar Timeout';
                    message = 'Tem certeza que deseja dar timeout de 24h para este usuário? Ele não poderá comentar durante esse período.';
                    break;
                case 'ban':
                    title = 'Banir Usuário';
                    message = 'Tem certeza que deseja banir permanentemente este usuário? Esta ação não pode ser desfeita facilmente.';
                    break;
                case 'delete_comment':
                    title = 'Deletar Comentário';
                    message = 'Tem certeza que deseja deletar este comentário? Esta ação não pode ser desfeita.';
                    break;
            }

            document.querySelector('#adminActionModal .modal-title').textContent = title;
            document.getElementById('adminActionContent').innerHTML = `<p>${message}</p>`;
            
            new bootstrap.Modal(document.getElementById('adminActionModal')).show();
        }

        function viewUserInfo(userId) {
            // Abrir perfil do usuário em nova aba
            window.open(`../perfil/perfil.php?id=${userId}`, '_blank');
        }

        document.getElementById('confirmAdminAction').addEventListener('click', function() {
            if (!currentAdminAction) return;

            fetch('ADMVerComentario.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    admin_action: currentAdminAction,
                    usuario_id: currentUserId,
                    comentario_id: currentCommentId,
                    ajax: '1'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fechar modal
                    bootstrap.Modal.getInstance(document.getElementById('adminActionModal')).hide();
                    
                    // Mostrar mensagem de sucesso
                    mostrarToast(data.message, 'success');
                    
                    // Atualizar interface conforme necessário
                    if (currentAdminAction === 'delete_comment') {
                        const comment = document.querySelector(`[data-comment-id="${currentCommentId}"]`);
                        if (comment) comment.remove();
                    }
                } else {
                    mostrarToast(data.message || 'Erro ao executar ação', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarToast('Erro ao executar ação administrativa', 'error');
            });
        });

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
