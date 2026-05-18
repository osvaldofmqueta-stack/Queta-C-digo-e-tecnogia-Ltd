<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

$rootPath = '/';
$pageTitle = getConfig('site_nome', 'Queta Código e Tecnologia, Ltd');
$pageDesc = 'Soluções tecnológicas inovadoras para a gestão educacional moderna. Conheça o Super Escola, o sistema ERP académico.';

$slides = getCarousel();
$aplicacoes = getAplicacoes();
$destaques = getFuncionalidades(null, true, 6);
$recentTopicos = getTopicosRecentes(4);
$videoUrl = getConfig('youtube_video', '');
$demoLink = getConfig('demo_link', '#contacto');
$whatsapp = getWhatsappLink();

$firstApp = !empty($aplicacoes) ? $aplicacoes[0] : null;
$audiencia = $firstApp ? getTargetAudience($firstApp['id']) : [];
?>
<?php include 'includes/header.php'; ?>

<!-- HERO CAROUSEL -->
<section class="hero-carousel">
    <div class="carousel-track">
        <?php if (empty($slides)): ?>
        <div class="carousel-slide">
            <div class="carousel-slide-bg" style="background: linear-gradient(135deg, #0D1117, #161B22);"></div>
            <div class="carousel-slide-content">
                <h2>Bem-vindo à Queta Código e Tecnologia</h2>
                <p>Soluções tecnológicas inovadoras para a educação moderna.</p>
                <a href="<?= $demoLink ?>" class="btn-primary"><i class="fas fa-play-circle"></i> Ver Demonstração</a>
            </div>
        </div>
        <?php else: foreach ($slides as $slide): ?>
        <div class="carousel-slide">
            <div class="carousel-slide-bg" style="background-image: url('<?php
                $img = $slide['imagem'];
                if ($img && file_exists($img)) echo h($img);
                else echo 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=1400&q=80';
            ?>');">
            </div>
            <div class="carousel-slide-content">
                <h2><?= h($slide['titulo']) ?></h2>
                <?php if ($slide['descricao']): ?><p><?= h($slide['descricao']) ?></p><?php endif; ?>
                <?php if ($slide['link']): ?>
                <a href="<?= h($slide['link']) ?>" class="btn-primary"><i class="fas fa-arrow-right"></i> Saber Mais</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>

    <div class="carousel-controls">
        <button class="carousel-btn prev"><i class="fas fa-chevron-left"></i></button>
        <div class="carousel-dots">
            <?php $count = empty($slides) ? 1 : count($slides); for ($i = 0; $i < $count; $i++): ?>
            <button class="carousel-dot <?= $i === 0 ? 'active' : '' ?>"></button>
            <?php endfor; ?>
        </div>
        <button class="carousel-btn next"><i class="fas fa-chevron-right"></i></button>
    </div>
</section>

<!-- LOGO / BRAND BANNER -->
<div class="logo-banner" id="sobre">
    <div class="container">
        <div class="logo-banner-content">
            <div class="logo-banner-text">
                <h3><?= h(getConfig('site_nome', 'Queta Código e Tecnologia, Ltd')) ?></h3>
                <p><?= h(getConfig('site_slogan', 'Tecnologia ao serviço da educação')) ?></p>
            </div>
            <div class="logo-banner-cta">
                <a href="<?= $whatsapp ?>" target="_blank" class="btn-outline-white">
                    <i class="fab fa-whatsapp"></i> Fala Connosco
                </a>
                <a href="<?= h($demoLink) ?>" class="btn-primary" style="background:white;color:var(--primary);">
                    <i class="fas fa-calendar-alt"></i> Quero uma Demonstração
                </a>
            </div>
        </div>
    </div>
</div>

<!-- NAVEGUE POR CATEGORIA / APLICAÇÕES -->
<section class="apps-section" id="aplicacoes">
    <div class="container">
        <div class="section-header">
            <span class="section-badge"><i class="fas fa-th-large"></i> As Nossas Soluções</span>
            <h2 class="section-title">Navegue por Categoria</h2>
            <p class="section-subtitle">Descubra as nossas aplicações desenvolvidas para modernizar a gestão educacional</p>
        </div>
        <div class="apps-grid">
            <?php foreach ($aplicacoes as $app): ?>
            <div class="app-card" onclick="window.location='aplicacao.php?id=<?= $app['id'] ?>'">
                <div class="app-card-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3><?= h($app['nome']) ?></h3>
                <p><?= h($app['descricao']) ?></p>
                <span class="app-card-link">Ver Detalhes <i class="fas fa-arrow-right"></i></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FUNCIONALIDADES EM DESTAQUE -->
<?php if (!empty($destaques)): ?>
<section class="features-section" id="funcionalidades">
    <div class="container">
        <div class="section-header">
            <span class="section-badge"><i class="fas fa-star"></i> Novidades</span>
            <h2 class="section-title">Funcionalidades em Destaque</h2>
            <p class="section-subtitle">As funcionalidades mais recentes que estão a transformar a gestão escolar</p>
        </div>
        <div class="features-grid">
            <?php foreach ($destaques as $f): ?>
            <div class="feature-card">
                <div class="feature-card-img">
                    <?php if ($f['imagem'] && file_exists($f['imagem'])): ?>
                        <img src="<?= h($f['imagem']) ?>" alt="<?= h($f['titulo']) ?>">
                    <?php else: ?>
                        <i class="fas fa-puzzle-piece"></i>
                    <?php endif; ?>
                </div>
                <div class="feature-card-body">
                    <span class="feature-badge"><?= h($f['aplicacao_nome'] ?? 'Super Escola') ?></span>
                    <h4><?= h($f['titulo']) ?></h4>
                    <p><?= h($f['descricao']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- VIDEO DEMO SECTION -->
<section class="demo-section" id="demo">
    <div class="container">
        <div class="demo-grid">
            <div class="demo-content">
                <span class="section-badge"><i class="fas fa-play-circle"></i> Demo</span>
                <h2>Simplifique a gestão da sua escola e gere mais receita!</h2>
                <p>O Super Escola é o sistema ERP académico que transforma a gestão da sua instituição de ensino. Matrículas, notas, propinas e comunicação — tudo numa única plataforma simples e eficiente.</p>
                <div class="demo-btns">
                    <a href="<?= $whatsapp ?>" target="_blank" class="btn-primary">
                        <i class="fas fa-calendar-alt"></i> Agenda uma Demonstração
                    </a>
                    <a href="#contacto" class="btn-outline-white">
                        <i class="fas fa-info-circle"></i> Saber Mais
                    </a>
                </div>
            </div>
            <div class="demo-video">
                <div class="video-wrapper">
                    <?php if ($videoUrl): ?>
                    <div class="video-placeholder" id="video-wrapper" onclick="playVideo('<?= h($videoUrl) ?>')">
                        <div class="video-play-btn"><i class="fas fa-play"></i></div>
                        <p>Clique para ver o vídeo do Super Escola</p>
                    </div>
                    <?php else: ?>
                    <div class="video-placeholder">
                        <div class="video-play-btn"><i class="fas fa-play"></i></div>
                        <p>Vídeo de apresentação do Super Escola</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PARA QUEM É INDICADO -->
<?php if (!empty($audiencia)): ?>
<section class="audience-section" id="para-quem">
    <div class="container">
        <div class="section-header">
            <span class="section-badge"><i class="fas fa-users"></i> Para Quem É</span>
            <h2 class="section-title">Para Quem é Indicado o Super Escola?</h2>
            <p class="section-subtitle">Desenvolvido para todos os tipos de instituições de ensino</p>
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

<!-- MANUAL DE APOIO DESTAQUES -->
<?php if (!empty($recentTopicos)): ?>
<section style="background:white; padding:64px 0;">
    <div class="container">
        <div class="section-header">
            <span class="section-badge"><i class="fas fa-book-open"></i> Manual</span>
            <h2 class="section-title">Artigos Recentes do Manual</h2>
            <p class="section-subtitle">Guias e tutoriais para tirar o máximo proveito do Super Escola</p>
        </div>
        <div class="features-grid">
            <?php foreach ($recentTopicos as $t): ?>
            <div class="feature-card" onclick="window.location='manual/topico.php?id=<?= $t['id'] ?>'" style="cursor:pointer;">
                <div class="feature-card-img">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="feature-card-body">
                    <span class="feature-badge"><?= h($t['categoria_nome']) ?></span>
                    <h4><?= h($t['titulo']) ?></h4>
                    <p><?= h(mb_substr($t['conteudo'] ?? '', 0, 100)) ?>...</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center; margin-top:32px;">
            <a href="manual/" class="btn-secondary"><i class="fas fa-book"></i> Ver Todo o Manual</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- FALA CONNOSCO CTA -->
<section class="contact-cta-section" id="contacto">
    <div class="container">
        <h2><i class="fab fa-whatsapp"></i> Fala Connosco</h2>
        <p>Tem dúvidas sobre o Super Escola? A nossa equipa está pronta para ajudar!</p>
        <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap;">
            <a href="<?= $whatsapp ?>" target="_blank" class="btn-whatsapp-large">
                <i class="fab fa-whatsapp"></i> Enviar Mensagem no WhatsApp
            </a>
            <a href="mailto:<?= h(getConfig('site_email', 'geral@queta.ao')) ?>" class="btn-outline-white" style="border-color:white;color:white;">
                <i class="fas fa-envelope"></i> Enviar Email
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
