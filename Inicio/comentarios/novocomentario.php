<?php
require_once __DIR__ . '/../config.php';
requireAuth();

// Configurações da página
$page_title = "NTHMS - Anthems | Novo Comentário";
$active_page = "novo_comentario";
$base_path = "../";

// CSS específico para esta página
$inline_css = '
.novo-comentario-page {
    padding-top: 100px;
    padding-bottom: 40px;
    min-height: 100vh;
    background-color: #f5f5f5;
}

.search-container {
    max-width: 600px;
    margin: 0 auto 30px;
    text-align: center;
    padding: 0 20px;
}

.search-container h2 {
    color: var(--primary-color);
    margin-bottom: 10px;
}

.search-container p {
    color: #666;
    margin-bottom: 20px;
}

.search-input-wrapper {
    position: relative;
}

.search-input {
    width: 100%;
    padding: 15px 20px 15px 50px;
    font-size: 1.1rem;
    border: 2px solid var(--border-gray);
    border-radius: 30px;
    outline: none;
    transition: border-color 0.3s, box-shadow 0.3s;
}

.search-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(23, 0, 69, 0.1);
}

.search-icon {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
}

.albums-count {
    text-align: center;
    color: #666;
    margin-bottom: 20px;
    font-size: 0.95rem;
}

.albums-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.album-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: transform 0.3s, box-shadow 0.3s;
    text-decoration: none;
    color: inherit;
    display: block;
}

.album-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.album-card-cover {
    width: 100%;
    aspect-ratio: 1 / 1;
    background-size: cover;
    background-position: center;
    background-color: var(--primary-light);
    display: flex;
    align-items: center;
    justify-content: center;
}

.album-card-cover svg {
    opacity: 0.5;
}

.album-card-info {
    padding: 12px 15px;
}

.album-card-title {
    font-weight: 600;
    color: var(--primary-color);
    font-size: 0.95rem;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.album-card-artist {
    color: #666;
    font-size: 0.85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.album-card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
    font-size: 0.8rem;
    color: #999;
}

.album-card-rating {
    display: flex;
    align-items: center;
    gap: 4px;
    color: #f5a623;
}

.loading-indicator {
    text-align: center;
    padding: 40px;
    color: #666;
}

.loading-indicator .spinner-border {
    color: var(--primary-color);
}

.no-results {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-results svg {
    margin-bottom: 20px;
    opacity: 0.5;
}

.no-results h4 {
    color: var(--primary-color);
    margin-bottom: 10px;
}

@media (max-width: 992px) {
    .albums-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .albums-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .search-input {
        font-size: 1rem;
        padding: 12px 15px 12px 45px;
    }
}

@media (max-width: 480px) {
    .albums-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        padding: 0 10px;
    }
    
    .album-card-info {
        padding: 10px;
    }
    
    .album-card-title {
        font-size: 0.85rem;
    }
    
    .album-card-artist {
        font-size: 0.75rem;
    }
}
';

// Incluir header
include '../includes/header.php';
?>

<?php include '../includes/navbar.php'; ?>

<main class="novo-comentario-page">
    <div class="search-container">
        <h2>Novo Comentário</h2>
        <p>Procure o álbum que gostaria de comentar</p>
        
        <div class="search-input-wrapper">
            <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <input type="text" 
                   id="searchAlbum" 
                   class="search-input" 
                   placeholder="Digite o nome do álbum ou artista..."
                   autocomplete="off">
        </div>
    </div>
    
    <div class="albums-count" id="albumsCount"></div>
    
    <div class="albums-grid" id="albumsGrid">
        <!-- Álbuns serão carregados via AJAX -->
    </div>
    
    <div class="loading-indicator" id="loadingIndicator" style="display: none;">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2">Carregando álbuns...</p>
    </div>
    
    <div class="no-results" id="noResults" style="display: none;">
        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M12 6v6l4 2"></path>
        </svg>
        <h4>Nenhum álbum encontrado</h4>
        <p>Tente buscar com outros termos ou adicione um novo álbum.</p>
    </div>
</main>

<?php include '../includes/chat-sidebar.php'; ?>

<?php 
$inline_js = '
(function() {
    const searchInput = document.getElementById("searchAlbum");
    const albumsGrid = document.getElementById("albumsGrid");
    const albumsCount = document.getElementById("albumsCount");
    const loadingIndicator = document.getElementById("loadingIndicator");
    const noResults = document.getElementById("noResults");
    
    let searchTimeout = null;
    let currentQuery = "";
    
    function createAlbumCard(album) {
        const coverStyle = album.cover_url 
            ? "background-image: url(\'" + album.cover_url + "\');" 
            : "";
        
        const coverContent = album.cover_url 
            ? "" 
            : \'<svg width="50" height="50" fill="white" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 14.5c-2.49 0-4.5-2.01-4.5-4.5S9.51 7.5 12 7.5s4.5 2.01 4.5 4.5-2.01 4.5-4.5 4.5zm0-5.5c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1z"/></svg>\';
        
        const rating = album.average_rating ? parseFloat(album.average_rating).toFixed(1) : "—";
        const year = album.release_year || "";
        
        return \'<a href="/albuns/album.php?id=\' + album.id + \'" class="album-card">\' +
            \'<div class="album-card-cover" style="\' + coverStyle + \'">\' + coverContent + \'</div>\' +
            \'<div class="album-card-info">\' +
                \'<div class="album-card-title" title="\' + escapeHtml(album.title) + \'">\' + escapeHtml(album.title) + \'</div>\' +
                \'<div class="album-card-artist" title="\' + escapeHtml(album.artist) + \'">\' + escapeHtml(album.artist) + \'</div>\' +
                \'<div class="album-card-meta">\' +
                    \'<span>\' + year + \'</span>\' +
                    \'<span class="album-card-rating">\' +
                        \'<svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>\' +
                        rating +
                    \'</span>\' +
                \'</div>\' +
            \'</div>\' +
        \'</a>\';
    }
    
    function escapeHtml(text) {
        if (!text) return "";
        var div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }
    
    function searchAlbums(query) {
        currentQuery = query;
        
        loadingIndicator.style.display = "block";
        albumsGrid.innerHTML = "";
        noResults.style.display = "none";
        albumsCount.textContent = "";
        
        var url = "/api/buscar-albuns.php?q=" + encodeURIComponent(query);
        
        fetch(url, { credentials: "same-origin" })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                loadingIndicator.style.display = "none";
                
                if (data.success && data.albums && data.albums.length > 0) {
                    var html = "";
                    for (var i = 0; i < data.albums.length; i++) {
                        html += createAlbumCard(data.albums[i]);
                    }
                    albumsGrid.innerHTML = html;
                    
                    var countText = query 
                        ? data.total + " álbum(ns) encontrado(s) para \"" + query + "\""
                        : data.total + " álbuns disponíveis";
                    albumsCount.textContent = countText;
                    
                    noResults.style.display = "none";
                } else {
                    albumsGrid.innerHTML = "";
                    albumsCount.textContent = "";
                    noResults.style.display = "block";
                }
            })
            .catch(function(error) {
                console.error("Erro ao buscar álbuns:", error);
                loadingIndicator.style.display = "none";
                noResults.style.display = "block";
            });
    }
    
    searchInput.addEventListener("input", function() {
        var query = this.value.trim();
        
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        searchTimeout = setTimeout(function() {
            searchAlbums(query);
        }, 300);
    });
    
    // Carregar todos os álbuns ao iniciar
    searchAlbums("");
    
    // Focar no campo de busca
    searchInput.focus();
})();
';

include '../includes/footer.php'; 
?>
