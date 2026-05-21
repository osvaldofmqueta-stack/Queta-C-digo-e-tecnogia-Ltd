<?php
$currentFile = basename($_SERVER['PHP_SELF']);
$db2 = getDB();
$pendingQ = (int)$db2->query("SELECT COUNT(*) FROM perguntas WHERE respondido=0")->fetchColumn();
$pendingChat = 0;
try { $pendingChat = (int)$db2->query("SELECT COUNT(DISTINCT sessao_id) FROM chat_mensagens WHERE remetente='cliente' AND lida=0")->fetchColumn(); } catch(Exception $e){}
$adminNome = $_SESSION['admin_nome'] ?? 'Admin';
$adminInicial = mb_strtoupper(mb_substr($adminNome, 0, 1));
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="a-logo">
        <div class="a-logo-mark">
            <div class="a-logo-icon">Q</div>
            <div class="a-logo-name">
                <strong>Queta</strong>
                <span>Código e Tecnologia, Lta.</span>
            </div>
        </div>
        <span class="a-panel-badge"><i class="fas fa-lock" style="font-size:9px;"></i> Painel de Controlo</span>
    </div>

    <nav class="a-nav">
        <div class="a-nav-label">Principal</div>
        <a href="dashboard.php" class="<?= $currentFile==='dashboard.php'?'active':'' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="analytics.php" class="<?= $currentFile==='analytics.php'?'active':'' ?>">
            <i class="fas fa-chart-line"></i> Analytics
            <?php if(false): ?><span class="a-nav-badge">!</span><?php endif; ?>
        </a>

        <div class="a-nav-label">Conteúdo do Site</div>
        <a href="carousel.php" class="<?= $currentFile==='carousel.php'?'active':'' ?>">
            <i class="fas fa-images"></i> Carousel / Banners
        </a>
        <a href="aplicacoes.php" class="<?= $currentFile==='aplicacoes.php'?'active':'' ?>">
            <i class="fas fa-graduation-cap"></i> Aplicações
        </a>
        <a href="funcionalidades.php" class="<?= $currentFile==='funcionalidades.php'?'active':'' ?>">
            <i class="fas fa-star"></i> Funcionalidades
        </a>
        <a href="audience.php" class="<?= $currentFile==='audience.php'?'active':'' ?>">
            <i class="fas fa-users"></i> Para Quem É
        </a>
        <a href="planos.php" class="<?= $currentFile==='planos.php'?'active':'' ?>">
            <i class="fas fa-tags"></i> Planos & Preços
        </a>
        <a href="clientes.php" class="<?= $currentFile==='clientes.php'?'active':'' ?>">
            <i class="fas fa-school"></i> Clientes Destaque
        </a>
        <a href="acessos.php" class="<?= $currentFile==='acessos.php'?'active':'' ?>">
            <i class="fas fa-sign-in-alt"></i> Perfis de Acesso
        </a>

        <div class="a-nav-label">Manual & FAQ</div>
        <a href="categorias.php" class="<?= $currentFile==='categorias.php'?'active':'' ?>">
            <i class="fas fa-folder"></i> Categorias
        </a>
        <a href="manual.php" class="<?= $currentFile==='manual.php'?'active':'' ?>">
            <i class="fas fa-file-alt"></i> Tópicos
        </a>
        <a href="passos.php" class="<?= $currentFile==='passos.php'?'active':'' ?>">
            <i class="fas fa-list-ol"></i> Passos
        </a>
        <a href="perguntas.php" class="<?= $currentFile==='perguntas.php'?'active':'' ?>">
            <i class="fas fa-comments"></i> Perguntas & Respostas
            <?php if ($pendingQ > 0): ?><span class="a-nav-badge"><?= $pendingQ ?></span><?php endif; ?>
        </a>

        <div class="a-nav-label">Clientes & Suporte</div>
        <a href="utilizadores.php" class="<?= $currentFile==='utilizadores.php'?'active':'' ?>">
            <i class="fas fa-user-friends"></i> Utilizadores
        </a>
        <a href="chat.php" class="<?= $currentFile==='chat.php'?'active':'' ?>">
            <i class="fas fa-headset"></i> Chat de Suporte
            <?php if ($pendingChat > 0): ?><span class="a-nav-badge"><?= $pendingChat ?></span><?php endif; ?>
        </a>

        <div class="a-nav-label">Configuração</div>
        <a href="configuracoes.php" class="<?= $currentFile==='configuracoes.php'?'active':'' ?>">
            <i class="fas fa-cog"></i> Configurações
        </a>
        <a href="alterar-senha.php" class="<?= $currentFile==='alterar-senha.php'?'active':'' ?>">
            <i class="fas fa-key"></i> Alterar Senha
        </a>
    </nav>

    <div class="a-sidebar-footer">
        <div class="a-user-row">
            <div class="a-user-avatar"><?= $adminInicial ?></div>
            <div class="a-user-info">
                <strong><?= h($adminNome) ?></strong>
                <span>Administrador</span>
            </div>
        </div>
        <div class="a-footer-links">
            <a href="../" target="_blank"><i class="fas fa-external-link-alt"></i> Ver Site</a>
            <a href="logout.php" class="a-logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
    </div>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:199;"></div>
