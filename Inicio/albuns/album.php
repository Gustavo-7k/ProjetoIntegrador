<?php
require_once __DIR__ . '/../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die('Álbum inválido');
}

// Configurações da página para o header.php
$page_title = 'NTHMS - Anthems | Álbum';
$active_page = 'album';
$base_path = '../';

// Scripts adicionais
$additional_js = [
    'https://cdn.jsdelivr.net/npm/marked/marked.min.js',
    'https://cdn.jsdelivr.net/npm/dompurify@2.4.0/dist/purify.min.js'
];

// Incluir header (já inclui DOCTYPE, html, head, body)
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<main class="album-page">
    <div class="container">
        <!-- Informações do Álbum -->
        <section class="album-info-section" id="album-info">
            <div class="album-loading">Carregando álbum...</div>
        </section>

        <!-- Seção de Comentários -->
        <section class="comments-section">
            <h2 class="comments-title">comentarios</h2>

            <!-- Formulário de Comentário -->
            <?php if (isLoggedIn()): ?>
            <div class="comment-form">
                <div class="rating-row">
                    <label>Sua nota:</label>
                    <div class="star-rating" id="star-rating">
                        <span class="star" data-value="1">★</span>
                        <span class="star" data-value="2">★</span>
                        <span class="star" data-value="3">★</span>
                        <span class="star" data-value="4">★</span>
                        <span class="star" data-value="5">★</span>
                    </div>
                    <input type="hidden" id="rating-select" value="">
                </div>
                
                <textarea id="comment-text" class="comment-input" rows="3" placeholder="Escreva seu comentário..."></textarea>
                
                <button id="submit-comment" class="submit-btn">Enviar</button>
            </div>
            <?php else: ?>
            <p class="login-msg">Faça <a href="/login/login.php">login</a> para comentar.</p>
            <?php endif; ?>

            <!-- Lista de Comentários -->
            <div id="comments-list" class="comments-list"></div>
        </section>
    </div>
</main>

<script>
const albumId = <?= $id ?>;

// Star Rating
document.querySelectorAll('#star-rating .star').forEach(star => {
    star.addEventListener('click', function() {
        const value = this.dataset.value;
        document.getElementById('rating-select').value = value;
        document.querySelectorAll('#star-rating .star').forEach((s, i) => {
            s.classList.toggle('active', i < value);
        });
    });
});

async function fetchAlbum(){
    const res = await fetch('/api/get-album.php?id=' + albumId);
    const json = await res.json();
    if (!json.success) return alert(json.message || 'Erro');
    const a = json.album;
    const coverImg = a.cover_image ? `/img/${escapeHtml(a.cover_image)}` : '/img/NTHMS.png';
    document.getElementById('album-info').innerHTML = `
        <div class="album-layout">
            <div class="album-cover">
                <img src="${coverImg}" alt="${escapeHtml(a.title)}">
            </div>
            <div class="album-details">
                <h1 class="album-title">${escapeHtml(a.title)}</h1>
                <p class="album-artist">${escapeHtml(a.artist)}</p>
                <p class="album-genre">${escapeHtml(a.genre || '')}</p>
                <p class="album-desc">Descrição do album</p>
                <div class="album-media">
                    <span class="media-label">media</span>
                    <span class="media-value">${a.average_rating || '0.0'}</span>
                </div>
            </div>
        </div>
    `;
}

function escapeHtml(s){ return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[c]); }

async function fetchComments(){
    const res = await fetch('/api/get-comments.php?album_id=' + albumId);
    const json = await res.json();
    if (!json.success) return;
    const container = document.getElementById('comments-list');
    if (json.comments.length === 0) {
        container.innerHTML = '<p class="no-comments">Nenhum comentário ainda.</p>';
        return;
    }
    container.innerHTML = '';
    json.comments.forEach(c => container.appendChild(renderComment(c)));
}

function renderComment(c){
    const div = document.createElement('div');
    div.className = 'comment-item';
    const defaultAvatar = 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50" fill="#c29fef"/><circle cx="50" cy="40" r="18" fill="#170045"/><ellipse cx="50" cy="85" rx="30" ry="25" fill="#170045"/></svg>');
    const avatarUrl = c.profile_image ? escapeHtml(c.profile_image) : defaultAvatar;
    const md = DOMPurify.sanitize(marked.parse(c.comment));
    const rating = c.rating ? '★'.repeat(c.rating) + '☆'.repeat(5-c.rating) : '';
    
    div.innerHTML = `
        <div class="comment-left">
            <img src="${avatarUrl}" alt="${escapeHtml(c.username)}" class="comment-avatar" onerror="this.src='${defaultAvatar}'">
        </div>
        <div class="comment-content">
            <div class="comment-header">
                <strong>${escapeHtml(c.username)}</strong>
                ${rating ? `<span class="comment-stars">${rating}</span>` : ''}
                <span class="comment-date">${new Date(c.created_at).toLocaleDateString('pt-BR')}</span>
            </div>
            <div class="comment-text">${md}</div>
            <div class="comment-actions">
                <button data-id="${c.id}" class="btn-action like-comment">👍 ${c.likes_count||0}</button>
                <button data-id="${c.id}" class="btn-action reply-comment">Responder</button>
                <button data-id="${c.id}" class="btn-action report-comment">Denunciar</button>
            </div>
        </div>
    `;
    
    if (c.replies && c.replies.length){
        const repliesContainer = document.createElement('div');
        repliesContainer.className = 'replies-list';
        c.replies.forEach(r => {
            repliesContainer.appendChild(renderComment(r));
        });
        div.appendChild(repliesContainer);
    }
    return div;
}

document.addEventListener('click', async (ev) => {
    if (ev.target.matches('#submit-comment')){
        const text = document.getElementById('comment-text').value.trim();
        const rating = document.getElementById('rating-select').value;
        if (!text) return alert('Comentário vazio');
        const res = await fetch('/api/post-comment.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({album_id:albumId, comment:text, rating:rating||null})});
        const j = await res.json();
        if (j.success){ 
            document.getElementById('comment-text').value=''; 
            document.getElementById('rating-select').value='';
            document.querySelectorAll('#star-rating .star').forEach(s => s.classList.remove('active'));
            fetchComments(); 
            fetchAlbum(); 
        }
        else alert(j.message||'Erro');
    }

    if (ev.target.closest('.like-comment')){
        const btn = ev.target.closest('.like-comment');
        const id = btn.getAttribute('data-id');
        const res = await fetch('/api/like-comment.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({comment_id:id})});
        const j = await res.json();
        if (j.success) fetchComments();
    }

    if (ev.target.closest('.reply-comment')){
        const btn = ev.target.closest('.reply-comment');
        const id = btn.getAttribute('data-id');
        const txt = prompt('Escreva sua resposta:');
        if (!txt) return;
        const res = await fetch('/api/post-comment.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({album_id:albumId, comment:txt, parent_id: id})});
        const j = await res.json(); if (j.success) fetchComments(); else alert(j.message||'Erro');
    }

    if (ev.target.closest('.report-comment')){
        const btn = ev.target.closest('.report-comment');
        const id = btn.getAttribute('data-id');
        const motivo = prompt('Motivo da denúncia:');
        if (!motivo) return;
        const res = await fetch('/api/report-comment.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({comment_id:id, reason:motivo})});
        const j = await res.json(); if (j.success) alert('Denúncia enviada'); else alert(j.message||'Erro');
    }
});

fetchAlbum();
fetchComments();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
