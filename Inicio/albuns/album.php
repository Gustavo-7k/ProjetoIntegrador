<?php
require_once __DIR__ . '/../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die('Álbum inválido');
}

$pageTitle = 'Álbum';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Medula+One&display=swap" rel="stylesheet">
    <link href="/css/estilos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@2.4.0/dist/purify.min.js"></script>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="album-page">
    <div class="container">
        <!-- Botão Voltar -->
        <a href="javascript:history.back()" class="back-btn">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            Voltar
        </a>

        <!-- Informações do Álbum -->
        <section class="album-header" id="album-info">
            <div class="album-loading">Carregando álbum...</div>
        </section>

        <!-- Seção de Avaliações -->
        <section class="reviews-section">
            <h3 class="section-title">
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M2.678 11.894a1 1 0 0 1 .287.801 10.97 10.97 0 0 1-.398 2c1.395-.323 2.247-.697 2.634-.893a1 1 0 0 1 .71-.074A8.06 8.06 0 0 0 8 14c3.996 0 7-2.807 7-6 0-3.192-3.004-6-7-6S1 4.808 1 8c0 1.468.617 2.83 1.678 3.894zm-.493 3.905a21.682 21.682 0 0 1-.713.129c-.2.032-.352-.176-.273-.362a9.68 9.68 0 0 0 .244-.637l.003-.01c.248-.72.45-1.548.524-2.319C.743 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7-3.582 7-8 7a9.06 9.06 0 0 0-2.347-.306c-.52.263-1.639.742-3.468 1.105z"/>
                </svg>
                Avaliações e Comentários
            </h3>

            <!-- Formulário de Comentário -->
            <div class="comment-form-container">
                <?php if (isLoggedIn()): ?>
                <div class="comment-form">
                    <h5 class="form-title">Deixe sua avaliação</h5>
                    
                    <div class="rating-container">
                        <label class="rating-label">Sua nota:</label>
                        <div class="star-rating" id="star-rating">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                        <input type="hidden" id="rating-select" value="">
                    </div>
                    
                    <textarea id="comment-text" class="comment-textarea" rows="4" placeholder="Escreva seu comentário... (Markdown suportado)"></textarea>
                    
                    <button id="submit-comment" class="submit-btn">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z"/>
                        </svg>
                        Enviar comentário
                    </button>
                </div>
                <?php else: ?>
                <div class="login-prompt">
                    <svg width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                        <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                    </svg>
                    <p>Faça <a href="/login/login.php">login</a> para deixar sua avaliação</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Lista de Comentários -->
            <div id="comments-list" class="comments-list">
                <div class="comments-loading">Carregando comentários...</div>
            </div>
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
    star.addEventListener('mouseover', function() {
        const value = this.dataset.value;
        document.querySelectorAll('#star-rating .star').forEach((s, i) => {
            s.classList.toggle('hover', i < value);
        });
    });
    star.addEventListener('mouseout', function() {
        document.querySelectorAll('#star-rating .star').forEach(s => s.classList.remove('hover'));
    });
});

async function fetchAlbum(){
    const res = await fetch('/api/get-album.php?id=' + albumId);
    const json = await res.json();
    if (!json.success) return alert(json.message || 'Erro');
    const a = json.album;
    const coverImg = a.cover_image ? `/img/${escapeHtml(a.cover_image)}` : '/img/NTHMS.png';
    document.getElementById('album-info').innerHTML = `
        <div class="album-header-content">
            <div class="album-cover-large">
                <img src="${coverImg}" alt="${escapeHtml(a.title)}">
            </div>
            <div class="album-details">
                <h1 class="album-title-large">${escapeHtml(a.title)}</h1>
                <h2 class="album-artist-large">${escapeHtml(a.artist)}</h2>
                <div class="album-meta">
                    <span class="meta-item">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M6 13c0 1.105-1.12 2-2.5 2S1 14.105 1 13c0-1.104 1.12-2 2.5-2s2.5.896 2.5 2zm9-2c0 1.105-1.12 2-2.5 2s-2.5-.895-2.5-2 1.12-2 2.5-2 2.5.895 2.5 2z"/>
                            <path fill-rule="evenodd" d="M14 11V2h1v9h-1zM6 3v10H5V3h1z"/>
                            <path d="M5 2.905a1 1 0 0 1 .9-.995l8-.8a1 1 0 0 1 1.1.995V3L5 4V2.905z"/>
                        </svg>
                        ${escapeHtml(a.genre || 'Sem gênero')}
                    </span>
                    <span class="meta-item">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                        </svg>
                        ${escapeHtml(a.release_year || 'Ano desconhecido')}
                    </span>
                </div>
                <div class="album-stats">
                    <div class="stat-box">
                        <span class="stat-value">${a.average_rating || '0.0'}</span>
                        <span class="stat-label">Média</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-value">${a.total_comments || 0}</span>
                        <span class="stat-label">Reviews</span>
                    </div>
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
        container.innerHTML = '<div class="no-comments"><p>Nenhum comentário ainda. Seja o primeiro a avaliar!</p></div>';
        return;
    }
    container.innerHTML = '';
    json.comments.forEach(c => container.appendChild(renderComment(c)));
}

function renderComment(c){
    const div = document.createElement('div');
    div.className = 'comment-card';
    const avatarUrl = c.profile_image ? escapeHtml(c.profile_image) : '/img/default-avatar.png';
    const md = DOMPurify.sanitize(marked.parse(c.comment));
    const rating = c.rating ? '★'.repeat(c.rating) + '☆'.repeat(5-c.rating) : '';
    
    div.innerHTML = `
        <div class="comment-header">
            <div class="comment-avatar">
                <img src="${avatarUrl}" alt="${escapeHtml(c.username)}" onerror="this.src='/img/default-avatar.png'">
            </div>
            <div class="comment-user-info">
                <strong class="comment-username">${escapeHtml(c.username)}</strong>
                <span class="comment-date">${new Date(c.created_at).toLocaleDateString('pt-BR', {day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'})}</span>
            </div>
            ${rating ? `<div class="comment-rating">${rating}</div>` : ''}
        </div>
        <div class="comment-body">${md}</div>
        <div class="comment-actions">
            <button data-id="${c.id}" class="action-btn like-comment">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8.864.046C7.908-.193 7.02.53 6.956 1.466c-.072 1.051-.23 2.016-.428 2.59-.125.36-.479 1.013-1.04 1.639-.557.623-1.282 1.178-2.131 1.41C2.685 7.288 2 7.87 2 8.72v4.001c0 .845.682 1.464 1.448 1.545 1.07.114 1.564.415 2.068.723l.048.03c.272.165.578.348.97.484.397.136.861.217 1.466.217h3.5c.937 0 1.599-.477 1.934-1.064a1.86 1.86 0 0 0 .254-.912c0-.152-.023-.312-.077-.464.201-.263.38-.578.488-.901.11-.33.172-.762.004-1.149.069-.13.12-.269.159-.403.077-.27.113-.568.113-.857 0-.288-.036-.585-.113-.856a2.144 2.144 0 0 0-.138-.362 1.9 1.9 0 0 0 .234-1.734c-.206-.592-.682-1.1-1.2-1.272-.847-.282-1.803-.276-2.516-.211a9.84 9.84 0 0 0-.443.05 9.365 9.365 0 0 0-.062-4.509A1.38 1.38 0 0 0 9.125.111L8.864.046z"/>
                </svg>
                <span>${c.likes_count||0}</span>
            </button>
            <button data-id="${c.id}" class="action-btn reply-comment">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M5.921 11.9 1.353 8.62a.719.719 0 0 1 0-1.238L5.921 4.1A.716.716 0 0 1 7 4.719V6c1.5 0 6 0 7 8-2.5-4.5-7-4-7-4v1.281c0 .56-.606.898-1.079.62z"/>
                </svg>
                Responder
            </button>
            <button data-id="${c.id}" class="action-btn report-comment">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
                    <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
                </svg>
                Denunciar
            </button>
        </div>
    `;
    
    if (c.replies && c.replies.length){
        const repliesContainer = document.createElement('div');
        repliesContainer.className = 'replies-container';
        c.replies.forEach(r => {
            const child = renderComment(r);
            child.classList.add('reply');
            repliesContainer.appendChild(child);
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
</body>
</html>
