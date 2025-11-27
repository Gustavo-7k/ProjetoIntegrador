<?php
require_once __DIR__ . '/../config.php';
requireAuth();

$usuario = getCurrentUser();

// Obter ID do artista
$artista_id = $_GET['id'] ?? 'radiohead';

// Dados do artista (placeholder - implementar com banco de dados)
$artista = [
    'id' => 'radiohead',
    'nome' => 'Radiohead',
    'capa' => '../img/InRainbows.jpeg',
    'foto' => '../img/radiohead.jpg',
    'ouvintes' => '4.2M',
    'seguindo' => false, // verificar se o usuário segue o artista
    'bio' => [
        'Radiohead é uma banda inglesa de rock alternativo formada em 1985 em Abingdon, Oxfordshire. A banda é composta por Thom Yorke (vocal, guitarra, piano), Jonny Greenwood (guitarra, teclados), Ed O\'Brien (guitarra, backing vocal), Colin Greenwood (baixo) e Phil Selway (bateria).',
        'Conhecida por sua abordagem experimental e inovadora à música, o Radiohead lançou álbuns aclamados como "OK Computer", "Kid A" e "In Rainbows".'
    ],
    'albums' => [
        [
            'id' => 1,
            'titulo' => 'OK Computer',
            'ano' => '1997',
            'capa' => '../img/okcomputer.jpeg'
        ],
        [
            'id' => 2,
            'titulo' => 'Kid A',
            'ano' => '2000',
            'capa' => '../img/kida.jpeg'
        ],
        [
            'id' => 3,
            'titulo' => 'In Rainbows',
            'ano' => '2007',
            'capa' => '../img/InRainbows.jpeg'
        ]
    ]
];

// Processar ação de seguir/deixar de seguir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_follow'])) {
    // CSRF removed - simply toggle follow status (placeholder logic)
    $artista['seguindo'] = !$artista['seguindo'];
    
    // Retornar resposta JSON para AJAX
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'seguindo' => $artista['seguindo']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$pageTitle = $artista['nome'] . ' - Anthems';
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
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    <?php include __DIR__ . '/../includes/chat-sidebar.php'; ?>

    <div class="artist-container">
        <!-- Cover and profile picture -->
        <div class="artist-cover" style="background-image: url('<?= htmlspecialchars($artista['capa']) ?>');">
            <div class="artist-picture" style="background-image: url('<?= htmlspecialchars($artista['foto']) ?>');"></div>
        </div>
        
        <!-- Artist content -->
        <div class="artist-content">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1><?= htmlspecialchars($artista['nome']) ?></h1>
                <button id="follow-btn" class="follow-btn" onclick="toggleFollow()" data-seguindo="<?= $artista['seguindo'] ? 'true' : 'false' ?>">
                    <?= $artista['seguindo'] ? 'Seguindo' : 'Seguir' ?>
                </button>
            </div>
            
            <!-- Stats -->
            <div class="artist-stats">
                <div class="stat-item">
                    <span class="stat-number"><?= htmlspecialchars($artista['ouvintes']) ?></span>
                    <span class="stat-label">ouvintes</span>
                </div>
            </div>
            
            <!-- Bio -->
            <div class="artist-bio">
                <h4>Sobre</h4>
                <?php foreach ($artista['bio'] as $paragrafo): ?>
                    <p><?= htmlspecialchars($paragrafo) ?></p>
                <?php endforeach; ?>
            </div>
            
            <!-- Popular albums -->
            <div class="albums-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3>Álbuns:</h3>
                    <a href="../albuns/vertodosalbunsartista.php?artista=<?= urlencode($artista['id']) ?>" 
                       style="color: #170045; text-decoration: none; font-weight: 500; display: inline-block;">
                        Ver todos
                    </a>
                </div>
                <div class="albums-grid">
                    <?php foreach ($artista['albums'] as $album): ?>
                        <!-- Album Card -->
                        <div class="album-card">
                            <div class="album-cover" style="background-image: url('<?= htmlspecialchars($album['capa']) ?>');">
                                <div class="album-info">
                                    <div class="album-title"><?= htmlspecialchars($album['titulo']) ?></div>
                                    <div class="album-artist"><?= htmlspecialchars($album['ano']) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/anthems.js"></script>

    <script>
        function toggleFollow() {
            const btn = document.getElementById('follow-btn');
            const seguindo = btn.dataset.seguindo === 'true';
            
            fetch('perfilartista.php?id=<?= urlencode($artista['id']) ?>', {
                method: 'POST',
                headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                body: new URLSearchParams({
                    toggle_follow: '1',
                    ajax: '1'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar botão
                    btn.textContent = data.seguindo ? 'Seguindo' : 'Seguir';
                    btn.dataset.seguindo = data.seguindo ? 'true' : 'false';
                    btn.className = data.seguindo ? 'follow-btn following' : 'follow-btn';
                    
                    // Mostrar toast
                    mostrarToast(data.seguindo ? 'Agora você segue este artista!' : 'Você não segue mais este artista');
                } else {
                    mostrarToast('Erro ao alterar seguimento', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarToast('Erro ao alterar seguimento', 'error');
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
