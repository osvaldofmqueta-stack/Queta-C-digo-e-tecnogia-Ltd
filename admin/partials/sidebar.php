<?php
$currentFile = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <div class="admin-logo">
        <div class="logo-text">
            <span class="logo-q">Q</span><span class="logo-rest">ueta</span>
            <span class="logo-dot">·</span><span class="logo-tech">Tech</span>
        </div>
        <span class="admin-badge">Painel de Controlo</span>
    </div>
    <nav class="admin-nav">
        <div class="admin-nav-section">Principal</div>
        <a href="dashboard.php" class="<?= $currentFile=='dashboard.php'?'active':'' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <div class="admin-nav-section">Conteúdo</div>
        <a href="planos.php" class="<?= $currentFile=='planos.php'?'active':'' ?>">
            <i class="fas fa-tags"></i> Planos & Preços
        </a>
        <a href="clientes.php" class="<?= $currentFile=='clientes.php'?'active':'' ?>">
            <i class="fas fa-school"></i> Clientes em Destaque
        </a>
        <a href="aplicacoes.php" class="<?= $currentFile=='aplicacoes.php'?'active':'' ?>">
            <i class="fas fa-graduation-cap"></i> Aplicações
        </a>
        <a href="acessos.php" class="<?= $currentFile=='acessos.php'?'active':'' ?>">
            <i class="fas fa-sign-in-alt"></i> Perfis de Acesso
        </a>
        <a href="carousel.php" class="<?= $currentFile=='carousel.php'?'active':'' ?>">
            <i class="fas fa-images"></i> Carousel / Banners
        </a>
        <a href="funcionalidades.php" class="<?= $currentFile=='funcionalidades.php'?'active':'' ?>">
            <i class="fas fa-star"></i> Funcionalidades
        </a>
        <a href="audience.php" class="<?= $currentFile=='audience.php'?'active':'' ?>">
            <i class="fas fa-users"></i> Para Quem É
        </a>

        <a href="chat.php" class="<?= $currentFile=='chat.php'?'active':'' ?>">
            <i class="fas fa-comments"></i> Chat de Suporte
        </a>

        <div class="admin-nav-section">Utilizadores & Stats</div>
        <a href="utilizadores.php" class="<?= $currentFile=='utilizadores.php'?'active':'' ?>">
            <i class="fas fa-user-friends"></i> Utilizadores Registados
        </a>
        <a href="analytics.php" class="<?= $currentFile=='analytics.php'?'active':'' ?>">
            <i class="fas fa-chart-line"></i> Analytics do Site
        </a>

        <div class="admin-nav-section">Manual</div>
        <a href="categorias.php" class="<?= $currentFile=='categorias.php'?'active':'' ?>">
            <i class="fas fa-folder"></i> Categorias
        </a>
        <a href="manual.php" class="<?= $currentFile=='manual.php'?'active':'' ?>">
            <i class="fas fa-file-alt"></i> Tópicos
        </a>
        <a href="passos.php" class="<?= $currentFile=='passos.php'?'active':'' ?>">
            <i class="fas fa-list-ol"></i> Passos
        </a>
        <a href="perguntas.php" class="<?= $currentFile=='perguntas.php'?'active':'' ?>">
            <i class="fas fa-comments"></i> Perguntas & Respostas
        </a>

        <div class="admin-nav-section">Configuração</div>
        <a href="configuracoes.php" class="<?= $currentFile=='configuracoes.php'?'active':'' ?>">
            <i class="fas fa-cog"></i> Configurações
        </a>
        <a href="alterar-senha.php" class="<?= $currentFile=='alterar-senha.php'?'active':'' ?>">
            <i class="fas fa-key"></i> Alterar Senha
        </a>

        <div style="margin-top:auto; padding:16px 20px; border-top:1px solid rgba(255,255,255,0.08);">
            <a href="../" target="_blank" style="color:rgba(255,255,255,0.5); font-size:13px;">
                <i class="fas fa-external-link-alt"></i> Ver Website
            </a>
        </div>
        <a href="logout.php" style="color:rgba(255,100,100,0.7)!important;">
            <i class="fas fa-sign-out-alt"></i> Sair
        </a>
    </nav>
</aside>
