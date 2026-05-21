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
$planos = $firstApp ? getPlanos($firstApp['id']) : [];
?>
<?php include 'includes/header.php'; ?>

<!-- PAGE LOADER -->
<div id="page-loader">
    <div class="loader-logo"><span>Q</span>ueta·Tech</div>
    <div class="loader-bar"><div class="loader-bar-fill"></div></div>
</div>

<!-- SCROLL PROGRESS -->
<div id="scroll-progress"></div>

<!-- HERO CAROUSEL -->
<?php
$heroImages = [
    'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=1600&q=85',
    'https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=1600&q=85',
    'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=1600&q=85',
];
$heroSlides = [];
if (!empty($slides)) {
    foreach ($slides as $si => $slide) {
        $img = ($slide['imagem'] && file_exists($slide['imagem'])) ? $slide['imagem'] : $heroImages[$si % count($heroImages)];
        $heroSlides[] = [
            'img' => $img,
            'badge' => $si === 0 ? '<i class="fas fa-bolt"></i> Sistema ERP Académico #1 em Angola' : '',
            'title' => h($slide['titulo']),
            'desc' => h($slide['descricao'] ?? ''),
            'link' => $slide['link'] ? h($slide['link']) : '#demo',
            'link_label' => 'Saber Mais',
            'stats' => false,
        ];
    }
} else {
    $heroSlides = [
        [
            'img' => $heroImages[0],
            'badge' => '<i class="fas fa-bolt"></i> Sistema ERP Académico #1 em Angola',
            'title' => 'Super Escola &mdash;<br>ERP Académico',
            'desc' => 'Gerencie matrículas, notas, finanças e muito mais numa só plataforma.',
            'link' => $demoLink,
            'link_label' => 'Ver Demonstração',
            'stats' => true,
        ],
        [
            'img' => $heroImages[1],
            'badge' => '<i class="fas fa-star"></i> Mais de 79 Funcionalidades Incluídas',
            'title' => 'Tudo o que a sua<br>escola precisa',
            'desc' => 'Lançamento de notas, pautas digitais, controlo de propinas e relatórios automáticos.',
            'link' => '#funcionalidades',
            'link_label' => 'Ver Funcionalidades',
            'stats' => false,
        ],
        [
            'img' => $heroImages[2],
            'badge' => '<i class="fas fa-tags"></i> Planos para todos os tamanhos',
            'title' => 'Planos flexíveis<br>para cada escola',
            'desc' => 'Premium, Golden ou Ruby — escolha o plano ideal para a sua instituição de ensino.',
            'link' => '#planos',
            'link_label' => 'Ver Planos',
            'stats' => false,
        ],
    ];
}
$slideCount = count($heroSlides);
?>
<section class="hero-carousel" id="hero">
    <canvas id="hero-particles"></canvas>
    <div class="carousel-track">
        <?php foreach ($heroSlides as $si => $hs): ?>
        <div class="carousel-slide <?= $si === 0 ? 'active-slide' : '' ?>">
            <div class="carousel-slide-bg" style="background-image:url('<?= $hs['img'] ?>');"></div>
            <div class="carousel-slide-content">
                <?php if ($hs['badge']): ?>
                <div class="hero-badge"><?= $hs['badge'] ?></div>
                <?php endif; ?>
                <h2><?= $hs['title'] ?></h2>
                <?php if ($hs['desc']): ?><p><?= $hs['desc'] ?></p><?php endif; ?>
                <div class="hero-actions">
                    <a href="<?= $hs['link'] ?>" class="btn-primary"><i class="fas fa-arrow-right"></i> <?= $hs['link_label'] ?></a>
                    <a href="<?= $whatsapp ?>" target="_blank" class="btn-outline-white"><i class="fab fa-whatsapp"></i> Falar Connosco</a>
                </div>
                <?php if ($hs['stats']): ?>
                <div class="hero-stats-row">
                    <div class="hero-stat"><div class="hs-num">79+</div><div class="hs-label">Funcionalidades</div></div>
                    <div class="hero-stat"><div class="hs-num">3</div><div class="hs-label">Planos</div></div>
                    <div class="hero-stat"><div class="hs-num">100%</div><div class="hs-label">Suporte Incluído</div></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Scroll hint -->
    <div class="carousel-scroll-hint">
        <div class="scroll-arrow"><div class="scroll-dot"></div></div>
    </div>

    <!-- Controls bar -->
    <div class="carousel-controls">
        <div class="carousel-progress-bars">
            <?php for ($i = 0; $i < $slideCount; $i++): ?>
            <div class="carousel-prog-bar <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>">
                <div class="carousel-prog-bar-fill"></div>
            </div>
            <?php endfor; ?>
        </div>
        <div style="display:flex;align-items:center;gap:16px;">
            <div class="carousel-counter">
                <span class="carousel-counter-current">01</span>
                <span style="opacity:0.4;font-size:13px;">/ <?= str_pad($slideCount, 2, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="carousel-arrows">
                <button class="carousel-btn prev"><i class="fas fa-chevron-left"></i></button>
                <button class="carousel-btn next"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</section>

<!-- TRUST BAR -->
<div class="trust-bar reveal">
    <div class="container">
        <div class="trust-items">
            <div class="trust-item"><i class="fas fa-shield-alt"></i> <span>Dados 100% Seguros</span></div>
            <div class="trust-divider"></div>
            <div class="trust-item"><i class="fas fa-headset"></i> <span>Suporte Técnico Incluído</span></div>
            <div class="trust-divider"></div>
            <div class="trust-item"><i class="fas fa-cloud"></i> <span>Acesso Online 24/7</span></div>
            <div class="trust-divider"></div>
            <div class="trust-item"><i class="fas fa-graduation-cap"></i> <span>Formação Incluída</span></div>
            <div class="trust-divider"></div>
            <div class="trust-item"><i class="fas fa-sync-alt"></i> <span>Actualizações Grátis</span></div>
        </div>
    </div>
</div>

<!-- STATS COUNTERS -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid-main">
            <div class="stat-item reveal" data-delay="0">
                <div class="stat-item-icon"><i class="fas fa-puzzle-piece"></i></div>
                <span class="counter-num" data-target="79" data-suffix="+">0+</span>
                <div class="stat-label">Funcionalidades</div>
            </div>
            <div class="stat-item reveal" data-delay="100">
                <div class="stat-item-icon green"><i class="fas fa-school"></i></div>
                <span class="counter-num" data-target="100" data-suffix="+">0+</span>
                <div class="stat-label">Escolas Clientes</div>
            </div>
            <div class="stat-item reveal" data-delay="200">
                <div class="stat-item-icon orange"><i class="fas fa-tags"></i></div>
                <span class="counter-num" data-target="3" data-suffix="">0</span>
                <div class="stat-label">Planos Disponíveis</div>
            </div>
            <div class="stat-item reveal" data-delay="300">
                <div class="stat-item-icon ruby"><i class="fas fa-headset"></i></div>
                <span class="counter-num" data-target="24" data-suffix="/7">0/7</span>
                <div class="stat-label">Suporte Disponível</div>
            </div>
        </div>
    </div>
</section>

<div class="section-divider"></div>

<!-- LOGO / BRAND BANNER -->
<div class="logo-banner reveal" id="sobre">
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
        <div class="section-header reveal">
            <span class="section-badge"><i class="fas fa-th-large"></i> As Nossas Soluções</span>
            <h2 class="section-title">Navegue por Categoria</h2>
            <p class="section-subtitle">Descubra as nossas aplicações desenvolvidas para modernizar a gestão educacional</p>
        </div>
        <div class="apps-grid">
            <?php foreach ($aplicacoes as $ai => $app): ?>
            <div class="app-card reveal" data-delay="<?= $ai * 100 ?>" onclick="window.location='aplicacao.php?id=<?= $app['id'] ?>'">
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
<?php if (!empty($destaques)):
$featureIcons = ['fa-users-cog','fa-money-bill-wave','fa-book-open','fa-comments','fa-chart-bar','fa-mobile-alt'];
?>
<section class="features-section" id="funcionalidades">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-badge"><i class="fas fa-star"></i> Destaques</span>
            <h2 class="section-title">Funcionalidades em Destaque</h2>
            <p class="section-subtitle">As funcionalidades que estão a transformar a gestão escolar em Angola</p>
        </div>
        <div class="features-grid">
            <?php foreach ($destaques as $fi => $f): ?>
            <div class="feature-card reveal" data-delay="<?= ($fi % 3) * 100 ?>">
                <div class="feature-card-img">
                    <?php if ($f['imagem'] && file_exists($f['imagem'])): ?>
                        <img src="<?= h($f['imagem']) ?>" alt="<?= h($f['titulo']) ?>">
                    <?php else: ?>
                        <i class="fas <?= $featureIcons[$fi % count($featureIcons)] ?>"></i>
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
            <div class="demo-content reveal from-left">
                <span class="section-badge"><i class="fas fa-play-circle"></i> Demo ao Vivo</span>
                <h2>Simplifique a gestão da sua escola e gere mais receita!</h2>
                <p>O Super Escola é o sistema ERP académico que transforma a gestão da sua instituição de ensino. Matrículas, notas, propinas e comunicação — tudo numa única plataforma simples e eficiente.</p>
                <ul style="list-style:none;margin:0 0 28px;display:flex;flex-direction:column;gap:10px;">
                    <li style="color:rgba(255,255,255,.8);font-size:15px;display:flex;align-items:center;gap:10px;"><i class="fas fa-check-circle" style="color:var(--secondary);font-size:16px;flex-shrink:0;"></i> Gestão completa de matrículas e propinas</li>
                    <li style="color:rgba(255,255,255,.8);font-size:15px;display:flex;align-items:center;gap:10px;"><i class="fas fa-check-circle" style="color:var(--secondary);font-size:16px;flex-shrink:0;"></i> Lançamento de notas e pautas digitais</li>
                    <li style="color:rgba(255,255,255,.8);font-size:15px;display:flex;align-items:center;gap:10px;"><i class="fas fa-check-circle" style="color:var(--secondary);font-size:16px;flex-shrink:0;"></i> Portal para pais, alunos e professores</li>
                    <li style="color:rgba(255,255,255,.8);font-size:15px;display:flex;align-items:center;gap:10px;"><i class="fas fa-check-circle" style="color:var(--secondary);font-size:16px;flex-shrink:0;"></i> Relatórios financeiros e académicos automáticos</li>
                </ul>
                <div class="demo-btns">
                    <a href="<?= $whatsapp ?>" target="_blank" class="btn-primary">
                        <i class="fas fa-calendar-alt"></i> Agenda uma Demonstração
                    </a>
                    <a href="#contacto" class="btn-outline-white">
                        <i class="fas fa-info-circle"></i> Saber Mais
                    </a>
                </div>
            </div>
            <div class="demo-video reveal from-right">
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

<div class="section-divider"></div>

<!-- PARA QUEM É INDICADO -->
<?php if (!empty($audiencia)): ?>
<section class="audience-section" id="para-quem">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-badge"><i class="fas fa-users"></i> Para Quem É</span>
            <h2 class="section-title">Para Quem é Indicado o Super Escola?</h2>
            <p class="section-subtitle">Desenvolvido para todos os tipos de instituições de ensino em Angola e África</p>
        </div>
        <div class="audience-grid">
            <?php foreach ($audiencia as $ai => $aud): ?>
            <div class="audience-card reveal" data-delay="<?= $ai * 80 ?>">
                <div class="audience-card-icon"><i class="fas <?= h($aud['icone']) ?>"></i></div>
                <h4><?= h($aud['titulo']) ?></h4>
                <p><?= h($aud['descricao']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- PLANOS / PREÇOS -->
<?php if (!empty($planos)):
    $planoIcons = ['Premium' => '⭐', 'Golden' => '🥇', 'Ruby' => '💎'];
?>
<section class="planos-section" id="planos">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-badge"><i class="fas fa-tags"></i> Planos & Preços</span>
            <h2 class="section-title">Escolha o Plano Certo para a sua Escola</h2>
            <p class="section-subtitle">Planos flexíveis com funcionalidades pensadas para todos os tipos de instituições de ensino.</p>
        </div>
        <div class="planos-grid">
            <?php foreach ($planos as $pi => $plano):
                $itens = getPlanoItens($plano['id']);
                $icon = $planoIcons[$plano['nome']] ?? '🎓';
                $numItens = count($itens);
            ?>
            <div class="plano-card <?= $plano['destaque'] ? 'plano-destaque' : '' ?> reveal" data-delay="<?= $pi * 120 ?>">
                <div class="plano-header" style="background: linear-gradient(135deg, <?= h($plano['cor']) ?>, <?= h($plano['cor']) ?>dd); border-top: none;">
                    <div class="plano-icon-row">
                        <span class="plano-emoji" style="font-size:36px;"><?= $icon ?></span>
                        <?php if ($plano['badge']): ?>
                        <span class="plano-count-badge"><?= h($plano['badge']) ?></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="plano-nome" style="color:white;"><?= h($plano['nome']) ?></h3>
                    <?php if ($plano['descricao']): ?>
                    <p class="plano-desc" style="color:rgba(255,255,255,.85);"><?= h($plano['descricao']) ?></p>
                    <?php endif; ?>
                    <div class="plano-preco">
                        <?php if ($plano['preco'] === 'Consultar'): ?>
                        <span class="plano-valor" style="color:white; font-size:22px;">Consultar preço</span>
                        <?php else: ?>
                        <span class="plano-valor" style="color:white;"><?= h($plano['preco']) ?></span>
                        <span class="plano-periodo" style="color:rgba(255,255,255,.8);">/ <?= h($plano['periodo']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <ul class="plano-itens plano-itens-scroll">
                    <?php foreach ($itens as $item): ?>
                    <li class="<?= $item['incluido'] ? 'incluido' : 'nao-incluido' ?>">
                        <i class="fas <?= $item['incluido'] ? 'fa-check-circle' : 'fa-times-circle' ?>" style="color:<?= $item['incluido'] ? h($plano['cor']) : '#ccc' ?>;"></i>
                        <?= h($item['descricao']) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="plano-footer">
                    <?php if (clienteLogado()): ?>
                    <button onclick="adicionarAoCarrinho(<?= $plano['id'] ?>, this)" class="plano-btn" style="background: <?= h($plano['cor']) ?>; color: white; border-color: <?= h($plano['cor']) ?>; width:100%; cursor:pointer; font-family:inherit;">
                        <i class="fas fa-cart-plus"></i> Adicionar ao Carrinho
                    </button>
                    <?php else: ?>
                    <a href="/registro.php?plano=<?= $plano['id'] ?>" class="plano-btn" style="background: <?= h($plano['cor']) ?>; color: white; border-color: <?= h($plano['cor']) ?>;">
                        <i class="fas fa-user-plus"></i> Registar para Contratar
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="reveal" style="text-align:center; margin-top:28px; padding:18px 24px; background:linear-gradient(135deg,#f0f7ff,#e8f4ff); border-radius:14px; font-size:14px; border:1px solid rgba(0,102,255,0.12);">
            <i class="fas fa-shield-alt" style="color:var(--primary);margin-right:6px;"></i>
            Todos os planos incluem suporte de instalação e formação.
            <a href="<?= getWhatsappLink() ?>" target="_blank" style="color:var(--primary); font-weight:700; margin-left:4px;"><i class="fab fa-whatsapp"></i> Fale connosco para uma proposta personalizada</a>.
        </div>
        <?php if (!clienteLogado()): ?>
        <div class="reveal" style="text-align:center; margin-top:12px; padding:14px 20px; background:#f0f7ff; border-radius:10px; font-size:14px;">
            <i class="fas fa-user-circle" style="color:var(--primary);"></i>
            Para adicionar ao carrinho, <a href="/registro.php" style="color:var(--primary);font-weight:600;">crie uma conta grátis</a> ou <a href="/login.php" style="color:var(--primary);font-weight:600;">entre na sua conta</a>.
        </div>
        <?php endif; ?>
        <div class="reveal" style="text-align:center; margin-top:20px;">
            <a href="planos-comparacao.php" class="btn-secondary">
                <i class="fas fa-table"></i> Ver Comparação Completa das 79 Funcionalidades
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- MANUAL DE APOIO DESTAQUES -->
<?php if (!empty($recentTopicos)):
$topicIcons = ['fa-rocket','fa-book','fa-cog','fa-chart-bar'];
?>
<section style="background:white; padding:64px 0;">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-badge"><i class="fas fa-book-open"></i> Manual de Apoio</span>
            <h2 class="section-title">Artigos Recentes do Manual</h2>
            <p class="section-subtitle">Guias e tutoriais para tirar o máximo proveito do Super Escola</p>
        </div>
        <div class="features-grid">
            <?php foreach ($recentTopicos as $ti => $t): ?>
            <div class="feature-card reveal" data-delay="<?= ($ti % 4) * 100 ?>" onclick="window.location='manual/topico.php?id=<?= $t['id'] ?>'" style="cursor:pointer;">
                <div class="feature-card-img">
                    <i class="fas <?= $topicIcons[$ti % count($topicIcons)] ?>"></i>
                </div>
                <div class="feature-card-body">
                    <span class="feature-badge"><?= h($t['categoria_nome']) ?></span>
                    <h4><?= h($t['titulo']) ?></h4>
                    <p><?= h(mb_substr($t['conteudo'] ?? '', 0, 100)) ?>...</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="reveal" style="text-align:center; margin-top:32px;">
            <a href="manual/" class="btn-secondary"><i class="fas fa-book"></i> Ver Todo o Manual</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- FALA CONNOSCO CTA -->
<section class="contact-cta-section" id="contacto">
    <div class="container">
        <div class="reveal" style="max-width:600px;margin:0 auto;text-align:center;">
            <div style="font-size:52px;margin-bottom:16px;animation:bounce 2s infinite;">💬</div>
            <h2><i class="fab fa-whatsapp"></i> Fala Connosco</h2>
            <p>Tem dúvidas sobre o Super Escola? A nossa equipa está pronta para ajudar agora mesmo!</p>
            <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap; margin-top:8px;">
                <a href="<?= $whatsapp ?>" target="_blank" class="btn-whatsapp-large">
                    <i class="fab fa-whatsapp"></i> Enviar Mensagem no WhatsApp
                </a>
                <a href="mailto:<?= h(getConfig('site_email', 'geral@queta.ao')) ?>" class="btn-outline-white" style="border-color:white;color:white;">
                    <i class="fas fa-envelope"></i> Enviar Email
                </a>
            </div>
            <p style="margin-top:20px;font-size:13px;color:rgba(255,255,255,0.7);"><i class="fas fa-clock"></i> Respondemos em menos de 1 hora nos dias úteis</p>
        </div>
    </div>
</section>
<style>
@keyframes bounce { 0%,100%{transform:translateY(0);} 50%{transform:translateY(-10px);} }
</style>

<script>
function adicionarAoCarrinho(planoId, btn) {
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> A adicionar...';
    const fd = new FormData();
    fd.append('acao', 'carrinho_adicionar');
    fd.append('plano_id', planoId);
    fetch('/api/auth.php', { method: 'POST', body: fd })
        .then(r => r.json()).then(d => {
            if (d.ok) {
                btn.innerHTML = '<i class="fas fa-check"></i> Adicionado!';
                btn.style.background = '#1a7a3a';
                btn.style.borderColor = '#1a7a3a';
                showCartToast(d.novo ? 'Plano adicionado ao carrinho!' : 'Plano já estava no carrinho.', d.novo ? 'success' : 'info');
                // Update cart badge
                const badge = document.querySelector('.nav-cart-badge');
                if (badge) { badge.textContent = d.total; }
                else {
                    const cartBtn = document.querySelector('.nav-cart-btn');
                    if (cartBtn) {
                        const b = document.createElement('span');
                        b.className = 'nav-cart-badge';
                        b.textContent = d.total;
                        cartBtn.appendChild(b);
                    }
                }
                setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; btn.style.background = ''; btn.style.borderColor = ''; }, 3000);
            } else if (d.erro === 'login_required') {
                window.location.href = '/login.php?plano=' + planoId;
            } else {
                btn.innerHTML = orig; btn.disabled = false;
            }
        }).catch(() => { btn.innerHTML = orig; btn.disabled = false; });
}

function showCartToast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = 'cart-toast ' + type;
    t.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'info-circle') + '"></i> ' + msg +
        ' <a href="/carrinho.php" style="color:rgba(255,255,255,0.8);margin-left:8px;font-size:12px;text-decoration:underline;">Ver carrinho</a>';
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 4000);
}
</script>
<?php include 'includes/footer.php'; ?>
