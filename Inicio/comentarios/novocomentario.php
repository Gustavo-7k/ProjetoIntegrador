<?php
require_once __DIR__ . '/../config.php';
requireAuth();
// Configurações da página
$page_title = "NTHMS - Anthems | Novo Comentário";
$active_page = "novo_comentario";
$base_path = "../";

// Dados dos álbuns disponíveis (em um sistema real, viria do banco de dados)
$available_albums = [
    ['title' => 'In Rainbows', 'artist' => 'Radiohead', 'url' => 'EscreverComentário.php'],
    ['title' => 'Kid A', 'artist' => 'Radiohead', 'url' => 'KidA.php'],
    ['title' => 'Ok Computer', 'artist' => 'Radiohead', 'url' => 'OkComputer.php'],
    ['title' => 'Bury Me At Makeout Creek', 'artist' => 'Mitski', 'url' => 'Mitski.php'],
    ['title' => 'Blonde', 'artist' => 'Frank Ocean', 'url' => 'FrankOcean.php']
];

// Incluir header
include '../includes/header.php';
?>

<?php include '../includes/navbar.php'; ?>

<!-- Conteúdo Principal -->
<div class="container centralizar-tela">
    <div class="row justify-content-center align-items-center w-100">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="text-center mb-4">
                <h2 class="mb-3" style="color: var(--primary-color);">Novo Comentário</h2>
                <p class="lead">Procure o álbum que gostaria de comentar</p>
            </div>
            
            <div class="mb-4">
                <label for="buscaAlbum" class="form-label espaco-label">Procure o Álbum que gostaria:</label>
                <input class="form-control input-largo mx-auto" 
                       list="albuns" 
                       id="buscaAlbum" 
                       placeholder="Digite para buscar..."
                       autocomplete="off">
                
                <datalist id="albuns">
                    <?php foreach ($available_albums as $album): ?>
                        <option value="<?php echo htmlspecialchars($album['title']); ?>" 
                                data-url="<?php echo htmlspecialchars($album['url']); ?>" 
                                data-artist="<?php echo htmlspecialchars($album['artist']); ?>">
                            <?php echo htmlspecialchars($album['artist']); ?>
                        </option>
                    <?php endforeach; ?>
                </datalist>
            </div>
            
            <!-- Sugestões populares -->
            <div class="text-center">
                <h5 class="mb-3" style="color: var(--primary-color);">Álbuns Populares</h5>
                <div class="row g-3">
                    <?php foreach (array_slice($available_albums, 0, 3) as $album): ?>
                        <div class="col-4">
                            <div class="card album-suggestion" 
                                 data-album="<?php echo htmlspecialchars($album['title']); ?>"
                                 style="cursor: pointer; border: 2px solid transparent; transition: all 0.3s ease;">
                                <div class="card-body text-center p-2">
                                    <h6 class="card-title small mb-1"><?php echo htmlspecialchars($album['title']); ?></h6>
                                    <p class="card-text small text-muted mb-0"><?php echo htmlspecialchars($album['artist']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/chat-sidebar.php'; ?>

<?php 
// JavaScript específico para esta página
$inline_js = "
// Configuração dos álbuns para JavaScript
const albumLinks = " . json_encode(array_column($available_albums, 'url', 'title')) . ";

// Manipulador do campo de busca
document.getElementById('buscaAlbum').addEventListener('change', function () {
    const valor = this.value.trim();
    
    if (albumLinks[valor]) {
        window.location.href = albumLinks[valor];
    } else if (valor) {
        const confirmMsg = 'Álbum \"' + valor + '\" não encontrado. Deseja sugerir este álbum?';
        if (confirm(confirmMsg)) {
            // Redirecionar para página de sugestão ou fazer AJAX
            console.log('Sugerindo álbum:', valor);
        }
    }
});

// Manipuladores das sugestões populares
document.querySelectorAll('.album-suggestion').forEach(card => {
    card.addEventListener('click', function() {
        const albumTitle = this.dataset.album;
        document.getElementById('buscaAlbum').value = albumTitle;
        document.getElementById('buscaAlbum').dispatchEvent(new Event('change'));
    });
    
    // Efeito visual no hover
    card.addEventListener('mouseenter', function() {
        this.style.borderColor = 'var(--primary-color)';
        this.style.transform = 'translateY(-2px)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.borderColor = 'transparent';
        this.style.transform = 'translateY(0)';
    });
});

// Auto-complete melhorado
document.getElementById('buscaAlbum').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    const suggestions = document.querySelectorAll('.album-suggestion');
    
    suggestions.forEach(card => {
        const title = card.dataset.album.toLowerCase();
        const shouldShow = query === '' || title.includes(query);
        card.style.display = shouldShow ? 'block' : 'none';
    });
});
";

include '../includes/footer.php'; 
?>
