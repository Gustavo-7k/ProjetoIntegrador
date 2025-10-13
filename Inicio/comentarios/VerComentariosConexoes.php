<?php
require_once __DIR__ . '/../config.php';
requireAuth();

$usuario = getCurrentUser();

// Comentários das conexões (placeholder - implementar com banco de dados)
$comentarios = [
    [
        'id' => 1,
        'album_titulo' => 'OK Computer',
        'album_capa' => '../img/okcomputer.jpeg',
        'autor' => 'João Pedro',
        'autor_avatar' => 'JP',
        'data' => '2 dias atrás',
        'texto' => 'Álbum revolucionário que definiu uma geração. A maneira como o Radiohead combina letras existencialistas com arranjos inovadores é impressionante. "Paranoid Android" continua sendo uma das músicas mais ambiciosas já gravadas no rock alternativo. A transição entre as seções da música é magistral, criando uma sensação de épico em miniatura. A produção do álbum, com seus layers de guitarra e efeitos, cria uma atmosfera única que ainda hoje soa futurista.',
        'curtidas' => 24
    ],
    [
        'id' => 2,
        'album_titulo' => 'In Rainbows',
        'album_capa' => '../img/InRainbows.jpeg',
        'autor' => 'Ana Maria',
        'autor_avatar' => 'AM',
        'data' => '1 semana atrás',
        'texto' => 'A experiência de ouvir In Rainbows pela primeira vez foi única. A faixa "Weird Fishes/Arpeggi" é uma jornada sonora incrível. O álbum mostra o Radiohead em seu melhor momento, equilibrando experimentalismo com acessibilidade. A seção rítmica de "15 Step" é hipnótica, enquanto "Reckoner" apresenta uma das melhores performances vocais de Yorke. O jeito como as guitarras se entrelaçam em "Jigsaw Falling Into Place" é pura magia. Um álbum que recompensa ouvidos atentos com cada nova audição.',
        'curtidas' => 18
    ],
    [
        'id' => 3,
        'album_titulo' => 'Kid A',
        'album_capa' => '../img/kida.jpeg',
        'autor' => 'Carlos Silva',
        'autor_avatar' => 'CS',
        'data' => '2 semanas atrás',
        'texto' => 'Quando Kid A foi lançado, ninguém esperava essa guinada eletrônica do Radiohead. Anos depois, fica claro que foi uma das decisões mais corajosas e visionárias da banda. "Everything in Its Right Place" é hipnótica, com seus vocais distorcidos e sintetizadores pulsantes. "How to Disappear Completely" é uma das músicas mais emocionantes já gravadas, com sua orquestração flutuante. O álbum inteiro é uma experiência imersiva que desafia as convenções do rock e abre caminho para novas possibilidades sonoras.',
        'curtidas' => 32
    ]
];

$pageTitle = 'Comentários das Conexões - Anthems';
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
    <?= gerarCSRFMeta() ?>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    <?php include __DIR__ . '/../includes/chat-sidebar.php'; ?>

    <!-- Main content -->
    <div class="main-container">
        <h1>Últimos Comentários de Conexões</h1>
        
        <?php foreach ($comentarios as $comentario): ?>
            <!-- Review Card -->
            <a href="Vercomentario.php?id=<?= $comentario['id'] ?>" style="text-decoration: none; color: inherit;">
                <div class="review-card">
                    <div class="album-cover-container" style="background-image: url('<?= htmlspecialchars($comentario['album_capa']) ?>');"></div>
                    <div class="review-content-container">
                        <div class="album-variations">
                            <?= htmlspecialchars($comentario['album_titulo']) ?>
                        </div>
                        
                        <div class="reviewer-info">
                            <div class="reviewer-avatar"><?= htmlspecialchars($comentario['autor_avatar']) ?></div>
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
                                Curtir (<?= $comentario['curtidas'] ?>)
                            </button>
                            <button class="action-btn" onclick="event.preventDefault(); compartilharComentario(<?= $comentario['id'] ?>)">
                                Compartilhar
                            </button>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/anthems.js"></script>

    <script>
        // Funções específicas dos comentários
        function curtirComentario(comentarioId) {
            fetch('../api/curtir-comentario.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ comentario_id: comentarioId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar contador de curtidas
                    const btn = event.target;
                    btn.textContent = `Curtir (${data.curtidas})`;
                    mostrarToast('Comentário curtido!');
                } else {
                    mostrarToast('Erro ao curtir comentário', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarToast('Erro ao curtir comentário', 'error');
            });
        }

        function compartilharComentario(comentarioId) {
            // Implementar funcionalidade de compartilhamento
            const url = window.location.origin + `/comentarios/Vercomentario.php?id=${comentarioId}`;
            
            if (navigator.share) {
                navigator.share({
                    title: 'Comentário no Anthems',
                    url: url
                });
            } else {
                // Fallback: copiar URL para clipboard
                navigator.clipboard.writeText(url).then(() => {
                    mostrarToast('Link copiado para área de transferência!');
                });
            }
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
            
            // Mostrar toast
            setTimeout(() => toast.style.opacity = '1', 100);
            
            // Remover toast
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
