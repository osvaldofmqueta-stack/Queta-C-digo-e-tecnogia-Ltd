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

<?php
// Static testimonials (can be made dynamic later)
$testimonials = [
    ['nome'=>'Directora Rosa Fernandes','escola'=>'Colégio São João','provincia'=>'Luanda','texto'=>'Desde que adoptámos o Super Escola as matrículas e cobranças ficaram completamente automatizadas. Poupamos horas por dia!','stars'=>5,'inicial'=>'R'],
    ['nome'=>'Director Carlos Mendes','escola'=>'Instituto Técnico Nacional','provincia'=>'Benguela','texto'=>'A gestão de notas e pautas digitais é excelente. Os professores adaptaram-se rapidamente e os pais adoram receber relatórios automáticos.','stars'=>5,'inicial'=>'C'],
    ['nome'=>'Directora Ana Luísa','escola'=>'Escola Privada Horizonte','provincia'=>'Huambo','texto'=>'O suporte da equipa Queta é fantástico. Resolvem qualquer dúvida rapidamente. O sistema é intuitivo e completo.','stars'=>5,'inicial'=>'A'],
];
?>

<!-- ═══════════════════════════════════════
     TRUST TICKER
═══════════════════════════════════════ -->
<div class="trust-ticker">
    <div class="trust-ticker-track">
        <?php $items = ['🔒 Dados 100% Seguros','🎧 Suporte Incluído','☁️ Acesso Online 24/7','🎓 Formação Incluída','🔄 Actualizações Grátis','📊 Relatórios Automáticos','📱 Acesso pelo Telemóvel','✅ Instalação Assistida']; ?>
        <?php for ($r=0;$r<3;$r++): foreach ($items as $it): ?>
        <span class="trust-ticker-item"><?= $it ?></span>
        <span class="trust-ticker-sep">·</span>
        <?php endforeach; endfor; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════
     STATS BAND
═══════════════════════════════════════ -->
<section class="stats-band" id="sobre">
    <div class="container">
        <div class="stats-band-grid">
            <div class="stats-band-item reveal" data-delay="0">
                <span class="counter-num sbn" data-target="79" data-suffix="+">0+</span>
                <span class="sbl">Funcionalidades</span>
            </div>
            <div class="stats-band-div"></div>
            <div class="stats-band-item reveal" data-delay="100">
                <span class="counter-num sbn" data-target="100" data-suffix="+">0+</span>
                <span class="sbl">Escolas Clientes</span>
            </div>
            <div class="stats-band-div"></div>
            <div class="stats-band-item reveal" data-delay="200">
                <span class="counter-num sbn" data-target="3" data-suffix="">0</span>
                <span class="sbl">Planos Disponíveis</span>
            </div>
            <div class="stats-band-div"></div>
            <div class="stats-band-item reveal" data-delay="300">
                <span class="counter-num sbn" data-target="24" data-suffix="/7">0/7</span>
                <span class="sbl">Suporte Disponível</span>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════
     APLICAÇÕES (pill grid)
═══════════════════════════════════════ -->
<?php if (!empty($aplicacoes)): ?>
<section class="apps-pill-section" id="aplicacoes">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-badge"><i class="fas fa-th-large"></i> As Nossas Soluções</span>
            <h2 class="section-title">Tudo para gerir a sua escola</h2>
            <p class="section-subtitle">Módulos integrados que cobrem toda a gestão académica e financeira</p>
        </div>
        <div class="apps-pill-grid">
            <?php
            $appIcons = ['fa-user-graduate','fa-money-bill-wave','fa-book-open','fa-chalkboard-teacher','fa-chart-pie','fa-comments','fa-calendar-alt','fa-file-alt'];
            foreach ($aplicacoes as $ai => $app):
            ?>
            <a href="aplicacao.php?id=<?= $app['id'] ?>" class="app-pill reveal" data-delay="<?= $ai * 60 ?>">
                <span class="app-pill-icon"><i class="fas <?= $appIcons[$ai % count($appIcons)] ?>"></i></span>
                <span class="app-pill-name"><?= h($app['nome']) ?></span>
                <i class="fas fa-arrow-right app-pill-arrow"></i>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════
     FEATURES BENTO GRID
═══════════════════════════════════════ -->
<?php if (!empty($destaques)):
$featureIcons = ['fa-users-cog','fa-money-bill-wave','fa-book-open','fa-comments','fa-chart-bar','fa-mobile-alt'];
?>
<section class="bento-section" id="funcionalidades">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-badge"><i class="fas fa-star"></i> Funcionalidades</span>
            <h2 class="section-title">O que está incluído</h2>
            <p class="section-subtitle">As ferramentas que transformam a gestão escolar em Angola</p>
        </div>
        <div class="bento-grid">
            <?php foreach ($destaques as $fi => $f):
                $icon = $featureIcons[$fi % count($featureIcons)];
                $img = ($f['imagem'] && file_exists($f['imagem'])) ? $f['imagem'] : null;
                $big = $fi === 0;
            ?>
            <div class="bento-card <?= $big ? 'bento-big' : '' ?> reveal" data-delay="<?= ($fi % 4) * 80 ?>">
                <div class="bento-card-icon <?= $big ? 'bento-icon-big' : '' ?>">
                    <?php if ($img): ?>
                    <img src="<?= h($img) ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
                    <?php else: ?>
                    <i class="fas <?= $icon ?>"></i>
                    <?php endif; ?>
                </div>
                <div class="bento-card-tag"><?= h($f['aplicacao_nome'] ?? 'Super Escola') ?></div>
                <h4 class="bento-card-title"><?= h($f['titulo']) ?></h4>
                <?php if ($big): ?><p class="bento-card-desc"><?= h($f['descricao']) ?></p><?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════
     DEMO SPLIT
═══════════════════════════════════════ -->
<section class="demo-section" id="demo">
    <div class="container">
        <div class="demo-grid">
            <div class="demo-content reveal from-left">
                <span class="section-badge" style="background:rgba(0,200,150,0.15);color:#4fffcb;border:1px solid rgba(0,200,150,0.3);">
                    <i class="fas fa-play-circle"></i> Demonstração
                </span>
                <h2>Simplifique a gestão e gere mais receita!</h2>
                <p>O Super Escola centraliza tudo numa única plataforma — sem papéis, sem confusão, sem atrasos.</p>
                <ul class="demo-checklist">
                    <li><i class="fas fa-check-circle"></i> Matrículas e propinas automatizadas</li>
                    <li><i class="fas fa-check-circle"></i> Notas e pautas digitais em segundos</li>
                    <li><i class="fas fa-check-circle"></i> Portal para pais e encarregados</li>
                    <li><i class="fas fa-check-circle"></i> Relatórios financeiros automáticos</li>
                    <li><i class="fas fa-check-circle"></i> Comunicação directa com professores</li>
                </ul>
                <div class="demo-btns">
                    <a href="<?= $whatsapp ?>" target="_blank" class="btn-primary">
                        <i class="fas fa-calendar-alt"></i> Agendar Demonstração
                    </a>
                    <a href="#planos" class="btn-outline-white">
                        <i class="fas fa-tags"></i> Ver Planos
                    </a>
                </div>
            </div>
            <div class="demo-video reveal from-right">
                <div class="video-wrapper">
                    <?php if ($videoUrl): ?>
                    <div class="video-placeholder" id="video-wrapper" onclick="playVideo('<?= h($videoUrl) ?>')">
                        <div class="video-play-btn"><i class="fas fa-play"></i></div>
                        <p>Clique para ver o Super Escola em acção</p>
                    </div>
                    <?php else: ?>
                    <div class="video-placeholder demo-mockup">
                        <div class="demo-mockup-screen">
                            <div class="dms-topbar"><span></span><span></span><span></span></div>
                            <div class="dms-row"><div class="dms-label">Matrículas</div><div class="dms-bar"><div style="width:87%"></div></div><div class="dms-val">87%</div></div>
                            <div class="dms-row"><div class="dms-label">Propinas</div><div class="dms-bar"><div style="width:94%;background:#00C896"></div></div><div class="dms-val">94%</div></div>
                            <div class="dms-row"><div class="dms-label">Notas</div><div class="dms-bar"><div style="width:76%;background:#F5A623"></div></div><div class="dms-val">76%</div></div>
                            <div class="dms-row"><div class="dms-label">Pais activos</div><div class="dms-bar"><div style="width:63%;background:#C0392B"></div></div><div class="dms-val">63%</div></div>
                        </div>
                        <div class="video-play-btn" style="position:absolute;"><i class="fas fa-play"></i></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════
     PLANOS / PREÇOS
═══════════════════════════════════════ -->
<?php if (!empty($planos)):
    $planoIcons = ['Premium' => '⭐', 'Golden' => '🥇', 'Ruby' => '💎'];
?>
<section class="planos-section" id="planos">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-badge"><i class="fas fa-tags"></i> Planos & Preços</span>
            <h2 class="section-title">Escolha o plano da sua escola</h2>
            <p class="section-subtitle">Planos flexíveis pensados para todos os tipos de instituição de ensino.</p>
        </div>
        <div class="planos-grid">
            <?php foreach ($planos as $pi => $plano):
                $itens = getPlanoItens($plano['id']);
                $icon = $planoIcons[$plano['nome']] ?? '🎓';
            ?>
            <div class="plano-card <?= $plano['destaque'] ? 'plano-destaque' : '' ?> reveal" data-delay="<?= $pi * 120 ?>">
                <div class="plano-header" style="background:linear-gradient(135deg,<?= h($plano['cor']) ?>,<?= h($plano['cor']) ?>cc);">
                    <div class="plano-icon-row">
                        <span class="plano-emoji"><?= $icon ?></span>
                        <?php if ($plano['badge']): ?>
                        <span class="plano-count-badge"><?= h($plano['badge']) ?></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="plano-nome"><?= h($plano['nome']) ?></h3>
                    <?php if ($plano['descricao']): ?>
                    <p class="plano-desc"><?= h($plano['descricao']) ?></p>
                    <?php endif; ?>
                    <div class="plano-preco">
                        <?php if ($plano['preco'] === 'Consultar'): ?>
                        <span class="plano-valor" style="font-size:22px;">Consultar preço</span>
                        <?php else: ?>
                        <span class="plano-valor"><?= h($plano['preco']) ?></span>
                        <span class="plano-periodo">/ <?= h($plano['periodo']) ?></span>
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
                    <button onclick="adicionarAoCarrinho(<?= $plano['id'] ?>, this)" class="plano-btn" style="background:<?= h($plano['cor']) ?>;color:white;border-color:<?= h($plano['cor']) ?>;width:100%;cursor:pointer;font-family:inherit;">
                        <i class="fas fa-cart-plus"></i> Adicionar ao Carrinho
                    </button>
                    <?php else: ?>
                    <a href="/registro.php?plano=<?= $plano['id'] ?>" class="plano-btn" style="background:<?= h($plano['cor']) ?>;color:white;border-color:<?= h($plano['cor']) ?>;">
                        <i class="fas fa-user-plus"></i> Contratar Agora
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="plans-footer-note reveal">
            <i class="fas fa-shield-alt"></i>
            Todos os planos incluem instalação, formação e suporte.
            <a href="planos-comparacao.php"><i class="fas fa-table"></i> Comparar todas as 79 funcionalidades</a>
            &nbsp;·&nbsp;
            <a href="<?= getWhatsappLink() ?>" target="_blank"><i class="fab fa-whatsapp"></i> Proposta personalizada</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════
     TESTEMUNHOS
═══════════════════════════════════════ -->
<section class="testimonials-section" id="testemunhos">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-badge"><i class="fas fa-quote-left"></i> Testemunhos</span>
            <h2 class="section-title">O que dizem as escolas</h2>
            <p class="section-subtitle">Directores e gestores que já transformaram as suas instituições com o Super Escola</p>
        </div>
        <div class="testimonials-grid">
            <?php foreach ($testimonials as $ti => $t): ?>
            <div class="testimonial-card reveal" data-delay="<?= $ti * 100 ?>">
                <div class="testimonial-stars">
                    <?php for ($s=0;$s<$t['stars'];$s++): ?><i class="fas fa-star"></i><?php endfor; ?>
                </div>
                <p class="testimonial-text">"<?= h($t['texto']) ?>"</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar"><?= h($t['inicial']) ?></div>
                    <div>
                        <div class="testimonial-name"><?= h($t['nome']) ?></div>
                        <div class="testimonial-school"><i class="fas fa-school"></i> <?= h($t['escola']) ?> · <?= h($t['provincia']) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════
     MANUAL (compact link list)
═══════════════════════════════════════ -->
<?php if (!empty($recentTopicos)): ?>
<section class="manual-compact" id="manual">
    <div class="container">
        <div class="manual-compact-inner reveal">
            <div class="manual-compact-header">
                <div>
                    <span class="section-badge" style="margin-bottom:10px;"><i class="fas fa-book-open"></i> Manual de Apoio</span>
                    <h3>Tutoriais e guias recentes</h3>
                </div>
                <a href="manual/" class="btn-secondary" style="flex-shrink:0;"><i class="fas fa-arrow-right"></i> Ver todos</a>
            </div>
            <div class="manual-compact-list">
                <?php
                $topicIcons = ['fa-rocket','fa-book','fa-cog','fa-chart-bar'];
                foreach ($recentTopicos as $ti => $t):
                ?>
                <a href="manual/topico.php?id=<?= $t['id'] ?>" class="manual-compact-item">
                    <span class="mci-icon"><i class="fas <?= $topicIcons[$ti % count($topicIcons)] ?>"></i></span>
                    <span class="mci-body">
                        <span class="mci-cat"><?= h($t['categoria_nome']) ?></span>
                        <span class="mci-title"><?= h($t['titulo']) ?></span>
                    </span>
                    <i class="fas fa-chevron-right mci-arrow"></i>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════
     FINAL CTA
═══════════════════════════════════════ -->
<section class="final-cta" id="contacto">
    <div class="container">
        <div class="final-cta-inner reveal">
            <div class="final-cta-text">
                <h2>Pronto para transformar a sua escola?</h2>
                <p>Fale connosco hoje e receba uma demonstração gratuita do Super Escola.</p>
                <div class="final-cta-trust">
                    <span><i class="fas fa-check"></i> Sem compromisso</span>
                    <span><i class="fas fa-check"></i> Instalação assistida</span>
                    <span><i class="fas fa-check"></i> Suporte dedicado</span>
                </div>
            </div>
            <div class="final-cta-actions">
                <a href="<?= $whatsapp ?>" target="_blank" class="cta-btn-whatsapp">
                    <i class="fab fa-whatsapp"></i> Falar pelo WhatsApp
                </a>
                <a href="mailto:<?= h(getConfig('site_email', 'geral@queta.ao')) ?>" class="cta-btn-email">
                    <i class="fas fa-envelope"></i> Enviar Email
                </a>
                <p class="final-cta-note"><i class="fas fa-clock"></i> Respondemos em menos de 1 hora</p>
            </div>
        </div>
    </div>
</section>

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
