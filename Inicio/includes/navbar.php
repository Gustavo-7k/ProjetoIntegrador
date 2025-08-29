<?php
$base_path = isset($base_path) ? $base_path : '';
$active_page = isset($active_page) ? $active_page : '';
$is_admin = isset($is_admin) && $is_admin;
?>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container">
        <a class="navbar-brand navbar-brand-custom" href="<?php echo $base_path; ?>inicio.php">
            <img src="<?php echo $base_path; ?>img/discosemfundo.png" alt="Logo" class="navbar-brand-logo">
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
                           href="<?php echo $base_path; ?>adm/ADMdenuncias.php">Denúncias</a>
                    </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_page === 'inicio' ? 'active' : ''; ?>" 
                       href="<?php echo $base_path; ?>inicio.php">Inicio</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_page === 'conexoes' ? 'active' : ''; ?>" 
                       href="<?php echo $base_path; ?>comentarios/VerComentariosConexoes.php">Conexões</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_page === 'novo_comentario' ? 'active' : ''; ?>" 
                       href="<?php echo $base_path; ?>comentarios/novocomentario.php">Novo Comentário</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_page === 'perfil' ? 'active' : ''; ?>" 
                       href="<?php echo $base_path; ?>perfil/perfil.php">Usuario</a>
                </li>
                
                <!-- Chat Icon -->
                <li class="nav-item dropdown">
                    <a class="nav-link nav-chat-icon" href="#" id="open-chat-sidebar" role="button">
                        <img src="<?php echo $base_path; ?>img/chaticon.png" alt="Chat" class="nav-icon">
                    </a>
                </li>
                
                <!-- Notifications Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo $base_path; ?>img/notificacaoicon.png" alt="Notificações" class="nav-icon nav-notification-icon">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                        <div class="notification-header">
                            <h6 class="mb-0">Notificações</h6>
                        </div>
                        <div class="notification-content">
                            <p class="text-muted mb-0">Você não tem notificações</p>
                        </div>
                        <div class="notification-footer">
                            <a href="<?php echo $base_path; ?>notificacoes/todasnotificacoes.php" class="text-primary">Ver todas</a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
