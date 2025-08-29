<?php
require_once __DIR__ . '/../config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

$usuario = obterDadosUsuario();

// Obter artista (padrão Radiohead se não especificado)
$artista_nome = $_GET['artista'] ?? 'radiohead';

// Dados do artista e seus álbuns (placeholder - implementar com banco de dados)
$artistas = [
    'radiohead' => [
        'nome' => 'Radiohead',
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
            ],
            [
                'id' => 4,
                'titulo' => 'The Bends',
                'ano' => '1995',
                'capa' => '../img/thebends.jpg'
            ],
            [
                'id' => 5,
                'titulo' => 'Amnesiac',
                'ano' => '2001',
                'capa' => '../img/amnesiac.jpg'
            ],
            [
                'id' => 6,
                'titulo' => 'Hail to the Thief',
                'ano' => '2003',
                'capa' => '../img/hailtothethief.jpg'
            ],
            [
                'id' => 7,
                'titulo' => 'A Moon Shaped Pool',
                'ano' => '2016',
                'capa' => '../img/moonshapedpool.jpg'
            ],
            [
                'id' => 8,
                'titulo' => 'The King of Limbs',
                'ano' => '2011',
                'capa' => '../img/kingoflimbs.jpg'
            ]
        ]
    ]
];

$artista = $artistas[$artista_nome] ?? $artistas['radiohead'];
$pageTitle = 'Todos os Álbuns - ' . $artista['nome'] . ' - Anthems';
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

    <div class="container">
        <div class="header">
            <h1 class="page-title">Todos os Álbuns - <?= htmlspecialchars($artista['nome']) ?></h1>
            <a href="../perfil/perfilartista.php?id=<?= urlencode($artista_nome) ?>" class="back-link">← Voltar</a>
        </div>
        
        <!-- Filtros e Ordenação -->
        <div class="filter-section mb-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" id="searchAlbums" class="form-control" placeholder="Buscar álbuns...">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2 justify-content-end">
                        <select id="sortOrder" class="form-select" style="max-width: 200px;">
                            <option value="year_desc">Mais recentes</option>
                            <option value="year_asc">Mais antigos</option>
                            <option value="name_asc">A-Z</option>
                            <option value="name_desc">Z-A</option>
                        </select>
                        <button id="viewToggle" class="btn btn-outline-primary" onclick="toggleView()">
                            <i class="bi bi-list"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contador de álbuns -->
        <div class="albums-counter mb-3">
            <span id="albumCount"><?= count($artista['albums']) ?></span> álbuns encontrados
        </div>
        
        <div class="albums-container" id="albumsContainer">
            <?php foreach ($artista['albums'] as $album): ?>
                <div class="album-card" 
                     data-title="<?= htmlspecialchars(strtolower($album['titulo'])) ?>"
                     data-year="<?= $album['ano'] ?>"
                     onclick="openAlbumDetails(<?= $album['id'] ?>)">
                    <div class="album-cover" style="background-image: url('<?= htmlspecialchars($album['capa']) ?>');">
                        <div class="album-overlay">
                            <div class="album-actions">
                                <button class="btn btn-light btn-sm" onclick="event.stopPropagation(); playAlbum(<?= $album['id'] ?>)">
                                    <i class="bi bi-play-fill"></i>
                                </button>
                                <button class="btn btn-light btn-sm" onclick="event.stopPropagation(); addToFavorites(<?= $album['id'] ?>)">
                                    <i class="bi bi-heart"></i>
                                </button>
                            </div>
                        </div>
                        <div class="album-info">
                            <div class="album-title"><?= htmlspecialchars($album['titulo']) ?></div>
                            <div class="album-year"><?= htmlspecialchars($album['ano']) ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Caso não encontre álbuns -->
        <div id="noResults" class="text-center py-5" style="display: none;">
            <i class="bi bi-music-note-list display-1 text-muted"></i>
            <h3 class="text-muted">Nenhum álbum encontrado</h3>
            <p class="text-muted">Tente ajustar sua busca ou filtros</p>
        </div>
    </div>

    <!-- Modal de Detalhes do Álbum -->
    <div class="modal fade" id="albumModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes do Álbum</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="albumModalContent">
                    <!-- Conteúdo será carregado via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="addAlbumToFavorites()">
                        <i class="bi bi-heart"></i> Favoritar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/anthems.js"></script>

    <script>
        let currentView = 'grid'; // 'grid' ou 'list'
        let currentAlbumId = null;

        // Busca em tempo real
        document.getElementById('searchAlbums').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterAndSort();
        });

        // Ordenação
        document.getElementById('sortOrder').addEventListener('change', function() {
            filterAndSort();
        });

        function filterAndSort() {
            const searchTerm = document.getElementById('searchAlbums').value.toLowerCase();
            const sortOrder = document.getElementById('sortOrder').value;
            const albumCards = Array.from(document.querySelectorAll('.album-card'));
            
            // Filtrar
            let visibleCards = albumCards.filter(card => {
                const title = card.dataset.title;
                const visible = title.includes(searchTerm);
                card.style.display = visible ? 'block' : 'none';
                return visible;
            });

            // Ordenar
            visibleCards.sort((a, b) => {
                const titleA = a.dataset.title;
                const titleB = b.dataset.title;
                const yearA = parseInt(a.dataset.year);
                const yearB = parseInt(b.dataset.year);

                switch (sortOrder) {
                    case 'year_desc':
                        return yearB - yearA;
                    case 'year_asc':
                        return yearA - yearB;
                    case 'name_asc':
                        return titleA.localeCompare(titleB);
                    case 'name_desc':
                        return titleB.localeCompare(titleA);
                    default:
                        return 0;
                }
            });

            // Reordenar no DOM
            const container = document.getElementById('albumsContainer');
            visibleCards.forEach(card => container.appendChild(card));

            // Atualizar contador
            document.getElementById('albumCount').textContent = visibleCards.length;

            // Mostrar/esconder mensagem de "nenhum resultado"
            const noResults = document.getElementById('noResults');
            if (visibleCards.length === 0) {
                noResults.style.display = 'block';
                container.style.display = 'none';
            } else {
                noResults.style.display = 'none';
                container.style.display = 'grid';
            }
        }

        function toggleView() {
            const container = document.getElementById('albumsContainer');
            const toggleBtn = document.getElementById('viewToggle');
            
            if (currentView === 'grid') {
                currentView = 'list';
                container.classList.add('list-view');
                toggleBtn.innerHTML = '<i class="bi bi-grid-3x3-gap"></i>';
            } else {
                currentView = 'grid';
                container.classList.remove('list-view');
                toggleBtn.innerHTML = '<i class="bi bi-list"></i>';
            }
        }

        function openAlbumDetails(albumId) {
            currentAlbumId = albumId;
            
            // Simular carregamento de dados do álbum
            const albumData = <?= json_encode($artista['albums']) ?>;
            const album = albumData.find(a => a.id === albumId);
            
            if (album) {
                document.getElementById('albumModalContent').innerHTML = `
                    <div class="row">
                        <div class="col-md-4">
                            <img src="${album.capa}" alt="${album.titulo}" class="img-fluid rounded">
                        </div>
                        <div class="col-md-8">
                            <h3>${album.titulo}</h3>
                            <p class="text-muted">Lançado em ${album.ano}</p>
                            <p class="text-muted">Por <?= htmlspecialchars($artista['nome']) ?></p>
                            
                            <div class="mt-4">
                                <h5>Faixas</h5>
                                <div class="track-list">
                                    <div class="track-item">1. Faixa Exemplo 1</div>
                                    <div class="track-item">2. Faixa Exemplo 2</div>
                                    <div class="track-item">3. Faixa Exemplo 3</div>
                                    <!-- Mais faixas seriam carregadas do banco de dados -->
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <span class="badge bg-primary">${album.ano}</span>
                                <span class="badge bg-secondary">Rock Alternativo</span>
                                <span class="badge bg-secondary">Experimental</span>
                            </div>
                        </div>
                    </div>
                `;
                
                new bootstrap.Modal(document.getElementById('albumModal')).show();
            }
        }

        function playAlbum(albumId) {
            // Implementar funcionalidade de reprodução
            mostrarToast('Reproduzindo álbum...');
        }

        function addToFavorites(albumId) {
            fetch('../api/favoritar-album.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ album_id: albumId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarToast(data.favorited ? 'Álbum adicionado aos favoritos!' : 'Álbum removido dos favoritos');
                } else {
                    mostrarToast('Erro ao favoritar álbum', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarToast('Erro ao favoritar álbum', 'error');
            });
        }

        function addAlbumToFavorites() {
            if (currentAlbumId) {
                addToFavorites(currentAlbumId);
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
            
            setTimeout(() => toast.style.opacity = '1', 100);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Adicionar ícones Bootstrap se não estiverem carregados
        if (!document.querySelector('link[href*="bootstrap-icons"]')) {
            const link = document.createElement('link');
            link.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css';
            link.rel = 'stylesheet';
            document.head.appendChild(link);
        }
    </script>
</body>
</html>
