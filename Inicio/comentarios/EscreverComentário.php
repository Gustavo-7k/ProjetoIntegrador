<?php
require_once __DIR__ . '/../config.php';
// Rota antiga - redireciona para a página central de álbum (In Rainbows -> id=1)
redirectTo(APP_URL . 'albuns/album.php?id=1');
exit;

// Conteúdo antigo mantido como referência (removido)
?>
$php_redirect = true;
require_once __DIR__ . '/../config.php';
// Redirecionar para a página de álbum centralizada
redirectTo(APP_URL . 'albuns/album.php?id=1');
// Se o redirecionamento falhar, continuar para a versão antiga do formulário
if ($php_redirect) {
    exit;
}

<?php
// Configurações da página
$page_title = "NTHMS - Anthems | Escrever Comentário - In Rainbows";
$active_page = "novo_comentario";
$base_path = "../";

// CSS adicional para Font Awesome (estrelas)
$additional_css = [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
];

// JavaScript adicional para jQuery
$additional_js = [
    'https://code.jquery.com/jquery-3.6.0.min.js'
];

// Dados do álbum (em um sistema real, viria do banco de dados baseado no ID)
$album_data = [
    'title' => 'IN RAINBOWS',
    'artist' => 'RADIOHEAD',
    'cover_image' => 'InRainbows.jpeg',
    'id' => 1
];

// CSS específico para esta página
$inline_css = '
.content-box {
    background: white;
    border-radius: 15px;
    padding: 30px;
    margin: 30px 0;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.album-cover {
    width: 100%;
    max-width: 300px;
    height: auto;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}

.album-title {
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 5px;
}

.artist-name {
    font-size: 1.2rem;
    color: var(--text-gray);
    margin-bottom: 30px;
}

.review-textarea {
    min-height: 200px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 15px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.review-textarea:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(23, 0, 69, 0.25);
}

.btn-post {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-post:hover {
    background-color: #0f0035;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(23, 0, 69, 0.3);
    color: white;
}

/* Star Rating System */
.stars-container {
    margin: 20px 0 30px 0;
    text-align: center;
}

.stars {
    display: flex;
    justify-content: center;
    gap: 10px;
}

.rate {
    cursor: pointer;
    position: relative;
}

.rate input {
    display: none;
}

.star {
    font-size: 2rem;
    color: #ddd;
    transition: color 0.3s ease;
}

.star-over,
.star.fas {
    color: #ffc107;
}

.rate-active {
    color: #ffc107 !important;
}

.face {
    position: absolute;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    top: -5px;
    left: -5px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.rate:hover .face {
    opacity: 1;
}

@media (max-width: 768px) {
    .album-title {
        font-size: 1.8rem;
        text-align: center;
    }
    
    .artist-name {
        text-align: center;
    }
    
    .album-cover {
        max-width: 250px;
        margin: 0 auto;
        display: block;
    }
    
    .stars {
        gap: 5px;
    }
    
    .star {
        font-size: 1.5rem;
    }
}
';

// Incluir header
include '../includes/header.php';
?>

<?php include '../includes/navbar.php'; ?>

<!-- Conteúdo Principal -->
<div class="container">
    <div class="content-box">
        <div class="row">
            <!-- Capa do álbum (esquerda) -->
            <div class="col-md-5 mb-4 mb-md-0 text-center">
                <img src="../img/<?php echo htmlspecialchars($album_data['cover_image']); ?>" 
                     alt="Capa do <?php echo htmlspecialchars($album_data['title']); ?>" 
                     class="album-cover">
            </div>
            
            <!-- Área de formulário (direita) -->
            <div class="col-md-7">
                <div class="album-title"><?php echo htmlspecialchars($album_data['title']); ?></div>
                <div class="artist-name"><?php echo htmlspecialchars($album_data['artist']); ?></div>
                <h3 class="mb-4">Desenvolva Seu Comentário</h3>
                
                <!-- Sistema de Avaliação com Estrelas -->
                <div class="stars-container">
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="rate">
                                <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>">
                                <div class="face"></div>
                                <i class="far fa-star star"></i>
                            </label>
                        <?php endfor; ?>
                    </div>
                    <div id="rating-text" class="mt-2 text-muted"></div>
                </div>
                
                <!-- Formulário de Comentário -->
                <form id="comment-form" method="POST" action="submit_comment.php" data-validate>
                    <input type="hidden" name="album_id" value="<?php echo $album_data['id']; ?>">
                    <input type="hidden" name="rating" id="rating-value" value="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="mb-4">
                        <textarea name="comment" class="form-control review-textarea" 
                                  placeholder="Digite sua resenha sobre o álbum..." 
                                  required minlength="10" maxlength="1000"></textarea>
                        <div class="form-text">Mínimo 10 caracteres, máximo 1000.</div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-outline-secondary me-md-2" onclick="history.back()">Voltar</button>
                        <button type="submit" class="btn btn-post">Publicar Comentário</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/chat-sidebar.php'; ?>

<?php
// JavaScript específico para esta página
$inline_js = '
// Textos para cada avaliação
const ratingTexts = {
    1: "⭐ Não gostei",
    2: "⭐⭐ Fraco",
    3: "⭐⭐⭐ Bom", 
    4: "⭐⭐⭐⭐ Muito bom",
    5: "⭐⭐⭐⭐⭐ Excelente"
};

$(document).ready(function() {
    // Sistema de avaliação com estrelas
    $(document).on({
        mouseover: function(event) {
            $(this).find(".far").addClass("star-over");
            $(this).prevAll().find(".far").addClass("star-over");
        },
        mouseleave: function(event) {
            $(this).find(".far").removeClass("star-over");
            $(this).prevAll().find(".far").removeClass("star-over");
        }
    }, ".rate");

    $(document).on("click", ".rate", function() {
        const rating = $(this).find("input").val();
        
        if (!$(this).find(".star").hasClass("rate-active")) {
            // Remove seleção anterior
            $(this).siblings().find(".star").addClass("far").removeClass("fas rate-active");
            
            // Adiciona nova seleção
            $(this).find(".star").addClass("rate-active fas").removeClass("far star-over");
            $(this).prevAll().find(".star").addClass("fas").removeClass("far star-over");
            
            // Atualiza valor hidden e texto
            $("#rating-value").val(rating);
            $("#rating-text").text(ratingTexts[rating]);
        }
    });
    
    // Validação do formulário
    $("#comment-form").on("submit", function(e) {
        const rating = $("#rating-value").val();
        const comment = $("textarea[name=comment]").val().trim();
        
        if (!rating) {
            e.preventDefault();
            alert("Por favor, selecione uma avaliação com estrelas.");
            return false;
        }
        
        if (comment.length < 10) {
            e.preventDefault();
            alert("O comentário deve ter pelo menos 10 caracteres.");
            return false;
        }
        
        // Desabilitar botão durante submissão
        $(this).find("button[type=submit]").prop("disabled", true).text("Publicando...");
    });
    
    // Contador de caracteres
    const textarea = $("textarea[name=comment]");
    const maxLength = textarea.attr("maxlength");
    
    $("<div>").addClass("character-counter text-muted small text-end mt-1")
              .text("0/" + maxLength)
              .insertAfter(textarea);
    
    textarea.on("input", function() {
        const currentLength = $(this).val().length;
        const counter = $(this).siblings(".character-counter");
        counter.text(currentLength + "/" + maxLength);
        
        if (currentLength > maxLength * 0.9) {
            counter.addClass("text-warning");
        } else {
            counter.removeClass("text-warning");
        }
        
        if (currentLength === maxLength) {
            counter.addClass("text-danger").removeClass("text-warning");
        } else {
            counter.removeClass("text-danger");
        }
    });
});
';

include '../includes/footer.php';
?>
