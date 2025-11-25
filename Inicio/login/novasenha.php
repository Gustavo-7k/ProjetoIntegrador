<?php
require_once __DIR__ . '/../config.php';

// Processar formulário de recuperação de senha
$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation removed
        $email = sanitizeInput($_POST['email']);
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Aqui você implementaria a lógica de envio do código de recuperação
            // Por exemplo: verificar se o email existe, gerar token, enviar por email
            
            // Simular sucesso para demonstração
            $mensagem = 'Se o email existir em nosso sistema, você receberá um código de recuperação em breve.';
            $tipo_mensagem = 'success';
            
            // Em um sistema real, você faria:
            // 1. Verificar se o email existe no banco
            // 2. Gerar um token único
            // 3. Salvar o token no banco com expiração
            // 4. Enviar email com o token
            
        } else {
            $mensagem = 'Por favor, informe um email válido.';
            $tipo_mensagem = 'error';
        }
    } else {
        $mensagem = 'Token CSRF inválido. Tente novamente.';
        $tipo_mensagem = 'error';
    }
}

$pageTitle = 'Recuperar Senha - Anthems';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" type="image/png" href="../img/NTHMSnavcon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/estilos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Medula+One&display=swap" rel="stylesheet">
    <!-- CSRF meta removed -->
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <!-- Login Section -->
    <section class="p-3 p-md-4 p-xl-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-xxl-11">
                    <div class="card border-light-subtle shadow-sm">
                        <div class="row g-0">
                            <div class="col-12 col-md-6">
                                <img class="img-fluid rounded-start w-100 h-100 object-fit-cover" 
                                     loading="lazy" 
                                     src="../img/dirt.jpeg" 
                                     alt="Recuperar senha">
                            </div>
                            <div class="col-12 col-md-6 d-flex align-items-center justify-content-center">
                                <div class="col-12 col-lg-11 col-xl-10">
                                    <div class="card-body p-3 p-md-4 p-xl-5">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="mb-5">
                                                    <div class="text-center mb-4">
                                                        <a href="../inicio.php">
                                                            <img src="../img/NTHMSnavcon.png" 
                                                                 alt="Anthems Logo" 
                                                                 width="75" 
                                                                 height="75">
                                                        </a>
                                                    </div>
                                                    <h4 class="text-center">Criar nova senha</h4>
                                                    <p class="text-center text-muted">
                                                        Digite seu email para receber um código de recuperação
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if ($mensagem): ?>
                                            <div class="alert alert-<?= $tipo_mensagem === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                                                <?= htmlspecialchars($mensagem) ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                        <?php endif; ?>

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="d-flex gap-3 flex-column">
                                                    <a href="#" class="btn btn-lg btn-outline-dark">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-google" viewBox="0 0 16 16">
                                                            <path d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z" />
                                                        </svg>
                                                        <span class="ms-2 fs-6">Recuperar com Google</span>
                                                    </a>
                                                </div>
                                                <p class="text-center mt-4 mb-5">Ou use seu email</p>
                                            </div>
                                        </div>

                                        <form method="POST" novalidate>
                                            <!-- CSRF hidden input removed -->
                                            
                                            <div class="row gy-3 overflow-hidden">
                                                <div class="col-12">
                                                    <div class="form-floating mb-3">
                                                        <input type="email" 
                                                               class="form-control" 
                                                               name="email" 
                                                               id="email" 
                                                               placeholder="name@example.com" 
                                                               required>
                                                        <label for="email" class="form-label">Email</label>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-12">
                                                    <div class="d-grid">
                                                        <button class="btn btn-dark btn-lg" 
                                                                style="background-color: #170045;" 
                                                                type="submit">
                                                            Enviar código agora
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="d-flex gap-2 gap-md-4 flex-column flex-md-row justify-content-md-center mt-5">
                                                    <a href="novologin.php" class="link-secondary text-decoration-none">
                                                        Criar nova conta
                                                    </a>
                                                    <a href="login.php" class="link-secondary text-decoration-none">
                                                        Voltar ao login
                                                    </a>
                                                </div>
                                            </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/anthems.js"></script>

    <script>
        // Validação do formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            
            // Limpar mensagens de erro anteriores
            email.classList.remove('is-invalid');
            
            let valido = true;
            
            // Validar email
            if (!email.value || !isValidEmail(email.value)) {
                email.classList.add('is-invalid');
                valido = false;
            }
            
            if (!valido) {
                e.preventDefault();
                mostrarToast('Por favor, corrija os erros no formulário', 'error');
            }
        });

        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function mostrarToast(mensagem, tipo = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${tipo}`;
            toast.textContent = mensagem;
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                background: ${tipo === 'error' ? '#dc3545' : '#28a745'};
                color: white;
                border-radius: 5px;
                z-index: 1050;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;
            
            document.body.appendChild(toast);
            
            // Mostrar toast
            setTimeout(() => toast.style.opacity = '1', 100);
            
            // Remover toast
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
