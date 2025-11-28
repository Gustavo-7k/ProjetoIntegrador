<?php
require_once __DIR__ . '/../config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

$usuario = obterDadosUsuario();

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

// Comentários do fórum (placeholder - implementar com banco de dados)
$forum_comentarios = [
    [
        'id' => 1,
        'usuario' => 'AntonyFantano',
        'texto' => 'dsadasd',
        'data' => '10/05/2023 14:30'
    ],
    [
        'id' => 2,
        'usuario' => 'Fãdemusica',
        'texto' => 'Alguém mais vai ver o show deles no próximo mês?',
        'data' => '11/05/2023 09:15'
    ],
    [
        'id' => 3,
        'usuario' => 'Novo Comentário',
        'texto' => 'Estou ouvindo esse álbum agora e é sensacional!',
        'data' => '12/05/2023 16:45',
        'novo' => true
    ]
];

// Processar novo comentário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novo_comentario'])) {
        $novo_comentario = trim($_POST['comentario']);
        
        if (!empty($novo_comentario)) {
            // Inserir no banco de dados
            // Por enquanto, apenas redirecionar
            header('Location: Vercomentario.php?id=' . $comentario_id . '&success=1');
            exit;
        }
    }
}

$pageTitle = 'Comentário - Anthems';
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
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    <?php include __DIR__ . '/../includes/chat-sidebar.php'; ?>

    <div class="centered-container">
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
            <div class="discussion-title">Fórum</div>
            
            <!-- Comment form -->
            <form class="comment-form" method="POST">
                <!-- CSRF hidden input removed -->
                <textarea name="comentario" placeholder="Adicione seu comentário..." required></textarea>
                <button type="submit" name="novo_comentario" class="btn btn-post">Postar Comentário</button>
            </form>
            
            <!-- Comments list -->
            <div class="comments-list">
                <?php foreach ($forum_comentarios as $forum_comentario): ?>
                    <div class="comment" data-comment-id="<?= $forum_comentario['id'] ?>">
                        <button class="report-button" onclick="openReportModal(<?= $forum_comentario['id'] ?>)">
                            <i class="bi bi-flag report-icon"></i>Denunciar
                        </button>
                        <div class="comment-user">
                            <?= htmlspecialchars($forum_comentario['usuario']) ?>
                            <?php if (isset($forum_comentario['novo']) && $forum_comentario['novo']): ?>
                                <span class="new-comment-tag">Novo</span>
                            <?php endif; ?>
                        </div>
                        <div class="comment-text"><?= htmlspecialchars($forum_comentario['texto']) ?></div>
                        <div class="comment-date"><?= htmlspecialchars($forum_comentario['data']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal de denúncia -->
    <div id="reportModal" class="report-modal" style="display: none;">
        <div class="report-modal-content">
            <div class="report-modal-title">Denunciar Comentário</div>
            <textarea class="report-modal-textarea" placeholder="Descreva o motivo da denúncia..."></textarea>
            <div class="report-modal-buttons">
                <button class="report-modal-cancel" onclick="closeReportModal()">Cancelar</button>
                <button class="report-modal-submit" onclick="submitReport()">Enviar Denúncia</button>
            </div>
        </div>
    </div>
    
    <!-- Toast de confirmação -->
    <div id="reportToast" class="report-toast" style="display: none;">
        Denúncia enviada com sucesso!
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
        let currentReportId = null;
        
        // Abrir modal de denúncia
        function openReportModal(commentId) {
            currentReportId = commentId;
            document.getElementById('reportModal').style.display = 'flex';
        }
        
        // Fechar modal de denúncia
        function closeReportModal() {
            document.getElementById('reportModal').style.display = 'none';
            document.querySelector('.report-modal-textarea').value = '';
            currentReportId = null;
        }
        
        // Enviar denúncia
        function submitReport() {
            const reportText = document.querySelector('.report-modal-textarea').value;
            
            if (!reportText.trim()) {
                alert('Por favor, descreva o motivo da denúncia.');
                return;
            }
            
            fetch('../api/denunciar-comentario.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    comentario_id: currentReportId,
                    motivo: reportText
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeReportModal();
                    
                    // Mostrar mensagem de sucesso
                    const toast = document.getElementById('reportToast');
                    toast.style.display = 'block';
                    
                    // Esconder toast após 3 segundos
                    setTimeout(() => {
                        toast.style.display = 'none';
                    }, 3000);
                } else {
                    alert('Erro ao enviar denúncia. Tente novamente.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao enviar denúncia. Tente novamente.');
            });
        }
        
        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modal = document.getElementById('reportModal');
            if (event.target === modal) {
                closeReportModal();
            }
        }
    </script>
</body>
</html>
