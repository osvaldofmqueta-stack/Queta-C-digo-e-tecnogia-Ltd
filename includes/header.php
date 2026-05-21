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
            <span>Usamos cookies para melhorar a sua experiência. Ao continuar, concorda com a nossa <a href="<?= $rootPath ?>privacidade.php">Política de Privacidade</a>.</span>
        </div>
        <div class="cookie-actions">
            <button class="btn-cookie-reject" onclick="rejectCookies()">Recusar</button>
            <button class="btn-cookie-accept" onclick="acceptCookies()">Aceitar</button>
        </div>
    </div>
</div>

<!-- MOBILE OVERLAY -->
<div class="nav-overlay" id="nav-overlay" onclick="closeMobileMenu()"></div>

<!-- Header -->
<header class="site-header" id="site-header">
    <nav class="navbar">
        <div class="nav-container">

            <!-- LOGO -->
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

            <!-- DESKTOP NAV -->
            <div class="nav-menu" id="nav-menu">
                <ul class="nav-links">

                    <!-- Manual de Apoio (mega dropdown) -->
                    <li class="has-dropdown">
                        <a href="<?= $rootPath ?>manual/" class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/manual/')!==false?'active':'' ?>">
                            <i class="fas fa-book-open nav-link-icon"></i>
                            Manual
                            <i class="fas fa-chevron-down nav-chevron"></i>
                        </a>
                        <div class="dropdown-menu dropdown-wide">
                            <div class="dropdown-header">
                                <span><i class="fas fa-book-open"></i> Manual de Apoio</span>
                                <a href="<?= $rootPath ?>manual/" class="dropdown-see-all">Ver tudo <i class="fas fa-arrow-right"></i></a>
                            </div>
                            <div class="dropdown-grid">
                                <?php foreach(getCategoriasManual() as $cat): ?>
                                <a href="<?= $rootPath ?>manual/categoria.php?id=<?= $cat['id'] ?>" class="dropdown-item-card">
                                    <span class="dropdown-item-icon"><i class="fas <?= h($cat['icone']) ?>"></i></span>
                                    <span class="dropdown-item-label"><?= h($cat['nome']) ?></span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </li>

                    <!-- Perguntas & Respostas -->
                    <li>
                        <a href="<?= $rootPath ?>perguntas/" class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/perguntas/')!==false?'active':'' ?>">
                            <i class="fas fa-circle-question nav-link-icon"></i>
                            FAQ
                        </a>
                    </li>

                    <!-- Aceder (dropdown) -->
                    <li class="has-dropdown">
                        <a href="<?= $rootPath ?>aceder.php" class="nav-link <?= $currentPage=='aceder.php'?'active':'' ?>">
                            <i class="fas fa-grid-2 nav-link-icon" style="font-family:'Font Awesome 6 Free';"></i>
                            <i class="fas fa-th nav-link-icon"></i>
                            Aceder
                            <i class="fas fa-chevron-down nav-chevron"></i>
                        </a>
                        <div class="dropdown-menu">
                            <div class="dropdown-header">
                                <span><i class="fas fa-graduation-cap"></i> Aplicações</span>
                            </div>
                            <?php foreach(getAplicacoes() as $a): ?>
                            <a href="<?= $rootPath ?>aceder.php?app=<?= $a['id'] ?>" class="dropdown-link">
                                <span class="dropdown-link-icon"><i class="fas fa-graduation-cap"></i></span>
                                <span><?= h($a['nome']) ?></span>
                                <i class="fas fa-arrow-right dropdown-link-arrow"></i>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </li>

                    <!-- Planos -->
                    <li>
                        <a href="<?= $rootPath ?>#planos" class="nav-link">
                            <i class="fas fa-tags nav-link-icon"></i>
                            Planos
                        </a>
                    </li>

                    <!-- Sobre Nós -->
                    <li>
                        <a href="<?= $rootPath ?>#sobre" class="nav-link">
                            <i class="fas fa-building nav-link-icon"></i>
                            Sobre Nós
                        </a>
                    </li>

                </ul>

                <!-- AUTH GROUP -->
                <div class="nav-auth-group">
                    <?php
                    $_navCarrinhoQtd = 0;
                    if (clienteLogado()) {
                        $_navCarrinhoQtd = contarCarrinho($_SESSION['cliente_id']);
                    }
                    ?>
                    <?php if (clienteLogado()): ?>
                    <a href="<?= $rootPath ?>carrinho.php" class="nav-cart-btn" title="Carrinho">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($_navCarrinhoQtd > 0): ?>
                        <span class="nav-cart-badge"><?= $_navCarrinhoQtd ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="nav-user has-dropdown">
                        <button class="nav-user-btn">
                            <span class="nav-user-avatar"><?= strtoupper(substr($_SESSION['cliente_nome'] ?? 'U', 0, 1)) ?></span>
                            <span class="nav-user-name"><?= h(explode(' ', $_SESSION['cliente_nome'] ?? 'Conta')[0]) ?></span>
                            <i class="fas fa-chevron-down nav-chevron" style="font-size:10px;"></i>
                        </button>
                        <div class="dropdown-menu dropdown-right">
                            <div class="dropdown-header">
                                <span><i class="fas fa-user-circle"></i> Minha Conta</span>
                            </div>
                            <a href="<?= $rootPath ?>carrinho.php" class="dropdown-link">
                                <span class="dropdown-link-icon"><i class="fas fa-shopping-cart"></i></span>
                                <span>Meu Carrinho</span>
                                <?php if ($_navCarrinhoQtd > 0): ?><span class="dropdown-badge"><?= $_navCarrinhoQtd ?></span><?php endif; ?>
                            </a>
                            <a href="<?= $rootPath ?>logout.php" class="dropdown-link dropdown-link-danger">
                                <span class="dropdown-link-icon"><i class="fas fa-sign-out-alt"></i></span>
                                <span>Terminar Sessão</span>
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="<?= $rootPath ?>login.php" class="nav-btn-ghost">
                        <i class="fas fa-sign-in-alt"></i> Entrar
                    </a>
                    <a href="<?= $rootPath ?>registro.php" class="nav-btn-solid">
                        Criar Conta
                    </a>
                    <?php endif; ?>
                    <a href="<?= getWhatsappLink() ?>" target="_blank" class="nav-btn-whatsapp">
                        <i class="fab fa-whatsapp"></i>
                        <span>Fala Connosco</span>
                    </a>
                </div>
            </div>

            <!-- HAMBURGER -->
            <button class="nav-toggle" id="nav-toggle" onclick="toggleMobileMenu()" aria-label="Menu">
                <span class="bar bar-1"></span>
                <span class="bar bar-2"></span>
                <span class="bar bar-3"></span>
            </button>

        </div>
    </nav>
</header>

<!-- MOBILE DRAWER -->
<div class="mobile-drawer" id="mobile-drawer">
    <div class="mobile-drawer-inner">
        <div class="mobile-drawer-logo">
            <div class="logo-text">
                <span class="logo-q">Q</span><span class="logo-rest">ueta</span>
                <span class="logo-dot">·</span>
                <span class="logo-tech">Tech</span>
            </div>
            <button class="mobile-close-btn" onclick="closeMobileMenu()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <nav class="mobile-nav">
            <a href="<?= $rootPath ?>manual/" class="mobile-nav-link">
                <span class="mobile-nav-icon"><i class="fas fa-book-open"></i></span>
                <span>Manual de Apoio</span>
                <i class="fas fa-chevron-right mobile-nav-arrow"></i>
            </a>
            <a href="<?= $rootPath ?>perguntas/" class="mobile-nav-link">
                <span class="mobile-nav-icon"><i class="fas fa-circle-question"></i></span>
                <span>Perguntas & Respostas</span>
                <i class="fas fa-chevron-right mobile-nav-arrow"></i>
            </a>
            <a href="<?= $rootPath ?>aceder.php" class="mobile-nav-link">
                <span class="mobile-nav-icon"><i class="fas fa-th"></i></span>
                <span>Aceder ao Sistema</span>
                <i class="fas fa-chevron-right mobile-nav-arrow"></i>
            </a>
            <a href="<?= $rootPath ?>#planos" class="mobile-nav-link" onclick="closeMobileMenu()">
                <span class="mobile-nav-icon"><i class="fas fa-tags"></i></span>
                <span>Planos & Preços</span>
                <i class="fas fa-chevron-right mobile-nav-arrow"></i>
            </a>
            <a href="<?= $rootPath ?>#sobre" class="mobile-nav-link" onclick="closeMobileMenu()">
                <span class="mobile-nav-icon"><i class="fas fa-building"></i></span>
                <span>Sobre Nós</span>
                <i class="fas fa-chevron-right mobile-nav-arrow"></i>
            </a>
        </nav>

        <div class="mobile-drawer-footer">
            <?php if (clienteLogado()): ?>
            <a href="<?= $rootPath ?>carrinho.php" class="mobile-btn-ghost">
                <i class="fas fa-shopping-cart"></i> Carrinho (<?= $_navCarrinhoQtd ?>)
            </a>
            <a href="<?= $rootPath ?>logout.php" class="mobile-btn-ghost" style="color:#ef4444;border-color:#ef4444;">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
            <?php else: ?>
            <a href="<?= $rootPath ?>login.php" class="mobile-btn-ghost">
                <i class="fas fa-sign-in-alt"></i> Entrar na Conta
            </a>
            <a href="<?= $rootPath ?>registro.php" class="mobile-btn-solid">
                Criar Conta Grátis
            </a>
            <?php endif; ?>
            <a href="<?= getWhatsappLink() ?>" target="_blank" class="mobile-btn-whatsapp">
                <i class="fab fa-whatsapp"></i> Fala Connosco pelo WhatsApp
            </a>
        </div>
    </div>
</div>


<!-- Social Proof Notification Widget -->
<?php $clientesNotif = getClientesDestaque(); ?>
<?php if (!empty($clientesNotif)): ?>
<div id="client-notif" class="client-notif" aria-live="polite">
    <button class="notif-close-btn" onclick="closeClientNotif()" title="Fechar">×</button>
    <div class="notif-pulse-dot"></div>
    <div class="notif-inner">
        <div class="notif-logo-wrap">
            <img id="notif-logo-img" src="" alt="" class="notif-logo-img">
            <div id="notif-logo-initials" class="notif-logo-initials"></div>
        </div>
        <div class="notif-info">
            <div class="notif-action-text"><i class="fas fa-check-circle"></i> Aderiu recentemente</div>
            <div id="notif-escola" class="notif-escola-name"></div>
            <div id="notif-plan" class="notif-plan-pill"></div>
            <div id="notif-loc" class="notif-location"></div>
        </div>
    </div>
</div>
<script>
const _clientesNotif = <?= json_encode(array_values($clientesNotif)) ?>;
</script>
<?php endif; ?>
