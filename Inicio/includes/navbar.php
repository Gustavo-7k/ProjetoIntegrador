<?php
require_once __DIR__ . '/../config.php';
$base_path = isset($base_path) ? $base_path : '';
$active_page = isset($active_page) ? $active_page : '';

// Verificar se o usuário é admin automaticamente
$currentUserNav = isLoggedIn() ? getCurrentUser() : null;
$is_admin = $currentUserNav && isset($currentUserNav['is_admin']) && $currentUserNav['is_admin'];
?>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container">
    <a class="navbar-brand navbar-brand-custom" href="/inicio.php">
            <img src="/img/discosemfundo.png" alt="Logo" class="navbar-brand-logo">
            <span class="navbar-brand-text">anthems</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" 
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if ($is_admin): ?>
                    <li class="nav-item">
                                        <a class="nav-link <?php echo $active_page === 'denuncias' ? 'active' : ''; ?>" 
                                            href="/adm/ADMdenuncias.php">Denúncias</a>
                    </li>
                <?php endif; ?>
                
                <li class="nav-item">
                              <a class="nav-link <?php echo $active_page === 'inicio' ? 'active' : ''; ?>" 
                                  href="/inicio.php">Inicio</a>
                </li>
                
                <li class="nav-item">
                              <a class="nav-link <?php echo $active_page === 'conexoes' ? 'active' : ''; ?>" 
                                  href="/comentarios/VerComentariosConexoes.php">Conexões</a>
                </li>
                
                <li class="nav-item">
                              <a class="nav-link <?php echo $active_page === 'novo_comentario' ? 'active' : ''; ?>" 
                                  href="/comentarios/novocomentario.php">Novo Comentário</a>
                </li>
                
                <?php if (isLoggedIn()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="addAlbumBtn" data-bs-toggle="modal" data-bs-target="#addAlbumModal">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle;">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="16"></line>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                        </svg>
                        <span class="d-none d-lg-inline ms-1">Álbum</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                              <a class="nav-link <?php echo $active_page === 'perfil' ? 'active' : ''; ?>" 
                                  href="/perfil/perfil.php">Usuario</a>
                </li>
                
                <!-- Chat removed -->
                
                <!-- Notifications Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="notificationDropdown">
                        <img src="/img/notificacaoicon.png" alt="Notificações" class="nav-icon nav-notification-icon">
                        <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                        <div class="notification-header">
                            <h6 class="mb-0">Notificações</h6>
                        </div>
                        <div class="notification-content" id="notificationContent">
                            <p class="text-muted mb-0 p-3">Carregando...</p>
                        </div>
                        <div class="notification-footer">
                            <a href="/notificacoes/todasnotificacoes.php" class="text-primary">Ver todas</a>
                        </div>
                    </div>
                </li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item d-flex align-items-center">
                        <a href="/login/logout.php" class="btn btn-outline-light btn-sm ms-2">Sair</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Modal Adicionar Álbum -->
<?php if (isLoggedIn()): ?>
<div class="modal fade" id="addAlbumModal" tabindex="-1" aria-labelledby="addAlbumModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content add-album-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="addAlbumModalLabel">Adicionar Novo Álbum</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="addAlbumForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="albumTitle" class="form-label">Título do Álbum *</label>
                        <input type="text" class="form-control" id="albumTitle" name="title" required placeholder="Ex: In Rainbows">
                    </div>
                    
                    <div class="mb-3">
                        <label for="albumArtist" class="form-label">Artista *</label>
                        <input type="text" class="form-control" id="albumArtist" name="artist" required placeholder="Ex: Radiohead">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="albumGenre" class="form-label">Gênero</label>
                            <input type="text" class="form-control" id="albumGenre" name="genre" placeholder="Ex: Rock Alternativo">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="albumYear" class="form-label">Ano de Lançamento</label>
                            <input type="number" class="form-control" id="albumYear" name="release_year" min="1900" max="2030" placeholder="Ex: 2007">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="albumCover" class="form-label">Capa do Álbum</label>
                        <input type="file" class="form-control" id="albumCover" name="cover" accept="image/*">
                        <div class="form-text">Formatos: JPG, PNG, GIF, WebP. Máximo 5MB.</div>
                        <div class="form-text mt-1">
                            <i class="bi bi-lightbulb text-warning"></i> 
                            Sugerimos buscar a capa do seu álbum neste site: 
                            <a href="https://covers.musichoarders.xyz/" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                                covers.musichoarders.xyz
                            </a>
                        </div>
                        <div id="albumCoverPreview" class="album-cover-preview mt-2"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-add-album" id="submitAlbumBtn">
                    <span class="btn-text">Adicionar Álbum</span>
                    <span class="btn-loading" style="display: none;">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Salvando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('addAlbumForm');
    const submitBtn = document.getElementById('submitAlbumBtn');
    const coverInput = document.getElementById('albumCover');
    const coverPreview = document.getElementById('albumCoverPreview');
    const modal = document.getElementById('addAlbumModal');
    
    if (!form || !submitBtn) return;
    
    // Preview da capa
    if (coverInput) {
        coverInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    coverPreview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                    coverPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                coverPreview.innerHTML = '';
                coverPreview.style.display = 'none';
            }
        });
    }
    
    // Enviar formulário
    submitBtn.addEventListener('click', function() {
        const title = document.getElementById('albumTitle').value.trim();
        const artist = document.getElementById('albumArtist').value.trim();
        
        if (!title) {
            alert('Título do álbum é obrigatório');
            return;
        }
        
        if (!artist) {
            alert('Nome do artista é obrigatório');
            return;
        }
        
        const formData = new FormData(form);
        
        // Mostrar loading
        submitBtn.querySelector('.btn-text').style.display = 'none';
        submitBtn.querySelector('.btn-loading').style.display = 'inline-flex';
        submitBtn.disabled = true;
        
        fetch('/api/adicionar-album.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                form.reset();
                coverPreview.innerHTML = '';
                coverPreview.style.display = 'none';
                
                // Fechar modal
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) bsModal.hide();
            } else {
                alert(data.message || 'Erro ao adicionar álbum');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao adicionar álbum. Tente novamente.');
        })
        .finally(() => {
            submitBtn.querySelector('.btn-text').style.display = 'inline';
            submitBtn.querySelector('.btn-loading').style.display = 'none';
            submitBtn.disabled = false;
        });
    });
    
    // Limpar form ao fechar modal
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            form.reset();
            coverPreview.innerHTML = '';
            coverPreview.style.display = 'none';
        });
    }
})();
</script>
<?php endif; ?>
