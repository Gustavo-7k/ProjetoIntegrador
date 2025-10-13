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

// Dados default para visitantes
$user_profile = [
    'name' => $current ? ($current['full_name'] ?: $current['username']) : 'Convidado',
    'bio' => $current ? ($current['bio'] ?: 'Sem bio ainda.') : 'Faça login para ver e editar seu perfil.',
    'connections' => 0,
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
            
            <div class="d-flex align-items-center gap-2">
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
                    <input type="text" class="form-control connection-input" placeholder="Digite o nome">
                    <button class="btn connection-submit">Enviar</button>
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
            <div class="albums-grid">
                <?php foreach ($liked_albums as $album): ?>
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

        <!-- Artistas favoritos -->
        <div class="albums-section">
            <h3>Artistas Favoritos</h3>
            <div class="albums-grid">
                <?php foreach ($favorite_artists as $artist): ?>
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
    </div>
</div>

<!-- Modal de Edição -->
<div id="edit-modal" class="edit-modal">
    <div class="edit-modal-content">
        <h3>Editar Perfil</h3>
        
        <form id="profile-edit-form" action="../perfil/update_profile.php" method="POST" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? generateCSRFToken()); ?>">
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
    function csrf() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : (document.querySelector('input[name="csrf_token"]').value || '');
    }

    const avatarInput = document.getElementById('upload-avatar');
    const coverInput = document.getElementById('upload-cover');
    const avatarPreview = document.getElementById('avatar-preview');
    const coverPreview = document.getElementById('cover-crop-preview');
    const profilePic = document.querySelector('.profile-picture');
    const coverPhoto = document.querySelector('.cover-photo');

    function uploadFile(url, fieldName, file) {
        const fd = new FormData();
        fd.append(fieldName, file);
        fd.append('csrf_token', csrf());
        return fetch(url, { method: 'POST', body: fd }).then(async (r)=>{
            const data = await r.json().catch(()=>({}));
            if (!r.ok || !data.ok) throw new Error(data.error || 'Falha no upload');
            return data.path;
        });
    }

    if (avatarInput) {
        avatarInput.addEventListener('change', function(){
            const f = this.files && this.files[0];
            if (!f) return;
            // Preview local
            const reader = new FileReader();
            reader.onload = function(e){
                if (avatarPreview) avatarPreview.style.backgroundImage = 'url(' + e.target.result + ')';
            };
            reader.readAsDataURL(f);

            uploadFile('../perfil/upload_avatar.php', 'avatar', f)
                .then(function(path){
                    if (profilePic) profilePic.style.backgroundImage = 'url(' + ('../' + path) + ')';
                })
                .catch(function(err){ alert(err.message); });
        });
    }

    if (coverInput) {
        coverInput.addEventListener('change', function(){
            const f = this.files && this.files[0];
            if (!f) return;
            // Preview local
            const reader = new FileReader();
            reader.onload = function(e){
                if (coverPreview) coverPreview.src = e.target.result;
            };
            reader.readAsDataURL(f);

            uploadFile('../perfil/upload_cover.php', 'cover', f)
                .then(function(path){
                    if (coverPhoto) coverPhoto.style.backgroundImage = 'url(' + ('../' + path) + ')';
                })
                .catch(function(err){ alert(err.message); });
        });
    }
})();
</script>
