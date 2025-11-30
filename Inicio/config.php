<?php
// Iniciar buffer de saída para capturar saídas acidentais (BOM/echo/warnings)
if (!ob_get_level()) {
    ob_start();
}

// Configurações do banco de dados
define('DB_HOST', getenv('DB_HOST') ?: 'anthems-db');
define('DB_NAME', getenv('DB_DATABASE') ?: 'anthems_db');
define('DB_USER', getenv('DB_USERNAME') ?: 'anthems_user');
define('DB_PASS', getenv('DB_PASSWORD') ?: 'change_this_user_please');
define('DB_CHARSET', 'utf8mb4');

// Configurações da aplicação
define('APP_NAME', 'Anthems');
define('APP_VERSION', '1.0.0');
define('APP_URL', rtrim(getenv('APP_URL') ?: 'http://localhost:8080', '/') . '/');

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_start();

// Configuração de encoding padrão
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de erro (desenvolvimento)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurações de segurança
define('PASSWORD_MIN_LENGTH', 6);
define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 dias

// Configurações de upload
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('UPLOAD_PATH', __DIR__ . '/uploads/');

    //Função para conexão com banco de dados
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            // Configurar timezone do MySQL para Brasília (UTC-3)
            $pdo->exec("SET time_zone = '-03:00'");
        } catch (PDOException $e) {
            error_log("Erro de conexão com banco de dados: " . $e->getMessage());
            // Verificar se é uma requisição que espera JSON
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            $isApi = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;
            $acceptsJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
            $isFormSubmit = $_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false;
            
            if ($isAjax || $isApi || $acceptsJson || $isFormSubmit || $_SERVER['REQUEST_METHOD'] === 'POST') {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados. Verifique se o serviço MySQL está rodando.']);
                exit;
            }
            die("Erro interno do servidor. Tente novamente mais tarde.");
        }
    }
    
    return $pdo;
}

    //Função para sanitizar entrada
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Função para validar email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

    //Função para hash de senha
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

    //Função para verificar senha
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Função para log de atividades
function logActivity($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    @file_put_contents($logDir . '/app.log', $logMessage, FILE_APPEND | LOCK_EX);
}

// Função para redirecionamento seguro
function redirectTo($url, $statusCode = 302) {
    // Validar URL para prevenir redirecionamento aberto
    if (!filter_var($url, FILTER_VALIDATE_URL) && !preg_match('/^\/[^\/]/', $url)) {
        $url = 'login/login.php';
    }
    
    header("Location: $url", true, $statusCode);
    exit;
}

    //Função para verificar se usuário está logado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

    //Função para verificar se usuário é admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

    //Função para obter dados do usuário atual
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    //conexão com banco
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    
    return $stmt->fetch();
}

// Função para escape de saída
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

    //Função para formatar data
function formatDate($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}

    //Função para enviar resposta JSON
function sendJSONResponse($data, $statusCode = 200) {
    http_response_code($statusCode);

    $displayErrors = ini_get('display_errors');
    $oldErrorReporting = error_reporting();
    ini_set('display_errors', '0');
    error_reporting(0);

    while (ob_get_level() > 0) {
        @ob_end_clean();
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);

    // Restaurar as configurações originais
    error_reporting($oldErrorReporting);
    ini_set('display_errors', $displayErrors);
    exit;
}

    //Função para validar upload de arquivo
function validateUpload($file) {
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Erro no upload do arquivo.';
        return $errors;
    }
    
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        $errors[] = 'Arquivo muito grande. Máximo permitido: ' . (UPLOAD_MAX_SIZE / 1024 / 1024) . 'MB.';
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, UPLOAD_ALLOWED_TYPES)) {
        $errors[] = 'Tipo de arquivo não permitido. Tipos aceitos: ' . implode(', ', UPLOAD_ALLOWED_TYPES);
    }
    
    return $errors;
}

// Middleware de autenticação
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: ../login/login.php');
        exit;
    }
}

// Middleware de admin
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        http_response_code(403);
        die('Acesso negado. Permissões de administrador necessárias.');
    }
}

