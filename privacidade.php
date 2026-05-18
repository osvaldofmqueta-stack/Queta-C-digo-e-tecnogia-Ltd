<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
$rootPath = '/';
$pageTitle = 'Política de Privacidade';
?>
<?php include 'includes/header.php'; ?>
<div class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="/">Início</a>
            <span class="sep"><i class="fas fa-chevron-right"></i></span>
            <span class="current">Política de Privacidade</span>
        </div>
        <h1><i class="fas fa-shield-alt"></i> Política de Privacidade</h1>
        <p>Última atualização: <?= date('d/m/Y') ?></p>
    </div>
</div>
<div class="container" style="padding:48px 24px; max-width:800px;">
    <div style="background:white; border-radius:var(--radius); padding:40px; border:1px solid var(--border); line-height:1.8; color:var(--text-light);">
        <h2 style="color:var(--dark); font-size:20px; margin-bottom:16px;">1. Informações que Recolhemos</h2>
        <p>A <?= h(getConfig('site_nome')) ?> recolhe informações que você nos fornece diretamente, como nome, email e perguntas submetidas através do nosso website.</p>
        <h2 style="color:var(--dark); font-size:20px; margin:24px 0 16px;">2. Cookies</h2>
        <p>Utilizamos cookies para melhorar a sua experiência de navegação. Os cookies são pequenos ficheiros de texto guardados no seu dispositivo. Pode recusar os cookies nas definições do seu navegador.</p>
        <h2 style="color:var(--dark); font-size:20px; margin:24px 0 16px;">3. Utilização dos Dados</h2>
        <p>Utilizamos as informações recolhidas para responder às suas perguntas, melhorar os nossos serviços e enviar informações sobre os nossos produtos, caso tenha dado consentimento.</p>
        <h2 style="color:var(--dark); font-size:20px; margin:24px 0 16px;">4. Partilha de Dados</h2>
        <p>Não vendemos nem partilhamos os seus dados pessoais com terceiros, exceto quando exigido por lei.</p>
        <h2 style="color:var(--dark); font-size:20px; margin:24px 0 16px;">5. Contacto</h2>
        <p>Para questões sobre privacidade, contacte-nos em: <a href="mailto:<?= h(getConfig('site_email')) ?>"><?= h(getConfig('site_email')) ?></a></p>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
