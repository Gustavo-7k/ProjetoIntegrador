<?php
require_once __DIR__ . '/../config.php';
$base_path = isset($base_path) ? $base_path : '';
$active_page = isset($active_page) ? $active_page : '';
$is_admin = isset($is_admin) && $is_admin;
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
