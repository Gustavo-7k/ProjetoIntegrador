<?php
// Configurações da página
$page_title = "NTHMS - Anthems | Login";
$active_page = "login";
$base_path = "../";

// CSS adicional específico para login
$additional_css = [];

// Incluir header
include '../includes/header.php';
?>

<?php include '../includes/navbar.php'; ?>

<!-- Conteúdo de Login -->
<section class="login-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-xxl-11">
                <div class="card border-light-subtle shadow-sm login-card">
                    <div class="row g-0">
                        <div class="col-12 col-md-6">
                            <img class="img-fluid rounded-start w-100 h-100 object-fit-cover login-image" 
                                 loading="lazy" src="../img/Ultraviolence.jpeg" 
                                 alt="Welcome back you've been missed!">
                        </div>
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center">
                            <div class="col-12 col-lg-11 col-xl-10">
                                <div class="login-content">
                                    <div class="text-center mb-4">
                                        <img src="../img/NTHMSnavcon.png" alt="Anthems Logo" class="login-logo">
                                    </div>
                                    
                                    <p class="text-center mb-4">Insira suas credenciais</p>
                                    
                                    <!-- Formulário de Login -->
                                    <form action="login_process.php" method="POST" data-validate data-ajax>
                                        <div class="mb-3">
                                            <div class="form-floating">
                                                <input type="email" class="form-control" name="email" id="email" 
                                                       placeholder="name@example.com" required>
                                                <label for="email">Email</label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-floating">
                                                <input type="password" class="form-control" name="password" id="password" 
                                                       placeholder="Password" required>
                                                <label for="password">Senha</label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="1" 
                                                       name="remember_me" id="remember_me">
                                                <label class="form-check-label text-secondary" for="remember_me">
                                                    Me mantenha conectado
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="d-grid mb-4">
                                            <button type="submit" class="btn login-btn">Entrar Agora</button>
                                        </div>
                                    </form>
                                    
                                    <!-- Links -->
                                    <div class="login-links d-flex gap-2 gap-md-4 flex-column flex-md-row justify-content-md-center">
                                        <a href="novologin.php">Criar Nova Conta</a>
                                        <a href="novasenha.php">Esqueci a Senha</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
