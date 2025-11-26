<?php
require_once __DIR__ . '/../config.php';
requireAuth();
// Configurações da página
$page_title = "NTHMS - Anthems | Perfil";
$active_page = "perfil";
$base_path = "../";

// CSS adicional para Cropper.js
$additional_css = [
    'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css'
];

// JS adicional para Cropper.js
$additional_js = [
    'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js'
];

// Buscar usuário atual
$current = getCurrentUser();

// Contar conexões do usuário
$connectionsCount = 0;
if ($current) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM connections 
            WHERE (follower_id = ? OR following_id = ?) AND status = 'accepted'
        ");
        $stmt->execute([$current['id'], $current['id']]);
        $connectionsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    } catch (PDOException $e) {
        $connectionsCount = 0;
    }
}

// Dados default para visitantes
$user_profile = [
    'name' => $current ? ($current['full_name'] ?: $current['username']) : 'Convidado',
    'bio' => $current ? ($current['bio'] ?: 'Sem bio ainda.') : 'Faça login para ver e editar seu perfil.',
    'connections' => $connectionsCount,
    'cover_image' => $current && !empty($current['cover_image']) ? ($base_path . 'uploads/' . $current['cover_image']) : '',
    'profile_image' => $current && !empty($current['profile_image']) ? ($base_path . 'uploads/' . $current['profile_image']) : ''
];

$liked_albums = [
    ['title' => 'Histórias de Kebrada Para Crianças Mal Criadas', 'artist' => 'Link do Zap', 'image' => 'NTHMS.png'],
    ['title' => 'In Rainbows', 'artist' => 'Radiohead', 'image' => 'InRainbows.jpeg'],
    ['title' => 'Bury Me At Makeout Creek', 'artist' => 'Mitski', 'image' => 'NTHMS.png'],
    ['title' => 'Blonde', 'artist' => 'Frank Ocean', 'image' => 'NTHMS.png'],
    ['title' => 'This Old Dog', 'artist' => 'Mac Demarco', 'image' => 'NTHMS.png'],
    ['title' => 'Shed', 'artist' => 'Title Fight', 'image' => 'NTHMS.png']
];

$favorite_artists = [
    ['name' => 'Radiohead', 'image' => 'radiohead.jpg'],
    ['name' => 'Mitski', 'image' => 'NTHMS.png'],
    ['name' => 'Frank Ocean', 'image' => 'NTHMS.png'],
    ['name' => 'Mac Demarco', 'image' => 'NTHMS.png'],
    ['name' => 'Big Rush', 'image' => 'NTHMS.png'],
    ['name' => 'Link do zap', 'image' => 'NTHMS.png']
];

// Incluir header
include '../includes/header.php';
?>

<?php include '../includes/navbar.php'; ?>

<!-- Conteúdo do Perfil -->
<div class="profile-container">
    <!-- Capa e foto de perfil -->
    <div class="cover-photo" style="<?php echo $user_profile['cover_image'] ? 'background-image: url('.$user_profile['cover_image'].')' : ''; ?>">
        <div class="profile-picture" style="<?php echo $user_profile['profile_image'] ? 'background-image: url('.$user_profile['profile_image'].')' : ''; ?>"></div>
    </div>
    
    <!-- Conteúdo do perfil -->
    <div class="profile-content">
        <h1><?php echo htmlspecialchars($user_profile['name']); ?></h1>
        
        <button id="edit-profile-btn" class="edit-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
            </svg>
            Editar perfil
        </button>
        
        <!-- Estatísticas -->
        <div class="profile-stats">
            <div class="stat-item">
                <span class="stat-number"><?php echo number_format($user_profile['connections']); ?></span>
                <span class="stat-label">conexões</span>
            </div>
            
            <div class="d-flex align-items-center gap-2 position-relative">
                <button id="toggle-friend-btn" class="btn connection-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                    Adicionar conexão
                </button>
                
                <div id="friend-request-form" class="connection-form">
                    <input type="text" id="search-username" class="form-control connection-input" placeholder="Digite o @usuario" autocomplete="off">
                    <div id="search-results" class="search-results-dropdown"></div>
                    <button id="send-request-btn" class="btn connection-submit">Enviar</button>
                </div>
            </div>
        </div>
        
        <!-- Biografia -->
        <div class="profile-bio">
            <h4>Bio:</h4>
            <p><?php echo htmlspecialchars($user_profile['bio']); ?></p>
        </div>
        
        <!-- Álbuns curtidos -->
        <div class="albums-section">
            <h3>Álbuns curtidos</h3>
            <div id="albumsCarousel" class="carousel slide" data-bs-ride="false">
                <div class="carousel-inner">
                    <?php 
                    $chunks = array_chunk($liked_albums, 3);
                    foreach ($chunks as $index => $chunk): 
                    ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="carousel-grid">
                            <?php foreach ($chunk as $album): ?>
                            <div class="album-card">
                                <div class="album-cover">
                                    <img src="../img/<?php echo htmlspecialchars($album['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($album['title']); ?>"
                                         loading="lazy">
                                    <div class="album-info">
                                        <div class="album-title"><?php echo htmlspecialchars($album['title']); ?></div>
                                        <div class="album-artist"><?php echo htmlspecialchars($album['artist']); ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($chunks) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#albumsCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#albumsCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Próximo</span>
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Artistas favoritos -->
        <div class="albums-section">
            <h3>Artistas Favoritos</h3>
            <div id="artistsCarousel" class="carousel slide" data-bs-ride="false">
                <div class="carousel-inner">
                    <?php 
                    $artistChunks = array_chunk($favorite_artists, 3);
                    foreach ($artistChunks as $index => $chunk): 
                    ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="carousel-grid">
                            <?php foreach ($chunk as $artist): ?>
                            <div class="album-card">
                                <div class="album-cover">
                                    <img src="../img/<?php echo htmlspecialchars($artist['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($artist['name']); ?>"
                                         loading="lazy">
                                    <div class="album-info">
                                        <div class="album-title"><?php echo htmlspecialchars($artist['name']); ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($artistChunks) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#artistsCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#artistsCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Próximo</span>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição -->
<div id="edit-modal" class="edit-modal">
    <div class="edit-modal-content">
        <h3>Editar Perfil</h3>
        
        <form id="profile-edit-form" action="../perfil/update_profile.php" method="POST" data-validate>
            <!-- CSRF token removed intentionally -->
            <!-- Campos de Texto -->
            <div class="mb-3">
                <label for="edit-name">Nome</label>
                <input type="text" id="edit-name" class="form-control" 
                       value="<?php echo htmlspecialchars($user_profile['name']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="edit-bio">Bio</label>
                <textarea id="edit-bio" class="form-control" rows="3"><?php echo htmlspecialchars($user_profile['bio']); ?></textarea>
            </div>
            
            <!-- Upload de Fotos -->
            <div class="mb-3">
                <label for="upload-avatar">Foto de Perfil</label>
                <input type="file" id="upload-avatar" accept="image/*" class="form-control">
                <div id="avatar-preview" class="avatar-preview"></div>
            </div>
            
            <div class="mb-3">
                <label for="upload-cover">Foto de Capa (arraste para ajustar o recorte)</label>
                <input type="file" id="upload-cover" accept="image/*" class="form-control">
                <div id="cover-crop-container" class="cover-crop-container">
                    <img id="cover-crop-preview" class="cover-crop-preview" alt="Preview da capa">
                </div>
            </div>
            
            <!-- Botões -->
            <div class="edit-modal-buttons">
                <button type="button" id="save-changes" class="btn btn-primary btn-primary-custom">Salvar</button>
                <button type="button" id="cancel-edit" class="btn btn-secondary">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/chat-sidebar.php'; ?>

<?php include '../includes/footer.php'; ?>

<script>
(function(){
    const avatarInput = document.getElementById('upload-avatar');
    const coverInput = document.getElementById('upload-cover');
    const avatarPreview = document.getElementById('avatar-preview');
    const coverPreview = document.getElementById('cover-crop-preview');
    const coverCropContainer = document.getElementById('cover-crop-container');
    const profilePic = document.querySelector('.profile-picture');
    const coverPhoto = document.querySelector('.cover-photo');
    const saveBtn = document.getElementById('save-changes');
    
    let coverCropper = null;
    let pendingCoverFile = null;

    function uploadBlob(url, fieldName, blob, filename) {
        const fd = new FormData();
        fd.append(fieldName, blob, filename);
        return fetch(url, { method: 'POST', body: fd }).then(async (r) => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok || !data.ok) throw new Error(data.error || 'Falha no upload');
            return data.path;
        });
    }

    function uploadFile(url, fieldName, file) {
        const fd = new FormData();
        fd.append(fieldName, file);
        return fetch(url, { method: 'POST', body: fd }).then(async (r) => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok || !data.ok) throw new Error(data.error || 'Falha no upload');
            return data.path;
        });
    }

    // Upload de Avatar (sem crop)
    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            const f = this.files && this.files[0];
            if (!f) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                if (avatarPreview) {
                    avatarPreview.style.backgroundImage = 'url(' + e.target.result + ')';
                    avatarPreview.style.display = 'block';
                    avatarPreview.style.width = '100px';
                    avatarPreview.style.height = '100px';
                    avatarPreview.style.borderRadius = '50%';
                    avatarPreview.style.backgroundSize = 'cover';
                    avatarPreview.style.backgroundPosition = 'center';
                    avatarPreview.style.margin = '10px 0';
                }
            };
            reader.readAsDataURL(f);

            uploadFile('../perfil/upload_avatar.php', 'avatar', f)
                .then(function(path) {
                    if (profilePic) profilePic.style.backgroundImage = 'url(../' + path + ')';
                })
                .catch(function(err) { alert(err.message); });
        });
    }

    // Upload de Capa (com crop)
    if (coverInput) {
        coverInput.addEventListener('change', function() {
            const f = this.files && this.files[0];
            if (!f) return;
            
            pendingCoverFile = f;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                if (coverPreview) {
                    coverPreview.src = e.target.result;
                    coverPreview.style.display = 'block';
                    
                    if (coverCropContainer) {
                        coverCropContainer.style.display = 'block';
                        coverCropContainer.style.maxHeight = '300px';
                        coverCropContainer.style.overflow = 'hidden';
                    }
                    
                    // Destruir cropper anterior se existir
                    if (coverCropper) {
                        coverCropper.destroy();
                    }
                    
                    // Inicializar Cropper.js
                    coverCropper = new Cropper(coverPreview, {
                        aspectRatio: 960 / 200, // Proporção exata da capa do perfil (4.8:1)
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: false,
                        toggleDragModeOnDblclick: false,
                    });
                }
            };
            reader.readAsDataURL(f);
        });
    }

    // Ao clicar em Salvar, fazer o crop e upload
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            // Se há um cropper ativo, processar o crop
            if (coverCropper && pendingCoverFile) {
                const canvas = coverCropper.getCroppedCanvas({
                    width: 960,
                    height: 200,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });
                
                if (canvas) {
                    canvas.toBlob(function(blob) {
                        const ext = pendingCoverFile.name.split('.').pop();
                        const filename = 'cover_cropped.' + ext;
                        
                        uploadBlob('../perfil/upload_cover.php', 'cover', blob, filename)
                            .then(function(path) {
                                if (coverPhoto) coverPhoto.style.backgroundImage = 'url(../' + path + ')';
                                alert('Perfil atualizado com sucesso!');
                                
                                // Limpar
                                coverCropper.destroy();
                                coverCropper = null;
                                pendingCoverFile = null;
                                
                                // Fechar modal
                                document.getElementById('edit-modal').style.display = 'none';
                            })
                            .catch(function(err) { alert(err.message); });
                    }, 'image/jpeg', 0.9);
                }
            } else {
                // Sem crop pendente, apenas fechar
                alert('Perfil atualizado!');
                document.getElementById('edit-modal').style.display = 'none';
            }
        });
    }

    // Modal de edição
    const editBtn = document.getElementById('edit-profile-btn');
    const editModal = document.getElementById('edit-modal');
    const cancelBtn = document.getElementById('cancel-edit');

    if (editBtn && editModal) {
        editBtn.addEventListener('click', function() {
            editModal.style.display = 'flex';
        });
    }

    if (cancelBtn && editModal) {
        cancelBtn.addEventListener('click', function() {
            editModal.style.display = 'none';
            if (coverCropper) {
                coverCropper.destroy();
                coverCropper = null;
            }
            pendingCoverFile = null;
        });
    }
    
    // Sistema de adicionar conexão
    const toggleFriendBtn = document.getElementById('toggle-friend-btn');
    const friendForm = document.getElementById('friend-request-form');
    const searchInput = document.getElementById('search-username');
    const searchResults = document.getElementById('search-results');
    const sendRequestBtn = document.getElementById('send-request-btn');
    let selectedUserId = null;
    let searchTimeout = null;
    let formVisible = false;
    
    if (toggleFriendBtn && friendForm) {
        // Garantir estado inicial oculto
        friendForm.style.cssText = 'display: none !important;';
        
        toggleFriendBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            formVisible = !formVisible;
            
            if (formVisible) {
                friendForm.style.cssText = 'display: flex !important; opacity: 1 !important;';
                if (searchInput) searchInput.focus();
            } else {
                friendForm.style.cssText = 'display: none !important;';
            }
        });
    }
    
    // Buscar usuários conforme digita
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            selectedUserId = null;
            
            if (searchTimeout) clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
                return;
            }
            
            searchTimeout = setTimeout(function() {
                fetch('/api/buscar-usuarios.php?q=' + encodeURIComponent(query), {
                    credentials: 'same-origin'
                })
                    .then(r => r.json())
                    .then(data => {
                        console.log('Resposta da busca:', data);
                        if (data.success && data.users && data.users.length > 0) {
                            searchResults.innerHTML = data.users.map(u => {
                                const avatarUrl = u.profile_image ? '/uploads/' + u.profile_image : '/img/default-avatar.svg';
                                return '<div class="search-result-item" data-id="' + u.id + '" data-username="' + u.username + '">' +
                                    '<img src="' + avatarUrl + '" alt="' + u.username + '" onerror="this.src=\'/img/default-avatar.svg\'">' +
                                    '<div class="search-result-info">' +
                                        '<span class="search-result-name">' + (u.full_name || u.username) + '</span>' +
                                        '<span class="search-result-username">@' + u.username + '</span>' +
                                        (u.connection_status ? '<span class="search-result-status">(' + u.connection_status + ')</span>' : '') +
                                    '</div>' +
                                '</div>';
                            }).join('');
                            searchResults.style.display = 'block';
                            
                            // Adicionar listeners aos resultados
                            searchResults.querySelectorAll('.search-result-item').forEach(item => {
                                item.addEventListener('click', function() {
                                    selectedUserId = this.dataset.id;
                                    searchInput.value = '@' + this.dataset.username;
                                    searchResults.style.display = 'none';
                                });
                            });
                        } else {
                            searchResults.innerHTML = '<div class="search-result-empty">Nenhum usuário encontrado</div>';
                            searchResults.style.display = 'block';
                        }
                    })
                    .catch(err => {
                        console.error('Erro na busca:', err);
                        searchResults.innerHTML = '<div class="search-result-empty">Erro ao buscar</div>';
                        searchResults.style.display = 'block';
                    });
            }, 300);
        });
    }
    
    // Enviar solicitação de conexão
    if (sendRequestBtn) {
        sendRequestBtn.addEventListener('click', function() {
            let username = searchInput.value.trim();
            if (username.startsWith('@')) username = username.substring(1);
            
            if (!username) {
                alert('Digite um nome de usuário');
                return;
            }
            
            sendRequestBtn.disabled = true;
            sendRequestBtn.textContent = 'Enviando...';
            
            fetch('/api/enviar-solicitacao.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username: username }),
                credentials: 'same-origin'
            })
            .then(r => r.json())
            .then(data => {
                console.log('Resposta do envio:', data);
                if (data.success) {
                    alert(data.message || 'Solicitação enviada com sucesso!');
                    searchInput.value = '';
                    friendForm.style.cssText = 'display: none !important;';
                    formVisible = false;
                    selectedUserId = null;
                } else {
                    alert(data.message || 'Erro ao enviar solicitação');
                }
            })
            .catch(err => {
                console.error('Erro:', err);
                alert('Erro ao enviar solicitação');
            })
            .finally(() => {
                sendRequestBtn.disabled = false;
                sendRequestBtn.textContent = 'Enviar';
            });
        });
    }
    
    // Fechar resultados ao clicar fora
    document.addEventListener('click', function(e) {
        if (searchResults && !searchResults.contains(e.target) && e.target !== searchInput) {
            searchResults.style.display = 'none';
        }
    });
})();
</script>
