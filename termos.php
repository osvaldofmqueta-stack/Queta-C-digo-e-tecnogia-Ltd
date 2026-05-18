<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
$rootPath = '/';
$pageTitle = 'Termos de Uso';
?>
<?php include 'includes/header.php'; ?>
<div class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="/">Início</a>
            <span class="sep"><i class="fas fa-chevron-right"></i></span>
            <span class="current">Termos de Uso</span>
        </div>
        <h1><i class="fas fa-file-contract"></i> Termos de Uso</h1>
        <p>Última atualização: <?= date('d/m/Y') ?></p>
    </div>
</div>
<div class="container" style="padding:48px 24px; max-width:800px;">
    <div style="background:white; border-radius:var(--radius); padding:40px; border:1px solid var(--border); line-height:1.8; color:var(--text-light);">
        <h2 style="color:var(--dark); font-size:20px; margin-bottom:16px;">1. Aceitação dos Termos</h2>
        <p>Ao aceder e utilizar este website, aceita os presentes Termos de Uso. Se não concordar, por favor não utilize o website.</p>
        <h2 style="color:var(--dark); font-size:20px; margin:24px 0 16px;">2. Uso do Website</h2>
        <p>Este website destina-se à divulgação dos produtos e serviços da <?= h(getConfig('site_nome')) ?> e ao suporte dos utilizadores dos nossos sistemas.</p>
        <h2 style="color:var(--dark); font-size:20px; margin:24px 0 16px;">3. Propriedade Intelectual</h2>
        <p>Todo o conteúdo deste website, incluindo textos, imagens e código, é propriedade da <?= h(getConfig('site_nome')) ?> e está protegido por direitos de autor.</p>
        <h2 style="color:var(--dark); font-size:20px; margin:24px 0 16px;">4. Limitação de Responsabilidade</h2>
        <p>A <?= h(getConfig('site_nome')) ?> não se responsabiliza por danos resultantes do uso ou impossibilidade de uso deste website.</p>
        <h2 style="color:var(--dark); font-size:20px; margin:24px 0 16px;">5. Contacto</h2>
        <p>Para questões sobre estes termos: <a href="mailto:<?= h(getConfig('site_email')) ?>"><?= h(getConfig('site_email')) ?></a></p>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
