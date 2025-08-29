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
                                        <h4 class="login-title">Entre na sua conta!</h4>
                                    </div>
                                    
                                    <!-- Login com Google -->
                                    <div class="d-grid mb-4">
                                        <button type="button" class="btn google-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-google me-2" viewBox="0 0 16 16">
                                                <path d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z" />
                                            </svg>
                                            Entrar com Google
                                        </button>
                                    </div>
                                    
                                    <p class="text-center mb-4">Ou entre com</p>
                                    
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
