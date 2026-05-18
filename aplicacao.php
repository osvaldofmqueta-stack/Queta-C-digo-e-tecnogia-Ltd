<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
$rootPath = '/';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$app = getAplicacao($id);
if (!$app) { header('Location: /'); exit; }

$pageTitle = h($app['nome']);
$pageDesc = $app['descricao'] ?? '';

$categorias = getCategoriasManual($id);
$funcionalidades = getFuncionalidades($id);
$audiencia = getTargetAudience($id);
$whatsapp = getWhatsappLink();
$videoUrl = getConfig('youtube_video', '');
?>
<?php include 'includes/header.php'; ?>

<div class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= $rootPath ?>">Início</a>
            <span class="sep"><i class="fas fa-chevron-right"></i></span>
            <span class="current"><?= h($app['nome']) ?></span>
        </div>
        <h1><i class="fas fa-graduation-cap"></i> <?= h($app['nome']) ?></h1>
        <p><?= h($app['descricao']) ?></p>
        <div style="display:flex; gap:12px; margin-top:24px; flex-wrap:wrap;">
            <a href="<?= $whatsapp ?>" target="_blank" class="btn-primary"><i class="fas fa-calendar-alt"></i> Agendar Demonstração</a>
            <a href="manual/index.php?app=<?= $app['id'] ?>" class="btn-outline-white"><i class="fas fa-book"></i> Ver Manual</a>
        </div>
    </div>
</div>

<?php if (!empty($funcionalidades)): ?>
<section class="features-section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge"><i class="fas fa-star"></i> Funcionalidades</span>
            <h2 class="section-title">O que o <?= h($app['nome']) ?> oferece?</h2>
        </div>
        <div class="features-grid">
            <?php foreach ($funcionalidades as $f): ?>
            <div class="feature-card">
                <div class="feature-card-img">
                    <?php if ($f['imagem'] && file_exists($f['imagem'])): ?>
                        <img src="<?= h($f['imagem']) ?>" alt="<?= h($f['titulo']) ?>">
                    <?php else: ?>
                        <i class="fas fa-puzzle-piece"></i>
                    <?php endif; ?>
                </div>
                <div class="feature-card-body">
                    <?php if ($f['destaque']): ?><span class="feature-badge">Destaque</span><?php endif; ?>
                    <h4><?= h($f['titulo']) ?></h4>
                    <p><?= h($f['descricao']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($videoUrl): ?>
<section class="demo-section">
    <div class="container">
        <div class="demo-grid">
            <div class="demo-content">
                <span class="section-badge"><i class="fas fa-play-circle"></i> Demo</span>
                <h2>Simplifique a gestão da sua escola e gere mais receita!</h2>
                <p>Veja como o <?= h($app['nome']) ?> pode transformar a gestão da sua instituição.</p>
                <div class="demo-btns">
                    <a href="<?= $whatsapp ?>" target="_blank" class="btn-primary">
                        <i class="fas fa-calendar-alt"></i> Agenda uma Demonstração
                    </a>
                </div>
            </div>
            <div class="demo-video">
                <div class="video-wrapper">
                    <div class="video-placeholder" id="video-wrapper" onclick="playVideo('<?= h($videoUrl) ?>')">
                        <div class="video-play-btn"><i class="fas fa-play"></i></div>
                        <p>Clique para ver o vídeo</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($audiencia)): ?>
<section class="audience-section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge"><i class="fas fa-users"></i> Para Quem É</span>
            <h2 class="section-title">Para Quem é Indicado?</h2>
        </div>
        <div class="audience-grid">
            <?php foreach ($audiencia as $aud): ?>
            <div class="audience-card">
                <div class="audience-card-icon"><i class="fas <?= h($aud['icone']) ?>"></i></div>
                <h4><?= h($aud['titulo']) ?></h4>
                <p><?= h($aud['descricao']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($categorias)): ?>
<section style="padding:64px 0; background:white;">
    <div class="container">
        <div class="section-header">
            <span class="section-badge"><i class="fas fa-book"></i> Suporte</span>
            <h2 class="section-title">Manual de Apoio — <?= h($app['nome']) ?></h2>
        </div>
        <?php foreach ($categorias as $cat): ?>
        <div class="category-card" onclick="window.location='manual/categoria.php?id=<?= $cat['id'] ?>'">
            <div class="category-icon"><i class="fas <?= h($cat['icone']) ?>"></i></div>
            <div class="category-info">
                <h3><?= h($cat['nome']) ?></h3>
                <?php if ($cat['descricao']): ?><p><?= h($cat['descricao']) ?></p><?php endif; ?>
            </div>
            <div style="margin-left:auto; color:var(--primary);"><i class="fas fa-chevron-right"></i></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="contact-cta-section">
    <div class="container">
        <h2>Pronto para começar?</h2>
        <p>Contacte-nos e agende uma demonstração gratuita do <?= h($app['nome']) ?></p>
        <a href="<?= $whatsapp ?>" target="_blank" class="btn-whatsapp-large">
            <i class="fab fa-whatsapp"></i> Agendar Demonstração via WhatsApp
        </a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
