<?php
// Ensure core config and session are loaded once
if (!function_exists('getDBConnection')) {
    @include_once dirname(__DIR__) . '/config.php';
}

$page_title = isset($page_title) ? $page_title : 'NTHMS - Anthems';
$active_page = isset($active_page) ? $active_page : '';
$is_admin = isset($is_admin) && $is_admin;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Anthems - Plataforma de músicas e álbuns" />
    <meta name="author" content="Anthems Team" />
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <?php if (function_exists('generateCSRFToken')): ?>
        <meta name="csrf-token" content="<?php echo htmlspecialchars(generateCSRFToken()); ?>" />
    <?php endif; ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo $base_path ?? ''; ?>img/NTHMSnavcon.png" />
    
    <!-- Fontes Google -->
    <link href="https://fonts.googleapis.com/css2?family=Medula+One&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- CSS do Template Bootstrap -->
    <link href="<?php echo $base_path ?? ''; ?>css/inicioTEMPLATE.css" rel="stylesheet" />
    
    <!-- CSS Centralizado Customizado -->
    <link href="<?php echo $base_path ?? ''; ?>css/estilos.css" rel="stylesheet" />
    
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link href="<?php echo $css; ?>" rel="stylesheet" />
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($inline_css)): ?>
        <style><?php echo $inline_css; ?></style>
    <?php endif; ?>
    
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
