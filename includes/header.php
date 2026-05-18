<?php
require_once __DIR__ . '/functions.php';
$siteName = getConfig('site_nome', 'Queta Código e Tecnologia');
$logo = getConfig('logo', '');
$currentPage = basename($_SERVER['PHP_SELF']);
$rootPath = isset($rootPath) ? $rootPath : '/';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?><?= h($siteName) ?></title>
    <meta name="description" content="<?= isset($pageDesc) ? h($pageDesc) : 'Soluções tecnológicas para a educação moderna.' ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $rootPath ?>assets/css/style.css">
    <?= isset($extraHead) ? $extraHead : '' ?>
</head>
<body>

<!-- Cookie Banner -->
<div id="cookie-banner" class="cookie-banner" style="display:none;">
    <div class="cookie-content">
        <div class="cookie-text">
            <i class="fas fa-cookie-bite"></i>
            <span>Usamos cookies para melhorar a sua experiência no nosso website. Ao continuar a navegar, concorda com a nossa <a href="<?= $rootPath ?>privacidade.php">Política de Privacidade</a>.</span>
        </div>
        <div class="cookie-actions">
            <button class="btn-cookie-reject" onclick="rejectCookies()">Recusar</button>
            <button class="btn-cookie-accept" onclick="acceptCookies()">Aceitar Cookies</button>
        </div>
    </div>
</div>

<!-- Header -->
<header class="site-header" id="site-header">
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?= $rootPath ?>" class="nav-logo">
                <?php if ($logo && file_exists($logo)): ?>
                    <img src="<?= $rootPath . h($logo) ?>" alt="<?= h($siteName) ?>" class="logo-img">
                <?php else: ?>
                    <div class="logo-text">
                        <span class="logo-q">Q</span><span class="logo-rest">ueta</span>
                        <span class="logo-dot">·</span>
                        <span class="logo-tech">Tech</span>
                    </div>
                <?php endif; ?>
            </a>

            <div class="nav-menu" id="nav-menu">
                <ul class="nav-links">
                    <li><a href="<?= $rootPath ?>" class="nav-link <?= $currentPage=='index.php'?'active':'' ?>">Início</a></li>
                    <li class="has-dropdown">
                        <a href="<?= $rootPath ?>manual/" class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/manual/')!==false?'active':'' ?>">
                            Manual de Apoio <i class="fas fa-chevron-down"></i>
                        </a>
                        <div class="dropdown-menu">
                            <?php foreach(getCategoriasManual() as $cat): ?>
                            <a href="<?= $rootPath ?>manual/categoria.php?id=<?= $cat['id'] ?>">
                                <i class="fas <?= h($cat['icone']) ?>"></i> <?= h($cat['nome']) ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </li>
                    <li><a href="<?= $rootPath ?>perguntas/" class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/perguntas/')!==false?'active':'' ?>">Perguntas & Respostas</a></li>
                    <li><a href="<?= $rootPath ?>#sobre" class="nav-link">Sobre Nós</a></li>
                    <li><a href="<?= $rootPath ?>#contacto" class="nav-link">Contacto</a></li>
                </ul>
                <a href="<?= getWhatsappLink() ?>" target="_blank" class="btn-whatsapp-nav">
                    <i class="fab fa-whatsapp"></i> Fala Connosco
                </a>
            </div>

            <button class="nav-toggle" id="nav-toggle" onclick="toggleMenu()">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>
</header>

<!-- WhatsApp Float Button -->
<a href="<?= getWhatsappLink() ?>" target="_blank" class="whatsapp-float" title="Fala connosco via WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>
