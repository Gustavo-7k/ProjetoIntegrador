<?php
// Configurações da página
$page_title = "NTHMS - Anthems | Criar Conta";
$active_page = "registro";
$base_path = "../";

// Incluir header
include '../includes/header.php';
?>

<?php include '../includes/navbar.php'; ?>

<!-- Conteúdo de Registro -->
<section class="login-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-xxl-11">
                <div class="card border-light-subtle shadow-sm login-card">
                    <div class="row g-0">
                        <div class="col-12 col-md-6">
                            <img class="img-fluid rounded-start w-100 h-100 object-fit-cover login-image" 
                                 loading="lazy" src="../img/HQCM.jpeg" 
                                 alt="Junte-se à nossa comunidade musical!">
                        </div>
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center">
                            <div class="col-12 col-lg-11 col-xl-10">
                                <div class="login-content">
                                    <div class="text-center mb-4">
                                        <img src="../img/NTHMSnavcon.png" alt="Anthems Logo" class="login-logo">
                                        <h4 class="login-title">Criar Nova Conta</h4>
                                        <p class="text-muted">Junte-se à nossa comunidade musical</p>
                                    </div>
                                    
                                    <!-- Registro com Google -->
                                    <div class="d-grid mb-4">
                                        <button type="button" class="btn google-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-google me-2" viewBox="0 0 16 16">
                                                <path d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z" />
                                            </svg>
                                            Registrar com Google
                                        </button>
                                    </div>
                                    
                                    <p class="text-center mb-4">Ou registre-se com</p>
                                    
                                    <!-- Formulário de Registro -->
                                    <form action="register_process.php" method="POST" data-validate id="register-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        
                                        <div class="mb-3">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" name="username" id="username" 
                                                       placeholder="Nome de usuário" required minlength="3" maxlength="30"
                                                       pattern="^[a-zA-Z0-9_]+$">
                                                <label for="username">Nome de Usuário</label>
                                            </div>
                                            <div class="form-text">
                                                Apenas letras, números e underscore. 3-30 caracteres.
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" name="full_name" id="full_name" 
                                                       placeholder="Nome completo" required minlength="2" maxlength="100">
                                                <label for="full_name">Nome Completo</label>
                                            </div>
                                        </div>
                                        
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
                                                       placeholder="Senha" required minlength="6">
                                                <label for="password">Senha</label>
                                            </div>
                                            <div class="form-text">
                                                Mínimo 6 caracteres.
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-floating">
                                                <input type="password" class="form-control" name="password_confirm" id="password_confirm" 
                                                       placeholder="Confirmar senha" required>
                                                <label for="password_confirm">Confirmar Senha</label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="terms_accepted" 
                                                       id="terms_accepted" required>
                                                <label class="form-check-label text-secondary" for="terms_accepted">
                                                    Aceito os <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Termos de Uso</a> 
                                                    e <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Política de Privacidade</a>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="newsletter" id="newsletter">
                                                <label class="form-check-label text-secondary" for="newsletter">
                                                    Quero receber novidades sobre música e lançamentos
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="d-grid mb-4">
                                            <button type="submit" class="btn login-btn">Criar Conta</button>
                                        </div>
                                    </form>
                                    
                                    <!-- Links -->
                                    <div class="login-links text-center">
                                        <p class="mb-0">Já tem uma conta? 
                                            <a href="login.php" class="fw-bold">Faça login</a>
                                        </p>
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

<!-- Modal de Termos de Uso -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel" style="color: var(--primary-color);">Termos de Uso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>1. Aceitação dos Termos</h6>
                <p>Ao usar o Anthems, você concorda com estes termos de uso.</p>
                
                <h6>2. Uso Responsável</h6>
                <p>Você se compromete a usar a plataforma de forma respeitosa e não publicar conteúdo ofensivo.</p>
                
                <h6>3. Propriedade Intelectual</h6>
                <p>Respeite os direitos autorais dos álbuns e músicas comentados.</p>
                
                <h6>4. Privacidade</h6>
                <p>Seus dados pessoais serão protegidos conforme nossa Política de Privacidade.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Política de Privacidade -->
<div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="privacyModalLabel" style="color: var(--primary-color);">Política de Privacidade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Coleta de Dados</h6>
                <p>Coletamos apenas dados necessários para o funcionamento da plataforma.</p>
                
                <h6>Uso dos Dados</h6>
                <p>Seus dados são usados para personalizar sua experiência e melhorar nossos serviços.</p>
                
                <h6>Compartilhamento</h6>
                <p>Não compartilhamos seus dados pessoais com terceiros sem sua autorização.</p>
                
                <h6>Cookies</h6>
                <p>Utilizamos cookies para melhorar a funcionalidade da plataforma.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<?php
// JavaScript específico para validação de formulário
$inline_js = '
document.getElementById("register-form").addEventListener("submit", function(e) {
    const password = document.getElementById("password").value;
    const passwordConfirm = document.getElementById("password_confirm").value;
    
    if (password !== passwordConfirm) {
        e.preventDefault();
        alert("As senhas não coincidem!");
        return false;
    }
    
    const username = document.getElementById("username").value;
    const usernameRegex = /^[a-zA-Z0-9_]+$/;
    
    if (!usernameRegex.test(username)) {
        e.preventDefault();
        alert("Nome de usuário deve conter apenas letras, números e underscore!");
        return false;
    }
});

// Validação em tempo real da confirmação de senha
document.getElementById("password_confirm").addEventListener("input", function() {
    const password = document.getElementById("password").value;
    const passwordConfirm = this.value;
    
    if (passwordConfirm && password !== passwordConfirm) {
        this.setCustomValidity("Senhas não coincidem");
        this.classList.add("is-invalid");
    } else {
        this.setCustomValidity("");
        this.classList.remove("is-invalid");
        if (passwordConfirm) this.classList.add("is-valid");
    }
});

// Validação do nome de usuário em tempo real
document.getElementById("username").addEventListener("input", function() {
    const username = this.value;
    const usernameRegex = /^[a-zA-Z0-9_]+$/;
    
    if (username && !usernameRegex.test(username)) {
        this.setCustomValidity("Use apenas letras, números e underscore");
        this.classList.add("is-invalid");
    } else {
        this.setCustomValidity("");
        this.classList.remove("is-invalid");
        if (username.length >= 3) this.classList.add("is-valid");
    }
});
';

include '../includes/footer.php';
?>
