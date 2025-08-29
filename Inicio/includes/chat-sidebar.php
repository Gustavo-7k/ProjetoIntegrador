<?php
$base_path = isset($base_path) ? $base_path : '';
?>

<!-- Chat Sidebar -->
<div class="chat-sidebar" id="chat-sidebar">
    <div class="chat-header">
        <h5>Chat</h5>
    </div>
    
    <div class="chat-search">
        <input type="text" class="form-control" placeholder="Search" id="chat-search-input">
    </div>
    
    <div class="chat-content">
        <div class="chat-section">
            <h6>Conexões</h6>
            
            <div class="chat-contacts">
                <div class="contact-item" data-user="gustavo">
                    <div class="contact-avatar primary"></div>
                    <div class="contact-info">
                        <p>GUSTAVO SCHENKEL</p>
                        <small>Online</small>
                    </div>
                </div>
                
                <div class="contact-item" data-user="maria">
                    <div class="contact-avatar secondary"></div>
                    <div class="contact-info">
                        <p>MARIA SILVA</p>
                        <small>Offline</small>
                    </div>
                </div>
                
                <div class="contact-item" data-user="carlos">
                    <div class="contact-avatar accent"></div>
                    <div class="contact-info">
                        <p>CARLOS SOUZA</p>
                        <small>Online</small>
                    </div>
                </div>
            </div>
            
            <div class="chat-contacts">
                <h6>Groups</h6>
                <div class="contact-item" data-group="shoegaze">
                    <div class="contact-avatar success"></div>
                    <div class="contact-info">
                        <p>shoegaze boys</p>
                        <small>5 members</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chat Overlay -->
<div class="chat-overlay" id="chat-overlay"></div>
