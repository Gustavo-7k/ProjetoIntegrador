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

// Verificar se as imagens existem
$profile_image_path = '';
$cover_image_path = '';

if ($current && !empty($current['profile_image'])) {
    $fullPath = __DIR__ . '/../uploads/' . $current['profile_image'];
    if (file_exists($fullPath)) {
        $profile_image_path = $base_path . 'uploads/' . $current['profile_image'];
    }
}

if ($current && !empty($current['cover_image'])) {
    $fullPath = __DIR__ . '/../uploads/' . $current['cover_image'];
    if (file_exists($fullPath)) {
        $cover_image_path = $base_path . 'uploads/' . $current['cover_image'];
    }
}

// Dados default para visitantes
$user_profile = [
    'name' => $current ? ($current['full_name'] ?: $current['username']) : 'Convidado',
    'bio' => $current ? ($current['bio'] ?: 'Sem bio ainda.') : 'Faça login para ver e editar seu perfil.',
    'connections' => $connectionsCount,
    'cover_image' => $cover_image_path,
    'profile_image' => $profile_image_path
];

// Buscar álbuns favoritos do usuário do banco de dados
$liked_albums = [];
if ($current) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT a.id, a.title, a.artist, a.cover_image
            FROM user_favorite_albums ufa
            JOIN albums a ON ufa.album_id = a.id
            WHERE ufa.user_id = ?
            ORDER BY ufa.position ASC
            LIMIT 6
        ");
        $stmt->execute([$current['id']]);
        $dbAlbums = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($dbAlbums as $album) {
            $cover = $album['cover_image'] ?? 'NTHMS.png';
            // Verificar onde a imagem está
            if (file_exists(__DIR__ . '/../img/albums/' . $cover)) {
                $imagePath = 'albums/' . $cover;
            } else {
                $imagePath = $cover;
            }
            $liked_albums[] = [
                'id' => $album['id'],
                'title' => $album['title'],
                'artist' => $album['artist'],
                'image' => $imagePath
            ];
        }
    } catch (PDOException $e) {
        // Se der erro, usa array vazio
        $liked_albums = [];
    }
}

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
        
        <!-- Álbuns favoritos -->
        <div class="favorite-albums-display">
            <h3>Álbuns Favoritos</h3>
            <?php if (empty($liked_albums)): ?>
                <div class="empty-albums-message">
                    <p>Você ainda não selecionou seus álbuns favoritos.</p>
                    <p>Clique em "Editar perfil" para adicionar até 6 álbuns.</p>
                </div>
            <?php else: ?>
            <div class="favorite-albums-grid">
                <?php foreach ($liked_albums as $album): ?>
                <a href="../albuns/album.php?id=<?php echo $album['id']; ?>" class="favorite-album-card">
                    <div class="favorite-album-cover" style="background-image: url('../img/<?php echo htmlspecialchars($album['image']); ?>');">
                        <?php if (empty($album['image']) || $album['image'] === 'NTHMS.png'): ?>
                        <svg width="50" height="50" fill="white" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 14.5c-2.49 0-4.5-2.01-4.5-4.5S9.51 7.5 12 7.5s4.5 2.01 4.5 4.5-2.01 4.5-4.5 4.5zm0-5.5c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1z"/></svg>
                        <?php endif; ?>
                    </div>
                    <div class="favorite-album-info">
                        <div class="favorite-album-title" title="<?php echo htmlspecialchars($album['title']); ?>"><?php echo htmlspecialchars($album['title']); ?></div>
                        <div class="favorite-album-artist" title="<?php echo htmlspecialchars($album['artist']); ?>"><?php echo htmlspecialchars($album['artist']); ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
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
            
            <!-- Seleção de Álbuns Favoritos -->
            <div class="mb-3">
                <label>Álbuns Favoritos (selecione até 6)</label>
                <div class="favorite-albums-section">
                    <input type="text" id="album-search-input" class="form-control" placeholder="Buscar álbum por título ou artista..." autocomplete="off">
                    <div id="album-search-results" class="album-search-results"></div>
                    
                    <div class="selected-albums-label">Álbuns selecionados: <span id="selected-count">0</span>/6</div>
                    <div id="selected-albums-grid" class="selected-albums-grid">
                        <!-- Álbuns selecionados aparecerão aqui -->
                    </div>
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
    let pendingAvatarFile = null;

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

    // Upload de Avatar (sem crop) - apenas preview, upload ao salvar
    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            const f = this.files && this.files[0];
            if (!f) return;
            
            // Armazenar arquivo pendente
            pendingAvatarFile = f;
            
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

    // O handler do saveBtn está definido abaixo com a funcionalidade de álbuns favoritos

    // Modal de edição
    const editBtn = document.getElementById('edit-profile-btn');
    const editModal = document.getElementById('edit-modal');
    const cancelBtn = document.getElementById('cancel-edit');

    if (editBtn && editModal) {
        editBtn.addEventListener('click', function() {
            editModal.style.display = 'flex';
            // Carregar álbuns favoritos quando o modal abrir
            if (typeof loadCurrentFavorites === 'function') {
                loadCurrentFavorites();
            }
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
            pendingAvatarFile = null;
            
            // Resetar preview do avatar
            if (avatarPreview) {
                avatarPreview.style.backgroundImage = '';
                avatarPreview.style.display = 'none';
            }
            
            // Resetar preview da capa
            if (coverPreview) {
                coverPreview.src = '';
                coverPreview.style.display = 'none';
            }
            if (coverCropContainer) {
                coverCropContainer.style.display = 'none';
            }
            
            // Limpar inputs de arquivo
            if (avatarInput) avatarInput.value = '';
            if (coverInput) coverInput.value = '';
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
                                const avatarUrl = u.profile_image || '/img/default-avatar.svg';
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
    
    // =============================================
    // SISTEMA DE ÁLBUNS FAVORITOS
    // =============================================
    const albumSearchInput = document.getElementById('album-search-input');
    const albumSearchResults = document.getElementById('album-search-results');
    const selectedAlbumsGrid = document.getElementById('selected-albums-grid');
    const selectedCountEl = document.getElementById('selected-count');
    let selectedAlbums = [];
    let albumSearchTimeout = null;
    const MAX_ALBUMS = 6;
    
    // Carregar álbuns favoritos atuais ao abrir o modal
    function loadCurrentFavorites() {
        fetch('/api/user-favorite-albums.php', { credentials: 'same-origin' })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.favorites) {
                    selectedAlbums = data.favorites.map(a => ({
                        id: a.id,
                        title: a.title,
                        artist: a.artist,
                        cover_url: a.cover_url
                    }));
                    renderSelectedAlbums();
                }
            })
            .catch(err => console.error('Erro ao carregar favoritos:', err));
    }
    
    // Renderizar álbuns selecionados
    function renderSelectedAlbums() {
        if (!selectedAlbumsGrid) return;
        
        selectedAlbumsGrid.innerHTML = selectedAlbums.map((album, index) => {
            const coverUrl = album.cover_url || '/img/NTHMS.png';
            return `
                <div class="selected-album-item" data-id="${album.id}">
                    <button type="button" class="selected-album-remove" onclick="removeSelectedAlbum(${album.id})">&times;</button>
                    <img src="${coverUrl}" alt="${album.title}" onerror="this.src='/img/NTHMS.png'">
                    <div class="selected-album-info">
                        <div class="selected-album-title">${album.title}</div>
                        <div class="selected-album-artist">${album.artist}</div>
                    </div>
                </div>
            `;
        }).join('');
        
        if (selectedCountEl) {
            selectedCountEl.textContent = selectedAlbums.length;
        }
    }
    
    // Remover álbum selecionado
    window.removeSelectedAlbum = function(albumId) {
        selectedAlbums = selectedAlbums.filter(a => a.id !== albumId);
        renderSelectedAlbums();
    };
    
    // Adicionar álbum à seleção
    function addSelectedAlbum(album) {
        if (selectedAlbums.length >= MAX_ALBUMS) {
            alert('Você já selecionou o máximo de ' + MAX_ALBUMS + ' álbuns');
            return;
        }
        
        if (selectedAlbums.find(a => a.id === album.id)) {
            alert('Este álbum já está selecionado');
            return;
        }
        
        selectedAlbums.push(album);
        renderSelectedAlbums();
        
        // Limpar busca
        if (albumSearchInput) albumSearchInput.value = '';
        if (albumSearchResults) {
            albumSearchResults.innerHTML = '';
            albumSearchResults.classList.remove('show');
        }
    }
    
    // Buscar álbuns
    if (albumSearchInput) {
        albumSearchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (albumSearchTimeout) clearTimeout(albumSearchTimeout);
            
            if (query.length < 2) {
                albumSearchResults.innerHTML = '';
                albumSearchResults.classList.remove('show');
                return;
            }
            
            albumSearchTimeout = setTimeout(function() {
                fetch('/api/buscar-albuns.php?q=' + encodeURIComponent(query), { credentials: 'same-origin' })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.albums && data.albums.length > 0) {
                            albumSearchResults.innerHTML = data.albums.map(album => {
                                const isSelected = selectedAlbums.find(a => a.id === album.id);
                                const coverUrl = album.cover_url || '/img/NTHMS.png';
                                return `
                                    <div class="album-search-item ${isSelected ? 'disabled' : ''}" 
                                         data-id="${album.id}" 
                                         data-title="${album.title}" 
                                         data-artist="${album.artist}"
                                         data-cover="${coverUrl}">
                                        <img src="${coverUrl}" alt="${album.title}" onerror="this.src='/img/NTHMS.png'">
                                        <div class="album-search-info">
                                            <span class="album-search-title">${album.title}</span>
                                            <span class="album-search-artist">${album.artist}</span>
                                            ${isSelected ? '<span class="album-search-added">✓ Já selecionado</span>' : ''}
                                        </div>
                                    </div>
                                `;
                            }).join('');
                            albumSearchResults.classList.add('show');
                            
                            // Adicionar listeners
                            albumSearchResults.querySelectorAll('.album-search-item:not(.disabled)').forEach(item => {
                                item.addEventListener('click', function() {
                                    addSelectedAlbum({
                                        id: parseInt(this.dataset.id),
                                        title: this.dataset.title,
                                        artist: this.dataset.artist,
                                        cover_url: this.dataset.cover
                                    });
                                });
                            });
                        } else {
                            albumSearchResults.innerHTML = '<div class="album-search-item disabled"><span>Nenhum álbum encontrado</span></div>';
                            albumSearchResults.classList.add('show');
                        }
                    })
                    .catch(err => {
                        console.error('Erro na busca de álbuns:', err);
                    });
            }, 300);
        });
    }
    
    // Salvar álbuns favoritos
    function saveFavoriteAlbums() {
        const albumIds = selectedAlbums.map(a => a.id);
        
        return fetch('/api/user-favorite-albums.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ album_ids: albumIds }),
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Erro ao salvar álbuns favoritos');
            }
            return data;
        });
    }
    
    // Handler do botão Salvar - executa salvamento direto
    if (saveBtn) {
        saveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const nameInput = document.getElementById('edit-name');
            const bioInput = document.getElementById('edit-bio');
            const name = nameInput ? nameInput.value.trim() : '';
            const bio = bioInput ? bioInput.value.trim() : '';
            
            if (!name) {
                alert('Nome é obrigatório');
                return;
            }
            
            // Função para salvar dados de texto e álbuns
            function saveTextAndAlbums() {
                const formData = new FormData();
                formData.append('name', name);
                formData.append('bio', bio);
                
                // Salvar perfil
                return fetch('../perfil/update_profile.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(r => {
                    if (!r.ok) throw new Error('Erro ao salvar perfil');
                    return saveFavoriteAlbums();
                })
                .then(() => {
                    // Atualizar página
                    const nameDisplay = document.querySelector('.profile-content h1');
                    if (nameDisplay) nameDisplay.textContent = name;
                    
                    const bioDisplay = document.querySelector('.profile-bio p');
                    if (bioDisplay) bioDisplay.textContent = bio || 'Sem bio ainda.';
                    
                    alert('Perfil atualizado com sucesso!');
                    
                    // Recarregar página para mostrar os novos álbuns e imagens
                    window.location.reload();
                });
            }
            
            // Função para fazer upload do avatar se pendente
            function uploadAvatarIfPending() {
                if (pendingAvatarFile) {
                    return uploadFile('../perfil/upload_avatar.php', 'avatar', pendingAvatarFile)
                        .then(function(path) {
                            if (profilePic) profilePic.style.backgroundImage = 'url(../' + path + ')';
                            pendingAvatarFile = null;
                        });
                }
                return Promise.resolve();
            }
            
            // Função para fazer upload da capa se pendente
            function uploadCoverIfPending() {
                if (coverCropper && pendingCoverFile) {
                    return new Promise(function(resolve, reject) {
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
                                        pendingCoverFile = null;
                                        if (coverCropper) {
                                            coverCropper.destroy();
                                            coverCropper = null;
                                        }
                                        resolve();
                                    })
                                    .catch(reject);
                            }, 'image/jpeg', 0.9);
                        } else {
                            resolve();
                        }
                    });
                }
                return Promise.resolve();
            }
            
            // Desabilitar botão durante salvamento
            saveBtn.disabled = true;
            saveBtn.textContent = 'Salvando...';
            
            // Executar todos os uploads e depois salvar os dados
            uploadAvatarIfPending()
                .then(uploadCoverIfPending)
                .then(saveTextAndAlbums)
                .catch(function(err) { 
                    alert(err.message);
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Salvar';
                });
        });
    }
    
    // Fechar dropdown de álbuns ao clicar fora
    document.addEventListener('click', function(e) {
        if (albumSearchResults && !albumSearchResults.contains(e.target) && e.target !== albumSearchInput) {
            albumSearchResults.classList.remove('show');
        }
    });
})();
</script>
