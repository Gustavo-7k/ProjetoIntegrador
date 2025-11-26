    <!-- Scripts JavaScript -->
    
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Scripts personalizados -->
    <script src="<?php echo $base_path ?? ''; ?>js/anthems.js"></script>
    
    <!-- Sistema de Notificações -->
    <script>
    (function() {
        function loadNotifications() {
            fetch('/api/get-notificacoes.php')
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    
                    const badge = document.getElementById('notificationBadge');
                    const content = document.getElementById('notificationContent');
                    
                    if (badge) {
                        if (data.unread_count > 0) {
                            badge.textContent = data.unread_count;
                            badge.style.display = 'flex';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                    
                    if (content && data.notifications) {
                        if (data.notifications.length === 0) {
                            content.innerHTML = '<p class="text-muted mb-0 p-3">Nenhuma notificação</p>';
                        } else {
                            content.innerHTML = data.notifications.map(n => {
                                if (n.type === 'follow' && n.connection_id) {
                                    return `
                                        <div class="notification-item" data-id="${n.id}">
                                            <div class="notification-text">${escapeHtml(n.message)}</div>
                                            <div class="notification-actions">
                                                <button class="btn btn-sm btn-success accept-btn" data-connection="${n.connection_id}">Aceitar</button>
                                                <button class="btn btn-sm btn-outline-secondary reject-btn" data-connection="${n.connection_id}">Recusar</button>
                                            </div>
                                        </div>
                                    `;
                                }
                                return `
                                    <div class="notification-item">
                                        <div class="notification-text">${escapeHtml(n.message)}</div>
                                        <small class="text-muted">${new Date(n.created_at).toLocaleDateString('pt-BR')}</small>
                                    </div>
                                `;
                            }).join('');
                            
                            // Event listeners para aceitar/recusar
                            content.querySelectorAll('.accept-btn').forEach(btn => {
                                btn.addEventListener('click', function(e) {
                                    e.stopPropagation();
                                    const connId = this.dataset.connection;
                                    handleConnection('/api/aceitar-conexao.php', connId, this.closest('.notification-item'));
                                });
                            });
                            
                            content.querySelectorAll('.reject-btn').forEach(btn => {
                                btn.addEventListener('click', function(e) {
                                    e.stopPropagation();
                                    const connId = this.dataset.connection;
                                    handleConnection('/api/recusar-conexao.php', connId, this.closest('.notification-item'));
                                });
                            });
                        }
                    }
                })
                .catch(err => console.error('Erro ao carregar notificações:', err));
        }
        
        function handleConnection(url, connectionId, element) {
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ connection_id: connectionId })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    element.remove();
                    loadNotifications();
                    alert(data.message);
                } else {
                    alert(data.message || 'Erro ao processar');
                }
            })
            .catch(err => alert('Erro ao processar'));
        }
        
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
        
        // Carregar ao iniciar e a cada 30 segundos
        if (document.getElementById('notificationContent')) {
            loadNotifications();
            setInterval(loadNotifications, 30000);
        }
    })();
    </script>
    
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($inline_js)): ?>
        <script><?php echo $inline_js; ?></script>
    <?php endif; ?>

</body>
</html>