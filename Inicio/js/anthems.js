/**
 * ANTHEMS - JavaScript Centralizado
 * Arquivo que consolida todas as funcionalidades JavaScript do projeto
 */

// Namespace principal da aplicação
const Anthems = {
    // Configurações globais
    config: {
        chatSidebarWidth: 350,
        animationDuration: 300,
        searchDelay: 300
    },
    
    // Estado da aplicação
    state: {
        chatSidebarOpen: false,
        currentUser: null,
        notifications: []
    },
    
    // Inicialização
    init: function() {
        this.chat.init();
        this.profile.init();
        this.search.init();
        this.forms.init();
        this.utils.init();
    }
};

// ========================================
// MÓDULO DE CHAT
// ========================================
Anthems.chat = {
    sidebar: null,
    overlay: null,
    
    init: function() {
        this.sidebar = document.querySelector('.chat-sidebar, #chat-sidebar');
        this.overlay = document.querySelector('.chat-overlay, #chat-overlay');
        this.bindEvents();
    },
    
    bindEvents: function() {
        // Botão de abrir chat
        const openBtn = document.getElementById('open-chat-sidebar');
        if (openBtn) {
            openBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.open();
            });
        }
        
        // Fechar ao clicar no overlay
        if (this.overlay) {
            this.overlay.addEventListener('click', () => {
                this.close();
            });
        }
        
        // Fechar com ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && Anthems.state.chatSidebarOpen) {
                this.close();
            }
        });
        
        // Busca no chat
        const searchInput = document.getElementById('chat-search-input');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.filterContacts(e.target.value);
                }, Anthems.config.searchDelay);
            });
        }
        
        // Cliques nos contatos
        const contactItems = document.querySelectorAll('.contact-item');
        contactItems.forEach(item => {
            item.addEventListener('click', () => {
                const userId = item.dataset.user || item.dataset.group;
                this.selectContact(userId);
            });
        });
    },
    
    open: function() {
        if (!this.sidebar) return;
        
        this.sidebar.style.right = '0';
        this.sidebar.classList.add('active');
        
        if (this.overlay) {
            this.overlay.style.display = 'block';
        }
        
        Anthems.state.chatSidebarOpen = true;
        document.body.style.overflow = 'hidden'; // Previne scroll do body
    },
    
    close: function() {
        if (!this.sidebar) return;
        
        this.sidebar.style.right = `-${Anthems.config.chatSidebarWidth}px`;
        this.sidebar.classList.remove('active');
        
        if (this.overlay) {
            this.overlay.style.display = 'none';
        }
        
        Anthems.state.chatSidebarOpen = false;
        document.body.style.overflow = ''; // Restaura scroll do body
    },
    
    filterContacts: function(query) {
        const contacts = document.querySelectorAll('.contact-item');
        const searchTerm = query.toLowerCase().trim();
        
        contacts.forEach(contact => {
            const name = contact.querySelector('.contact-info p');
            if (name) {
                const contactName = name.textContent.toLowerCase();
                const shouldShow = searchTerm === '' || contactName.includes(searchTerm);
                contact.style.display = shouldShow ? 'flex' : 'none';
            }
        });
    },
    
    selectContact: function(userId) {
        // Remove seleção anterior
        document.querySelectorAll('.contact-item.selected').forEach(item => {
            item.classList.remove('selected');
        });
        
        // Adiciona seleção atual
        const selectedContact = document.querySelector(`[data-user="${userId}"], [data-group="${userId}"]`);
        if (selectedContact) {
            selectedContact.classList.add('selected');
            console.log('Contato selecionado:', userId);
            // Aqui você pode implementar a abertura do chat específico
        }
    }
};

// ========================================
// MÓDULO DE PERFIL
// ========================================
Anthems.profile = {
    modal: null,
    cropper: null,
    
    init: function() {
        this.modal = document.getElementById('edit-modal');
        this.bindEvents();
    },
    
    bindEvents: function() {
        // Botão de editar perfil
        const editBtn = document.getElementById('edit-profile-btn');
        if (editBtn) {
            editBtn.addEventListener('click', () => this.openEditModal());
        }
        
        // Botões do modal
        const cancelBtn = document.getElementById('cancel-edit');
        const saveBtn = document.getElementById('save-changes');
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.closeEditModal());
        }
        
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveChanges());
        }
        
        // Upload de imagens
        const avatarUpload = document.getElementById('upload-avatar');
        const coverUpload = document.getElementById('upload-cover');
        
        if (avatarUpload) {
            avatarUpload.addEventListener('change', (e) => this.handleAvatarUpload(e));
        }
        
        if (coverUpload) {
            coverUpload.addEventListener('change', (e) => this.handleCoverUpload(e));
        }
        
        // Botão de adicionar conexão
        const friendBtn = document.getElementById('toggle-friend-btn');
        if (friendBtn) {
            friendBtn.addEventListener('click', () => this.toggleFriendForm());
        }
        
        // Fechar modal com ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal && this.modal.style.display === 'flex') {
                this.closeEditModal();
            }
        });
    },
    
    openEditModal: function() {
        if (this.modal) {
            this.modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    },
    
    closeEditModal: function() {
        if (this.modal) {
            this.modal.style.display = 'none';
            document.body.style.overflow = '';
            
            // Destruir cropper se existir
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
        }
    },
    
    handleAvatarUpload: function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        if (!file.type.startsWith('image/')) {
            alert('Por favor, selecione apenas arquivos de imagem.');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (event) => {
            const preview = document.getElementById('avatar-preview');
            if (preview) {
                preview.style.backgroundImage = `url(${event.target.result})`;
                preview.style.backgroundSize = 'cover';
                preview.style.backgroundPosition = 'center';
            }
        };
        reader.readAsDataURL(file);
    },
    
    handleCoverUpload: function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        if (!file.type.startsWith('image/')) {
            alert('Por favor, selecione apenas arquivos de imagem.');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (event) => {
            const container = document.getElementById('cover-crop-container');
            const preview = document.getElementById('cover-crop-preview');
            
            if (container && preview) {
                container.style.display = 'block';
                preview.src = event.target.result;
                
                // Destruir cropper anterior se existir
                if (this.cropper) {
                    this.cropper.destroy();
                }
                
                // Inicializar Cropper.js se disponível
                if (typeof Cropper !== 'undefined') {
                    this.cropper = new Cropper(preview, {
                        aspectRatio: 16 / 4.5,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.8,
                        movable: true,
                        cropBoxMovable: true,
                        cropBoxResizable: true
                    });
                }
            }
        };
        reader.readAsDataURL(file);
    },
    
    saveChanges: function() {
        // Salvar nome e bio
        const nameInput = document.getElementById('edit-name');
        const bioInput = document.getElementById('edit-bio');
        const profileName = document.querySelector('.profile-content h1');
        const profileBio = document.querySelector('.profile-bio p');
        
        if (nameInput && profileName) {
            profileName.textContent = nameInput.value;
        }
        
        if (bioInput && profileBio) {
            profileBio.textContent = bioInput.value;
        }
        
        // Aplicar foto de perfil
        const avatarPreview = document.getElementById('avatar-preview');
        const profilePicture = document.querySelector('.profile-picture');
        
        if (avatarPreview && profilePicture) {
            const backgroundImage = avatarPreview.style.backgroundImage;
            if (backgroundImage && backgroundImage !== 'none') {
                profilePicture.style.backgroundImage = backgroundImage;
                profilePicture.style.backgroundSize = 'cover';
                profilePicture.style.backgroundPosition = 'center';
            }
        }
        
        // Aplicar capa recortada
        if (this.cropper) {
            const croppedCanvas = this.cropper.getCroppedCanvas();
            const croppedImage = croppedCanvas.toDataURL('image/jpeg');
            const coverPhoto = document.querySelector('.cover-photo');
            
            if (coverPhoto) {
                coverPhoto.style.backgroundImage = `url(${croppedImage})`;
                coverPhoto.style.backgroundSize = 'cover';
                coverPhoto.style.backgroundPosition = 'center';
            }
        }
        
        // Mostrar feedback
        this.showSaveSuccess();
        
        // Fechar modal
        this.closeEditModal();
    },
    
    toggleFriendForm: function() {
        const form = document.getElementById('friend-request-form');
        if (!form) return;
        
        const isVisible = form.style.display === 'flex';
        
        if (isVisible) {
            form.style.opacity = '0';
            setTimeout(() => {
                form.style.display = 'none';
            }, 300);
        } else {
            form.style.display = 'flex';
            form.style.opacity = '0';
            setTimeout(() => {
                form.style.opacity = '1';
            }, 10);
        }
    },
    
    showSaveSuccess: function() {
        // Criar toast de sucesso
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.textContent = 'Perfil atualizado com sucesso!';
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 9999;
            animation: slideInRight 0.3s ease;
        `;
        
        document.body.appendChild(toast);
        
        // Remover após 3 segundos
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
};

// ========================================
// MÓDULO DE BUSCA
// ========================================
Anthems.search = {
    init: function() {
        this.bindEvents();
    },
    
    bindEvents: function() {
        // Busca de álbuns
        const albumSearch = document.getElementById('buscaAlbum');
        if (albumSearch) {
            albumSearch.addEventListener('change', (e) => this.handleAlbumSearch(e));
            albumSearch.addEventListener('input', (e) => this.handleAlbumInput(e));
        }
    },
    
    handleAlbumSearch: function(e) {
        const value = e.target.value.trim();
        
        // Dicionário de redirecionamento - pode ser carregado de um arquivo JSON
        const albumLinks = {
            "In Rainbows": "comentarios/EscreverComentário.php",
            "Kid A": "albuns/KidA.php",
            "Ok Computer": "albuns/OkComputer.php"
        };
        
        if (albumLinks[value]) {
            window.location.href = albumLinks[value];
        } else if (value) {
            this.showAlbumNotFound(value);
        }
    },
    
    handleAlbumInput: function(e) {
        // Implementar sugestões em tempo real aqui se necessário
        const query = e.target.value.toLowerCase();
        // Filtrar opções do datalist baseado na entrada
    },
    
    showAlbumNotFound: function(albumName) {
        const message = `Álbum "${albumName}" não encontrado. Deseja sugerir este álbum?`;
        if (confirm(message)) {
            // Implementar sugestão de álbum
            console.log('Sugerindo álbum:', albumName);
        }
    }
};

// ========================================
// MÓDULO DE FORMULÁRIOS
// ========================================
Anthems.forms = {
    init: function() {
        this.setupValidation();
        this.setupSubmission();
    },
    
    setupValidation: function() {
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
            
            // Validação em tempo real
            const inputs = form.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', () => this.clearFieldError(input));
            });
        });
    },
    
    setupSubmission: function() {
        const ajaxForms = document.querySelectorAll('form[data-ajax]');
        
        ajaxForms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitFormAjax(form);
            });
        });
    },
    
    validateForm: function(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], textarea[required]');
        
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    validateField: function(field) {
        const value = field.value.trim();
        let isValid = true;
        let message = '';
        
        // Validação de campos obrigatórios
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            message = 'Este campo é obrigatório.';
        }
        
        // Validação de email
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                message = 'Por favor, insira um email válido.';
            }
        }
        
        // Validação de senha
        if (field.type === 'password' && value) {
            if (value.length < 6) {
                isValid = false;
                message = 'A senha deve ter pelo menos 6 caracteres.';
            }
        }
        
        this.showFieldError(field, message);
        return isValid;
    },
    
    showFieldError: function(field, message) {
        this.clearFieldError(field);
        
        if (message) {
            field.classList.add('is-invalid');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = message;
            
            field.parentNode.appendChild(errorDiv);
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }
    },
    
    clearFieldError: function(field) {
        field.classList.remove('is-invalid', 'is-valid');
        
        const errorMsg = field.parentNode.querySelector('.invalid-feedback');
        if (errorMsg) {
            errorMsg.remove();
        }
    },
    
    submitFormAjax: function(form) {
        const formData = new FormData(form);
        const url = form.action || window.location.href;
        
        // Mostrar loading
        this.showFormLoading(form, true);
        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            return response.text().then(text => ({ ok: response.ok, status: response.status, text }));
        })
        .then(result => {
            this.showFormLoading(form, false);

            if (!result.ok) {
                const serverMsg = result.text ? `Erro do servidor (${result.status}).` : `Erro do servidor (${result.status}).`;
                this.showFormError(form, serverMsg);
                console.error('Server error response:', result.status, result.text);
                return;
            }

            let data;
            try {
                data = JSON.parse(result.text || '{}');
            } catch (e) {
                this.showFormError(form, 'Resposta inválida do servidor.');
                console.error('Invalid JSON response:', result.text);
                return;
            }

            if (data.success) {
                this.showFormSuccess(form, data.message || 'Operação realizada com sucesso.');
                form.reset();
                if (data.redirect) {
                    // Pequeno delay para o usuário ver a mensagem antes de redirecionar
                    setTimeout(() => { window.location.href = data.redirect; }, 600);
                }
            } else {
                this.showFormError(form, data.message || 'Erro ao processar solicitação.');
            }
        })
        .catch(error => {
            this.showFormLoading(form, false);
            this.showFormError(form, 'Erro ao processar solicitação.');
            console.error('Form submission error:', error);
        });
    },
    
    showFormLoading: function(form, loading) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = loading;
            submitBtn.textContent = loading ? 'Processando...' : submitBtn.dataset.originalText || 'Enviar';
            
            if (!submitBtn.dataset.originalText) {
                submitBtn.dataset.originalText = submitBtn.textContent;
            }
        }
    },
    
    showFormSuccess: function(form, message) {
        this.showFormMessage(form, message, 'success');
    },
    
    showFormError: function(form, message) {
        this.showFormMessage(form, message, 'error');
    },
    
    showFormMessage: function(form, message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        form.insertBefore(alertDiv, form.firstChild);
        
        // Auto-remover após 5 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
};

// ========================================
// UTILITÁRIOS
// ========================================
Anthems.utils = {
    init: function() {
        this.setupImageLazyLoading();
        this.setupSmoothScroll();
        this.setupTooltips();
        this.setupAccessibility();
    },
    
    setupImageLazyLoading: function() {
        const images = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    },
    
    setupSmoothScroll: function() {
        const scrollLinks = document.querySelectorAll('a[href^="#"]');
        
        scrollLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                const target = document.querySelector(link.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    },
    
    setupTooltips: function() {
        // Inicializar tooltips do Bootstrap se disponível
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }
    },
    
    setupAccessibility: function() {
        // Melhorar navegação por teclado
        const interactiveElements = document.querySelectorAll('.contact-item, .album-card, .edit-btn');
        
        interactiveElements.forEach(element => {
            if (!element.hasAttribute('tabindex')) {
                element.setAttribute('tabindex', '0');
            }
            
            element.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    element.click();
                }
            });
        });
    },
    
    // Função utilitária para debounce
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Função utilitária para formatação de datas
    formatDate: function(date, options = {}) {
        const defaultOptions = {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        
        return new Date(date).toLocaleDateString('pt-BR', { ...defaultOptions, ...options });
    },
    
    // Função utilitária para escape de HTML
    escapeHtml: function(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

// ========================================
// INICIALIZAÇÃO
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    Anthems.init();
});

// Adicionar estilos CSS dinâmicos para animações
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .contact-item.selected {
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 8px;
    }
    
    .toast-notification {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 14px;
        font-weight: 500;
    }
`;
document.head.appendChild(style);

// Exportar para uso global
window.Anthems = Anthems;
