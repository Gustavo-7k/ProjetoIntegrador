<?php
require_once 'config.php';

// Configurações da página
$page_title = "NTHMS - Anthems | Página Inicial";
$active_page = "inicio";
$base_path = "";

// Buscar álbuns se usuário estiver logado
$topRatedAlbums = [];
$mostCommentedAlbums = [];

if (isLoggedIn()) {
    try {
        $pdo = getDBConnection();
        
        // Top 3 álbuns melhores avaliados
        $stmt = $pdo->query("
            SELECT id, title, artist, cover_image, average_rating, total_comments
            FROM albums
            WHERE average_rating > 0
            ORDER BY average_rating DESC, total_comments DESC
            LIMIT 3
        ");
        $topRatedAlbums = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Top 3 álbuns mais comentados
        $stmt = $pdo->query("
            SELECT id, title, artist, cover_image, average_rating, total_comments
            FROM albums
            WHERE total_comments > 0
            ORDER BY total_comments DESC, average_rating DESC
            LIMIT 3
        ");
        $mostCommentedAlbums = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Processar caminhos das capas
        foreach ($topRatedAlbums as &$album) {
            $cover = $album['cover_image'] ?? '';
            if ($cover && file_exists(__DIR__ . '/img/albums/' . $cover)) {
                $album['cover_url'] = '/img/albums/' . $cover;
            } elseif ($cover) {
                $album['cover_url'] = '/img/' . $cover;
            } else {
                $album['cover_url'] = '/img/NTHMS.png';
            }
        }
        unset($album);
        
        foreach ($mostCommentedAlbums as &$album) {
            $cover = $album['cover_image'] ?? '';
            if ($cover && file_exists(__DIR__ . '/img/albums/' . $cover)) {
                $album['cover_url'] = '/img/albums/' . $cover;
            } elseif ($cover) {
                $album['cover_url'] = '/img/' . $cover;
            } else {
                $album['cover_url'] = '/img/NTHMS.png';
            }
        }
        unset($album);
        
    } catch (PDOException $e) {
        error_log("Erro ao buscar álbuns: " . $e->getMessage());
    }
}

// CSS específico da página
$inline_css = '
.home-section {
    margin-bottom: 3rem;
    text-align: center;
}

.home-section-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.home-section-title i {
    color: var(--primary-light);
}

.albums-grid {
    display: grid;
    grid-template-columns: repeat(3, 180px);
    gap: 1.25rem;
    justify-content: center;
}

.album-card {
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
    color: inherit;
    display: block;
    max-width: 180px;
}

.album-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 5px 18px rgba(0,0,0,0.15);
}

.album-card-cover {
    width: 100%;
    aspect-ratio: 1;
    object-fit: cover;
}

.album-card-info {
    padding: 0.85rem;
}

.album-card-title {
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.album-card-artist {
    color: #666;
    font-size: 0.85rem;
    margin-bottom: 0.4rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.album-card-stats {
    display: flex;
    gap: 0.6rem;
    font-size: 0.8rem;
    color: #888;
}

.album-card-stats span {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.album-card-stats .rating {
    color: #f5a623;
}

.welcome-hero {
    text-align: center;
    padding: 3rem 0;
    margin-bottom: 2rem;
}

.welcome-hero h1 {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.welcome-hero p {
    color: #666;
    font-size: 1.1rem;
}

.guest-message {
    text-align: center;
    padding: 4rem 2rem;
    background: linear-gradient(135deg, var(--primary-light) 0%, #e8d4ff 100%);
    border-radius: 20px;
    margin-top: 2rem;
}

.guest-message h2 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.guest-message p {
    color: #555;
    margin-bottom: 1.5rem;
}

.guest-message .btn {
    margin: 0 0.5rem;
}

@media (max-width: 768px) {
    .albums-grid {
        grid-template-columns: repeat(3, 140px);
    }
    
    .album-card {
        max-width: 140px;
    }
    
    .welcome-hero h1 {
        font-size: 1.8rem;
    }
}

@media (max-width: 480px) {
    .albums-grid {
        grid-template-columns: repeat(2, 150px);
    }
    
    .album-card {
        max-width: 150px;
    }
}
';

// Incluir header
include 'includes/header.php';
?>

<?php include 'includes/navbar.php'; ?>

<!-- Conteúdo Principal -->
<div class="container">
    <div class="welcome-hero">
        <h1>Bem-vindo ao Anthems!</h1>
        <p>Descubra, compartilhe e conecte-se através da música</p>
    </div>

    <?php if (isLoggedIn()): ?>
        <!-- Álbuns Melhores Avaliados -->
        <?php if (!empty($topRatedAlbums)): ?>
        <section class="home-section">
            <h2 class="home-section-title">
                <i class="bi bi-star-fill"></i> Melhores Avaliados
            </h2>
            <div class="albums-grid">
                <?php foreach ($topRatedAlbums as $album): ?>
                <a href="/albuns/album.php?id=<?= $album['id'] ?>" class="album-card">
                    <img src="<?= htmlspecialchars($album['cover_url']) ?>" 
                         alt="<?= htmlspecialchars($album['title']) ?>" 
                         class="album-card-cover"
                         onerror="this.src='/img/NTHMS.png'">
                    <div class="album-card-info">
                        <div class="album-card-title"><?= htmlspecialchars($album['title']) ?></div>
                        <div class="album-card-artist"><?= htmlspecialchars($album['artist']) ?></div>
                        <div class="album-card-stats">
                            <span class="rating">★ <?= number_format($album['average_rating'], 1) ?></span>
                            <span>💬 <?= $album['total_comments'] ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Álbuns Mais Comentados -->
        <?php if (!empty($mostCommentedAlbums)): ?>
        <section class="home-section">
            <h2 class="home-section-title">
                <i class="bi bi-chat-dots-fill"></i> Mais Comentados
            </h2>
            <div class="albums-grid">
                <?php foreach ($mostCommentedAlbums as $album): ?>
                <a href="/albuns/album.php?id=<?= $album['id'] ?>" class="album-card">
                    <img src="<?= htmlspecialchars($album['cover_url']) ?>" 
                         alt="<?= htmlspecialchars($album['title']) ?>" 
                         class="album-card-cover"
                         onerror="this.src='/img/NTHMS.png'">
                    <div class="album-card-info">
                        <div class="album-card-title"><?= htmlspecialchars($album['title']) ?></div>
                        <div class="album-card-artist"><?= htmlspecialchars($album['artist']) ?></div>
                        <div class="album-card-stats">
                            <span>💬 <?= $album['total_comments'] ?> comentários</span>
                            <span class="rating">★ <?= number_format($album['average_rating'], 1) ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (empty($topRatedAlbums) && empty($mostCommentedAlbums)): ?>
        <div class="text-center py-5">
            <p class="text-muted">Nenhum álbum avaliado ainda. Seja o primeiro a comentar!</p>
            <a href="/albuns/vertodosalbunsartista.php" class="btn btn-primary">Explorar Álbuns</a>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Mensagem para visitantes -->
        <div class="guest-message">
            <h2>Junte-se à comunidade!</h2>
            <p>Faça login para ver os álbuns mais populares, comentar e conectar-se com outros amantes de música.</p>
            <a href="/login/login.php" class="btn btn-primary">Entrar</a>
            <a href="/login/login.php" class="btn btn-outline-secondary">Criar Conta</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/chat-sidebar.php'; ?>

<?php include 'includes/footer.php'; ?>
