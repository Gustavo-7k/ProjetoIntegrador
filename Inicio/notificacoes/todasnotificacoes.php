<?php
require_once __DIR__ . '/../config.php';
requireAuth();

$usuario = getCurrentUser();

// Buscar notificações do usuário (placeholder - implementar conforme necessidade)
$notificacoes = [
    [
        'id' => 1,
        'tipo' => 'conexao',
        'usuario' => 'Gustavo Schenkel',
        'avatar' => '../img/NTHMS.png',
        'mensagem' => 'enviou uma solicitação de conexão',
        'tempo' => 'Há 15 minutos',
        'acao' => true
    ],
    [
        'id' => 2,
        'tipo' => 'album',
        'usuario' => 'Slowdive',
        'avatar' => '../img/NTHMS.png',
        'mensagem' => 'lançou um novo álbum: "everything is alive"',
        'tempo' => 'Há 2 horas',
        'acao' => false
    ],
    [
        'id' => 3,
        'tipo' => 'comentario',
        'usuario' => 'Maria Silva',
        'avatar' => '../img/NTHMS.png',
        'mensagem' => 'comentou no seu post: "Adorei essa recomendação!"',
        'tempo' => 'Ontem, 18:30',
        'acao' => false
    ],
    [
        'id' => 4,
        'tipo' => 'conexao_aceita',
        'usuario' => 'Carlos Souza',
        'avatar' => '../img/NTHMS.png',
        'mensagem' => 'aceitou sua solicitação de conexão',
        'tempo' => 'Ontem, 14:15',
        'acao' => false
    ],
    [
        'id' => 5,
        'tipo' => 'evento',
        'usuario' => 'Festival Shoegaze 2023',
        'avatar' => '../img/NTHMS.png',
        'mensagem' => 'Novo evento na sua área: "Festival Shoegaze 2023"',
        'tempo' => '2 dias atrás',
        'acao' => false
    ],
    [
        'id' => 6,
        'tipo' => 'recomendacao',
        'usuario' => 'Lush',
        'avatar' => '../img/NTHMS.png',
        'mensagem' => 'Baseado no que você ouve, você pode gostar de: "Lush"',
        'tempo' => '3 dias atrás',
        'acao' => false
    ]
];

$pageTitle = 'Notificações - Anthems';
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
    <?php include __DIR__ . '/../includes/chat-sidebar.php'; ?>

    <div class="notification-container">
        <div class="notification-header">
            Notificações
        </div>
        
        <!-- Lista de Notificações -->
        <div class="notification-list">
            <?php foreach ($notificacoes as $notificacao): ?>
                <div class="notification-item" data-id="<?= $notificacao['id'] ?>">
                    <img src="<?= htmlspecialchars($notificacao['avatar']) ?>" alt="Avatar" class="notification-avatar">
                    <div class="notification-content">
                        <div class="notification-title">
                            <?= htmlspecialchars($notificacao['usuario'] . ' ' . $notificacao['mensagem']) ?>
                        </div>
                        <div class="notification-time"><?= htmlspecialchars($notificacao['tempo']) ?></div>
                    </div>
                    <?php if ($notificacao['acao']): ?>
                        <div class="notification-actions">
                            <button class="btn-accept" onclick="aceitarConexao(<?= $notificacao['id'] ?>)">Aceitar</button>
                            <button class="btn-decline" onclick="recusarConexao(<?= $notificacao['id'] ?>)">Recusar</button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/anthems.js"></script>

    <script>
        // Funções específicas das notificações
        function aceitarConexao(notificacaoId) {
            // Implementar AJAX para aceitar conexão
            fetch('../api/aceitar-conexao.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ notificacao_id: notificacaoId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remover ou atualizar notificação
                    const item = document.querySelector(`[data-id="${notificacaoId}"]`);
                    item.remove();
                    mostrarToast('Conexão aceita com sucesso!');
                } else {
                    mostrarToast('Erro ao aceitar conexão', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarToast('Erro ao aceitar conexão', 'error');
            });
        }

        function recusarConexao(notificacaoId) {
            // Implementar AJAX para recusar conexão
            fetch('../api/recusar-conexao.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ notificacao_id: notificacaoId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remover notificação
                    const item = document.querySelector(`[data-id="${notificacaoId}"]`);
                    item.remove();
                    mostrarToast('Conexão recusada');
                } else {
                    mostrarToast('Erro ao recusar conexão', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarToast('Erro ao recusar conexão', 'error');
            });
        }

        function mostrarToast(mensagem, tipo = 'success') {
            // Criar e mostrar toast de notificação
            const toast = document.createElement('div');
            toast.className = `toast ${tipo}`;
            toast.textContent = mensagem;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>
</body>
</html>
